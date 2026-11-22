import { describe, expect, it } from 'vitest'
import {
  formatFullDate,
  formatShortDate,
  groupByMonth,
  monthKey,
  parseExpenseDate
} from '@/modules/splitExpense/utils/expenseDates'

// An expense date is a calendar day, not a moment in time: however the API spells it, 1 Dec must
// read as 1 Dec in every timezone. Run with TZ=America/Los_Angeles (and others) to see the shift
// this guards against.
const FIRST_OF_DECEMBER = ['2026-12-01', '2026-12-01T00:00:00+00:00', '2026-12-01T00:00:00.000Z']

describe.each(FIRST_OF_DECEMBER)('expense date %s', (value) => {
  it('is the same calendar day in any timezone', () => {
    const date = parseExpenseDate(value)!
    expect([date.getFullYear(), date.getMonth() + 1, date.getDate()]).toEqual([2026, 12, 1])
  })

  it('is shown as 1 December and grouped under December', () => {
    expect(formatShortDate(value).day).toBe('01')
    expect(formatShortDate(value).month).toMatch(/dec/i)
    expect(formatFullDate(value)).toMatch(/dec/i)
    expect(formatFullDate(value)).toMatch(/\b1\b/)
    expect(monthKey(value)).toBe('2026-12')
  })
})

describe('month boundaries', () => {
  it('keeps the last and first day of a month in their own months', () => {
    expect(monthKey('2026-11-30T00:00:00+00:00')).toBe('2026-11')
    expect(monthKey('2026-12-01T00:00:00+00:00')).toBe('2026-12')
    expect(monthKey('2027-01-01T00:00:00+00:00')).toBe('2027-01')
    expect(formatShortDate('2026-11-30T00:00:00+00:00').day).toBe('30')
  })

  it('groups expenses by their calendar month', () => {
    const groups = groupByMonth(
      [
        { id: 1, date: '2026-12-01T00:00:00+00:00' },
        { id: 2, date: '2026-11-30T00:00:00+00:00' },
        { id: 3, date: '2026-12-15T00:00:00+00:00' }
      ],
      (item) => item.date
    )

    expect(groups.map((group) => [group.month, group.items.map((item) => item.id)])).toEqual([
      ['2026-12', [1, 3]],
      ['2026-11', [2]]
    ])
  })
})

describe('unparseable input', () => {
  it('returns null and falls back to the raw text', () => {
    expect(parseExpenseDate('not a date')).toBeNull()
    expect(monthKey('not a date')).toBe('not a date')
    expect(formatShortDate('not a date')).toEqual({ day: 'not a date', month: '' })
  })
})
