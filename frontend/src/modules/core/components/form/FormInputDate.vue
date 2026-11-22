<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import FormSelectDropdown from '@/modules/core/components/form/FormSelectDropdown.vue'
import { useFixedDropdown } from '@/modules/core/composables/useFixedDropdown'
import {
  detectDateFormat,
  formatDisplay,
  maskInput,
  parseDisplay,
  placeholderFor
} from '@/modules/core/utils/dateInput'
import {
  addDays,
  addMonths,
  detectFirstDay,
  isValidIso,
  monthGrid,
  monthTitle,
  todayIso,
  weekdayLabels
} from '@/modules/core/utils/dateCalendar'

interface Props {
  id: string
  label: string
  modelValue: string // ISO date, YYYY-MM-DD (a longer ISO datetime is accepted and trimmed)
  isValid?: boolean | null
  errorText?: string
  helpText?: string
  disabled?: boolean
  required?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  helpText: '',
  disabled: false,
  required: true
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const PANEL_WIDTH_REM = 19
const PANEL_MARGIN_PX = 8

const format = detectDateFormat()
const placeholder = placeholderFor(format)
const firstDay = detectFirstDay()
const weekdays = weekdayLabels(firstDay)

const rootEl = ref<HTMLElement | null>(null)
const textEl = ref<HTMLInputElement | null>(null)
const panelEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)

const text = ref(formatDisplay(props.modelValue, format))
const selectedIso = computed(() => {
  const iso = props.modelValue.slice(0, 10)
  return isValidIso(iso) ? iso : ''
})

// The day the keyboard (or mouse hover) is on; the shown month always follows it.
const cursor = ref(selectedIso.value || todayIso())
const today = computed(() => todayIso())
// The month on screen. Only the nav buttons, the keyboard and the value change it; hovering never does.
const viewMonth = ref(cursor.value.slice(0, 7))
// The keyboard-driven highlight is shown only while the keyboard is in use; hover is plain CSS.
const usingKeyboard = ref(false)
const cells = computed(() => monthGrid(`${viewMonth.value}-01`, firstDay))
const title = computed(() => monthTitle(`${viewMonth.value}-01`))

const { dropdownStyle, isOpenUp, isEventInside } = useFixedDropdown({
  anchorEl: rootEl,
  rootEl,
  panelEl,
  isOpen,
  maxHeightPx: 400
})

// A calendar needs a fixed width, not the field's width; keep it on screen.
const panelStyle = computed(() => {
  const style = { ...dropdownStyle.value }
  if (!isOpen.value || !rootEl.value) return style

  const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16
  const width = Math.min(PANEL_WIDTH_REM * rem, window.innerWidth - PANEL_MARGIN_PX * 2)
  const maxLeft = window.innerWidth - PANEL_MARGIN_PX - width
  const left = Math.max(
    PANEL_MARGIN_PX,
    Math.min(rootEl.value.getBoundingClientRect().left, maxLeft)
  )

  return { ...style, left: `${left}px`, width: `${width}px` }
})

const syncValidity = () => {
  const incomplete = text.value !== '' && parseDisplay(text.value, format) === null
  textEl.value?.setCustomValidity(incomplete ? `Enter a valid date (${placeholder})` : '')
}

watch(
  () => props.modelValue,
  () => {
    // Skip echoes of what was just typed; only external changes (reset, edit-load) replace the text.
    if ((parseDisplay(text.value, format) ?? '') !== props.modelValue.slice(0, 10)) {
      text.value = formatDisplay(props.modelValue, format)
      syncValidity()
    }
    if (selectedIso.value) showDay(selectedIso.value)
  }
)

onMounted(syncValidity)

const setValue = (iso: string) => {
  text.value = formatDisplay(iso, format)
  syncValidity()
  if (iso !== props.modelValue.slice(0, 10)) emit('update:modelValue', iso)
}

const onInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  const masked = maskInput(input.value, format)

  text.value = masked
  input.value = masked
  syncValidity()

  const iso = parseDisplay(masked, format) ?? ''
  if (iso !== props.modelValue.slice(0, 10)) emit('update:modelValue', iso)
  if (iso) showDay(iso)
}

const onBlur = () => {
  // Drop a half-typed date instead of leaving text that disagrees with the model.
  if (text.value !== '' && parseDisplay(text.value, format) === null) {
    text.value = formatDisplay(props.modelValue, format)
    syncValidity()
  }
}

const open = () => {
  if (props.disabled || isOpen.value) return
  showDay(selectedIso.value || todayIso())
  usingKeyboard.value = false
  isOpen.value = true
  // Keep the keyboard in the field so arrows and Enter drive the calendar.
  void nextTick(() => textEl.value?.focus({ preventScroll: true }))
}

const close = () => {
  isOpen.value = false
}

// Any click on the field except on the date text itself toggles the calendar.
const onFieldClick = (event: MouseEvent) => {
  if ((event.target as HTMLElement).closest('.form-date-text')) return
  if (isOpen.value) close()
  else open()
}

// Going to edit the text means the calendar is no longer needed.
const onTextMouseDown = () => close()

const pick = (iso: string) => {
  setValue(iso)
  close()
  textEl.value?.focus({ preventScroll: true })
}

const clear = () => {
  setValue('')
  close()
}

// Points the cursor at a day and brings its month on screen.
function showDay(iso: string) {
  cursor.value = iso
  viewMonth.value = iso.slice(0, 7)
}

const moveCursor = (iso: string) => {
  usingKeyboard.value = true
  showDay(iso)
}

// Nav buttons only change the month on screen; the cursor and the value stay where they are.
const shiftView = (months: number) => {
  viewMonth.value = addMonths(`${viewMonth.value}-01`, months).slice(0, 7)
}

const onKeydown = (event: KeyboardEvent) => {
  if (props.disabled) return

  if (!isOpen.value) {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      open()
    }
    return
  }

  const handlers: Record<string, () => void> = {
    Escape: close,
    Tab: close,
    ArrowLeft: () => moveCursor(addDays(cursor.value, -1)),
    ArrowRight: () => moveCursor(addDays(cursor.value, 1)),
    ArrowUp: () => moveCursor(addDays(cursor.value, -7)),
    ArrowDown: () => moveCursor(addDays(cursor.value, 7)),
    PageUp: () => moveCursor(addMonths(cursor.value, event.shiftKey ? -12 : -1)),
    PageDown: () => moveCursor(addMonths(cursor.value, event.shiftKey ? 12 : 1)),
    Enter: () => pick(cursor.value)
  }

  const handler = handlers[event.key]
  if (!handler) return

  if (event.key !== 'Tab') event.preventDefault() // also keeps Enter from submitting the form
  handler()
}

const onDocumentClick = (event: MouseEvent) => {
  if (isOpen.value && !isEventInside(event)) close()
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))

defineExpose({ open, close })
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div
      ref="rootEl"
      class="form-date"
      :class="{
        'form-date--open': isOpen,
        'form-date--invalid': isValid === false,
        'form-date--disabled': disabled
      }"
      @click="onFieldClick"
      @keydown="onKeydown"
    >
      <label :for="id" class="form-date-label">{{ label }}</label>

      <input
        :id="id"
        ref="textEl"
        type="text"
        class="form-date-text"
        :value="text"
        :placeholder="placeholder"
        :aria-describedby="`${id}-validation-feedback`"
        aria-haspopup="dialog"
        :aria-expanded="isOpen"
        :required="required"
        :disabled="disabled"
        inputmode="numeric"
        autocomplete="off"
        @mousedown="onTextMouseDown"
        @input="onInput"
        @blur="onBlur"
      />

      <i class="bi bi-calendar3 form-date-icon" aria-hidden="true"></i>
    </div>

    <FormSelectDropdown
      v-model:el="panelEl"
      :open="isOpen"
      :open-up="isOpenUp"
      :invalid="isValid === false"
      :panel-style="panelStyle"
      role="dialog"
      aria-label="Choose date"
    >
      <div class="form-date-calendar" @mousemove="usingKeyboard = false">
        <div class="form-date-nav">
          <button
            type="button"
            class="form-date-nav-button"
            aria-label="Previous year"
            @click="shiftView(-12)"
          >
            <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
          </button>
          <button
            type="button"
            class="form-date-nav-button"
            aria-label="Previous month"
            @click="shiftView(-1)"
          >
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
          </button>
          <span class="form-date-title" aria-live="polite">{{ title }}</span>
          <button
            type="button"
            class="form-date-nav-button"
            aria-label="Next month"
            @click="shiftView(1)"
          >
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
          </button>
          <button
            type="button"
            class="form-date-nav-button"
            aria-label="Next year"
            @click="shiftView(12)"
          >
            <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
          </button>
        </div>

        <div class="form-date-grid">
          <span v-for="weekday in weekdays" :key="weekday" class="form-date-weekday">
            {{ weekday }}
          </span>

          <button
            v-for="cell in cells"
            :key="cell.iso"
            type="button"
            class="form-date-day"
            :class="{
              'is-outside': !cell.inMonth,
              'is-today': cell.iso === today,
              'is-cursor': usingKeyboard && cell.iso === cursor,
              'is-selected': cell.iso === selectedIso
            }"
            :aria-pressed="cell.iso === selectedIso"
            :data-date="cell.iso"
            @click="pick(cell.iso)"
          >
            {{ cell.day }}
          </button>
        </div>

        <div class="form-date-footer">
          <button type="button" class="form-date-link" @click="pick(today)">Today</button>
          <button v-if="!required" type="button" class="form-date-link" @click="clear">
            Clear
          </button>
        </div>
      </div>
    </FormSelectDropdown>
  </BaseFormField>
</template>

<style scoped>
/* The field. Its hand-drawn outline and focus glow come from the ink theme (_forms.scss). */
.form-date {
  position: relative;
  display: flex;
  align-items: flex-start;
  box-sizing: border-box;
  height: 2.875rem;
  padding: calc(1rem + var(--bs-border-width)) calc(0.75rem + var(--bs-border-width)) 0;
  color: var(--bs-body-color);
  cursor: pointer;
}

/* Always floated above the value, like the select fields. */
.form-date-label {
  position: absolute;
  top: 0;
  left: 0;
  padding: 0.5rem 0.75rem;
  border: var(--bs-border-width) solid transparent;
  color: var(--ov-ink-muted);
  white-space: nowrap;
  pointer-events: none;
  transform-origin: 0 0;
  transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
}

/* Only as wide as the date; the rest of the field belongs to the calendar toggle. */
.form-date-text {
  flex: none;
  box-sizing: content-box;
  width: 10ch;
  height: 1.5em;
  margin: 0;
  padding: 0;
  border: 0;
  background: transparent;
  color: inherit;
  font: inherit;
  line-height: 1.5;
  outline: 0;
  box-shadow: none;
  cursor: text;
}

.form-date-text::placeholder {
  color: var(--ov-ink-muted);
  opacity: 0.8;
}

.form-date-icon {
  margin-left: auto;
  color: var(--ov-ink-muted);
  line-height: 1.5;
  pointer-events: none;
}

.form-date--disabled {
  cursor: default;
  opacity: 0.55;
}

.form-date--disabled .form-date-text {
  cursor: default;
}

/* The calendar (teleported into the shared ink dropdown panel). */
.form-date-calendar {
  padding: 0.35rem;
  color: var(--bs-body-color);
  user-select: none;
}

.form-date-nav {
  display: flex;
  align-items: center;
  gap: 0.1rem;
  margin-bottom: 0.35rem;
}

.form-date-title {
  flex: 1 1 auto;
  text-align: center;
  font-weight: 600;
  text-transform: capitalize;
}

.form-date-nav-button,
.form-date-link {
  border: 0;
  background: transparent;
  color: inherit;
  font: inherit;
  cursor: pointer;
}

.form-date-nav-button {
  flex: none;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  line-height: 1;
}

.form-date-nav-button:hover,
.form-date-nav-button:focus-visible {
  background: rgba(var(--ov-ink-rgb), 0.12);
  outline: 0;
}

.form-date-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}

.form-date-weekday {
  padding: 0.2rem 0;
  color: var(--ov-ink-muted);
  font-size: var(--ov-font-size-md);
  text-align: center;
}

.form-date-day {
  display: block;
  min-height: 2rem;
  padding: 0;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: inherit;
  font: inherit;
  line-height: 1;
  cursor: pointer;
}

.form-date-day.is-outside {
  opacity: 0.4;
}

.form-date-day.is-today {
  outline: 1px dashed currentColor;
  outline-offset: -3px;
}

.form-date-day:hover,
.form-date-day.is-cursor {
  background: rgba(var(--ov-ink-rgb), 0.14);
}

.form-date-day.is-selected {
  background: var(--bs-body-color);
  color: var(--bs-body-bg);
  opacity: 1;
}

.form-date-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 0.35rem;
  padding-top: 0.25rem;
}

.form-date-link {
  padding: 0.15rem 0.4rem;
  text-decoration: underline;
}
</style>
