<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import FormSelectDropdown from '@/modules/core/components/form/FormSelectDropdown.vue'
import { useFixedDropdown } from '@/modules/core/composables/useFixedDropdown'
import { validationClass, type FormSelectOption } from '@/modules/core/components/form/types'

interface Props {
  id: string
  label: string
  modelValue: string | number | null
  options: FormSelectOption[]
  isValid?: boolean | null
  errorText?: string
  helpText?: string
  disabled?: boolean
  required?: boolean
  searchable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  helpText: '',
  required: true,
  searchable: true
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const fieldValidationClass = computed(() => validationClass(props.isValid))
const selectedOption = computed(() =>
  props.options.find((option) => option.value === props.modelValue)
)

const rootEl = ref<HTMLElement | null>(null)
const triggerEl = ref<HTMLElement | null>(null)
const dropdownEl = ref<HTMLElement | null>(null)
const searchInputEl = ref<HTMLInputElement | null>(null)
const optionsListEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)
const searchQuery = ref('')
const activeIndex = ref(-1)

const { dropdownStyle, isOpenUp, isEventInside } = useFixedDropdown({
  anchorEl: triggerEl,
  rootEl,
  panelEl: dropdownEl,
  isOpen,
  maxHeightPx: 280
})

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return props.options

  return props.options.filter((option) => option.label.toLowerCase().includes(query))
})

watch(filteredOptions, (options) => {
  if (options.length === 0) {
    activeIndex.value = -1
    return
  }

  const selectedIdx = options.findIndex((option) => option.value === props.modelValue)
  activeIndex.value = selectedIdx >= 0 ? selectedIdx : 0
})

watch(isOpen, async (open) => {
  if (!open) {
    searchQuery.value = ''
    activeIndex.value = -1
    return
  }

  const selectedIdx = filteredOptions.value.findIndex((option) => option.value === props.modelValue)
  activeIndex.value = selectedIdx >= 0 ? selectedIdx : filteredOptions.value.length > 0 ? 0 : -1

  await nextTick()
  scrollActiveIntoView()
})

async function toggle(): Promise<void> {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}

function select(option: FormSelectOption): void {
  emit('update:modelValue', option.value)
  isOpen.value = false
}

function close(): void {
  isOpen.value = false
}

function isSearchFocused(): boolean {
  return document.activeElement === searchInputEl.value
}

function isPrintableKey(event: KeyboardEvent): boolean {
  return event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey
}

async function typeToFocusSearch(event: KeyboardEvent): Promise<void> {
  if (!props.searchable || !isOpen.value || isSearchFocused()) return

  if (event.key === 'Backspace') {
    event.preventDefault()
    searchQuery.value = searchQuery.value.slice(0, -1)
    await nextTick()
    searchInputEl.value?.focus()
    return
  }

  if (!isPrintableKey(event)) return

  event.preventDefault()
  searchQuery.value += event.key
  await nextTick()
  searchInputEl.value?.focus()
}

function onDropdownKeydown(event: KeyboardEvent): void {
  if (!isOpen.value) return

  const options = filteredOptions.value
  const count = options.length

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    if (count === 0) return
    activeIndex.value = (activeIndex.value + 1) % count
    scrollActiveIntoView()
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    if (count === 0) return
    activeIndex.value = (activeIndex.value - 1 + count) % count
    scrollActiveIntoView()
  } else if (event.key === 'Enter') {
    if ((event.target as HTMLElement | null)?.closest?.('.form-select-dropdown-option')) return
    event.preventDefault()
    const option = options[activeIndex.value]
    if (option) select(option)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    close()
  } else {
    void typeToFocusSearch(event)
  }
}

function scrollActiveIntoView(): void {
  void nextTick(() => {
    const list = optionsListEl.value
    if (!list || activeIndex.value < 0) return
    const active = list.querySelector<HTMLElement>(`[data-option-index="${activeIndex.value}"]`)
    active?.scrollIntoView({ block: 'nearest' })
  })
}

function onDocumentClick(event: MouseEvent): void {
  if (!isOpen.value) return
  if (isEventInside(event)) return
  close()
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div
      ref="rootEl"
      class="form-floating form-select-icons"
      :class="{
        'form-select-icons--open': isOpen,
        'form-select-icons--up': isOpen && isOpenUp
      }"
      @keydown="onDropdownKeydown"
    >
      <button
        :id="id"
        ref="triggerEl"
        type="button"
        class="form-select form-select-icons-trigger"
        :class="fieldValidationClass"
        :aria-describedby="`${id}-validation-feedback`"
        :aria-expanded="isOpen"
        :aria-controls="`${id}-options`"
        aria-haspopup="listbox"
        :aria-required="required"
        :disabled="disabled"
        @click="toggle"
      >
        <span v-if="selectedOption" class="form-select-icons-value">
          <i
            v-if="selectedOption.icon"
            class="bi"
            :class="selectedOption.icon"
            aria-hidden="true"
          ></i>
          {{ selectedOption.label }}
        </span>
      </button>

      <label :for="id">{{ label }}</label>
    </div>

    <FormSelectDropdown
      v-model:el="dropdownEl"
      :open="isOpen"
      :open-up="isOpenUp"
      :panel-style="dropdownStyle"
      @keydown="onDropdownKeydown"
    >
      <template v-if="searchable" #header>
        <input
          :id="`${id}-search`"
          ref="searchInputEl"
          v-model="searchQuery"
          type="search"
          class="form-select-dropdown-search"
          placeholder="Search"
          autocomplete="off"
          :aria-controls="`${id}-options`"
          @click.stop
          @mousedown.stop
        />
      </template>

      <ul
        :id="`${id}-options`"
        ref="optionsListEl"
        class="form-select-dropdown-options list-unstyled mb-0"
        role="listbox"
      >
        <li v-if="filteredOptions.length === 0" class="form-select-dropdown-empty">No matches</li>

        <li v-for="(option, index) in filteredOptions" :key="option.value">
          <button
            type="button"
            class="form-select-dropdown-option"
            :class="{
              'is-active': option.value === modelValue,
              'is-highlighted': index === activeIndex
            }"
            :data-option-index="index"
            role="option"
            :aria-selected="option.value === modelValue"
            @click="select(option)"
            @mouseenter="activeIndex = index"
          >
            <i v-if="option.icon" class="bi" :class="option.icon" aria-hidden="true"></i>
            <span>{{ option.label }}</span>
          </button>
        </li>
      </ul>
    </FormSelectDropdown>
  </BaseFormField>
</template>

<style scoped>
.form-select-icons {
  position: relative;
}

.form-select-icons-trigger {
  position: relative;
  z-index: 2;
  text-align: left;
}

.form-select-icons-value {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}
</style>
