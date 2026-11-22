import { afterEach, describe, expect, it } from 'vitest'
import { flushPromises, mount, type VueWrapper } from '@vue/test-utils'
import FormInputDate from '@/modules/core/components/form/FormInputDate.vue'
import { detectDateFormat, formatDisplay } from '@/modules/core/utils/dateInput'
import { todayIso } from '@/modules/core/utils/dateCalendar'

const format = detectDateFormat()
let wrapper: VueWrapper | null = null

const mountField = (modelValue = '2026-11-22', props: Record<string, unknown> = {}) => {
  wrapper = mount(FormInputDate, {
    props: { id: 'd', label: 'Date', modelValue, ...props },
    attachTo: document.body
  })
  return wrapper
}

const textInput = () => wrapper!.find('input[type="text"]')
const calendar = () => document.body.querySelector('.form-date-calendar')
const day = (iso: string) => document.body.querySelector<HTMLElement>(`[data-date="${iso}"]`)
const lastEmit = () => {
  const emitted = wrapper!.emitted('update:modelValue')
  return emitted?.[emitted.length - 1]
}

afterEach(() => {
  wrapper?.unmount()
  wrapper = null
})

describe('FormInputDate: text', () => {
  it('shows the model as locale text', () => {
    mountField('2026-11-22')
    expect((textInput().element as HTMLInputElement).value).toBe(
      formatDisplay('2026-11-22', format)
    )
  })

  it('emits an ISO date once the typed text is a complete real date', async () => {
    mountField('')
    await textInput().setValue(formatDisplay('2026-11-22', format).replace(/\D/g, ''))

    expect(lastEmit()).toEqual(['2026-11-22'])
    expect((textInput().element as HTMLInputElement).value).toBe(
      formatDisplay('2026-11-22', format)
    )
  })

  it('emits an empty value and blocks submit for an incomplete date', async () => {
    mountField('2026-11-22')
    await textInput().setValue('12')

    expect(lastEmit()).toEqual([''])
    expect((textInput().element as HTMLInputElement).validationMessage).not.toBe('')
  })

  it('reverts half-typed text on blur', async () => {
    mountField('2026-11-22')
    await textInput().setValue('12')
    await wrapper!.setProps({ modelValue: '' })
    await textInput().trigger('blur')

    expect((textInput().element as HTMLInputElement).value).toBe('')
  })

  it('follows external model changes', async () => {
    mountField('2026-11-22')
    await wrapper!.setProps({ modelValue: '2027-01-02' })

    expect((textInput().element as HTMLInputElement).value).toBe(
      formatDisplay('2027-01-02', format)
    )
  })
})

describe('FormInputDate: clicks', () => {
  it('opens the calendar from anywhere on the field except the date text', async () => {
    mountField()
    expect(calendar()).toBeNull()

    await wrapper!.find('.form-date').trigger('click') // empty space
    await flushPromises()
    expect(calendar()).not.toBeNull()

    await wrapper!.find('.form-date-icon').trigger('click') // the icon toggles it closed again
    await flushPromises()
    expect(calendar()).toBeNull()
  })

  it('does not toggle the calendar when the date text is clicked', async () => {
    mountField()
    await textInput().trigger('click')
    await flushPromises()
    expect(calendar()).toBeNull()

    await wrapper!.find('.form-date').trigger('click')
    await flushPromises()
    expect(calendar()).not.toBeNull()

    await textInput().trigger('mousedown') // going to edit closes it
    await flushPromises()
    expect(calendar()).toBeNull()
  })

  it('closes on a click outside', async () => {
    mountField()
    await wrapper!.find('.form-date').trigger('click')
    await flushPromises()

    document.body.click()
    await flushPromises()
    expect(calendar()).toBeNull()
  })

  it('does nothing when disabled', async () => {
    mountField('2026-11-22', { disabled: true })
    await wrapper!.find('.form-date').trigger('click')
    await flushPromises()
    expect(calendar()).toBeNull()
  })
})

describe('FormInputDate: calendar', () => {
  const openCalendar = async (modelValue = '2026-11-22', props: Record<string, unknown> = {}) => {
    mountField(modelValue, props)
    await wrapper!.find('.form-date').trigger('click')
    await flushPromises()
  }

  it('shows the month of the current value with the day selected', async () => {
    await openCalendar('2026-11-22')

    expect(document.body.querySelector('.form-date-title')?.textContent).toMatch(/2026/)
    expect(day('2026-11-22')?.classList.contains('is-selected')).toBe(true)
    expect(document.body.querySelectorAll('.form-date-day')).toHaveLength(42)
  })

  it('picks a day, updates the text and closes', async () => {
    await openCalendar('2026-11-22')
    day('2026-11-05')!.click()
    await flushPromises()

    expect(lastEmit()).toEqual(['2026-11-05'])
    expect((textInput().element as HTMLInputElement).value).toBe(
      formatDisplay('2026-11-05', format)
    )
    expect(calendar()).toBeNull()
  })

  it('moves between months and years with the arrows', async () => {
    await openCalendar('2026-11-22')
    const title = () => document.body.querySelector('.form-date-title')!.textContent

    document.body.querySelector<HTMLElement>('[aria-label="Next month"]')!.click()
    await flushPromises()
    expect(day('2026-12-05')).not.toBeNull()

    document.body.querySelector<HTMLElement>('[aria-label="Previous year"]')!.click()
    await flushPromises()
    expect(title()).toMatch(/2025/)
  })

  it('never changes the month when the mouse moves over days, including faded ones', async () => {
    await openCalendar('2026-11-22')
    const title = () => document.body.querySelector('.form-date-title')!.textContent
    const before = title()

    for (const cell of document.body.querySelectorAll<HTMLElement>('.form-date-day')) {
      cell.dispatchEvent(new MouseEvent('mouseenter'))
      cell.dispatchEvent(new MouseEvent('mouseover', { bubbles: true }))
      cell.dispatchEvent(new MouseEvent('mousemove', { bubbles: true }))
    }
    await flushPromises()

    expect(title()).toBe(before)
    expect(wrapper!.emitted('update:modelValue')).toBeUndefined()
  })

  it('picks a day from a neighbouring month shown faded in the grid', async () => {
    await openCalendar('2026-11-22')
    day('2026-12-03')!.click() // December days are shown after Nov 30
    await flushPromises()

    expect(lastEmit()).toEqual(['2026-12-03'])
    expect(calendar()).toBeNull()
  })

  it('keeps the day when moving the month on screen with the arrows', async () => {
    await openCalendar('2026-11-22')
    const next = document.body.querySelector<HTMLElement>('[aria-label="Next month"]')!

    next.click()
    await flushPromises()
    expect(day('2026-12-05')).not.toBeNull()
    expect(wrapper!.emitted('update:modelValue')).toBeUndefined() // browsing does not change the value
  })

  it('offers Today, and Clear only for optional fields', async () => {
    await openCalendar('2020-01-15') // any date that is not today
    expect(document.body.textContent).not.toContain('Clear')

    const todayButton = [...document.body.querySelectorAll<HTMLElement>('.form-date-link')].find(
      (button) => button.textContent?.trim() === 'Today'
    )!
    todayButton.click()
    await flushPromises()
    expect(lastEmit()).toEqual([todayIso()])
  })

  it('clears an optional field', async () => {
    await openCalendar('2026-11-22', { required: false })
    const clearButton = [...document.body.querySelectorAll<HTMLElement>('.form-date-link')].find(
      (button) => button.textContent?.trim() === 'Clear'
    )!
    clearButton.click()
    await flushPromises()

    expect(lastEmit()).toEqual([''])
    expect((textInput().element as HTMLInputElement).value).toBe('')
  })

  it('is driven from the keyboard', async () => {
    mountField('2026-11-22')
    const root = wrapper!.find('.form-date')

    await root.trigger('keydown', { key: 'ArrowDown' })
    await flushPromises()
    expect(calendar()).not.toBeNull()

    await root.trigger('keydown', { key: 'ArrowRight' }) // 23rd
    await root.trigger('keydown', { key: 'ArrowDown' }) // 30th
    await root.trigger('keydown', { key: 'Enter' })
    await flushPromises()

    expect(lastEmit()).toEqual(['2026-11-30'])
    expect(calendar()).toBeNull()
  })

  it('closes on Escape without changing the value', async () => {
    await openCalendar('2026-11-22')
    await wrapper!.find('.form-date').trigger('keydown', { key: 'Escape' })
    await flushPromises()

    expect(calendar()).toBeNull()
    expect(wrapper!.emitted('update:modelValue')).toBeUndefined()
  })
})
