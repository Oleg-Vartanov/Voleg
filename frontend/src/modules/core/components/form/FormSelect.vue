<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
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
const searchInputEl = ref<HTMLInputElement | null>(null)
const optionsListEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)
const searchQuery = ref('')
const activeIndex = ref(-1)

const filteredOptions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return props.options

  // Search label text only; option icons are ignored.
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

  const selectedIdx = filteredOptions.value.findIndex(
    (option) => option.value === props.modelValue
  )
  activeIndex.value = selectedIdx >= 0 ? selectedIdx : filteredOptions.value.length > 0 ? 0 : -1

  await nextTick()
  if (props.searchable) {
    searchInputEl.value?.focus()
  }
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

function onSearchKeydown(event: KeyboardEvent): void {
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
    // When focus is already on an option button, let the native click handle it.
    if ((event.target as HTMLElement | null)?.closest?.('.form-select-icons-option')) return
    event.preventDefault()
    const option = options[activeIndex.value]
    if (option) select(option)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    close()
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
  if (!rootEl.value?.contains(event.target as Node)) {
    close()
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div
      ref="rootEl"
      class="form-floating form-select-icons"
      @keydown.esc="close"
      @keydown="!searchable ? onSearchKeydown($event) : undefined"
    >
      <button
        :id="id"
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

      <div v-if="isOpen" class="form-select-icons-dropdown">
        <div v-if="searchable" class="form-select-icons-search">
          <input
            :id="`${id}-search`"
            ref="searchInputEl"
            v-model="searchQuery"
            type="search"
            class="form-control form-control-sm"
            placeholder="Search"
            autocomplete="off"
            :aria-controls="`${id}-options`"
            @keydown="onSearchKeydown"
            @click.stop
          />
        </div>

        <ul
          :id="`${id}-options`"
          ref="optionsListEl"
          class="form-select-icons-options list-unstyled mb-0"
          role="listbox"
        >
          <li v-if="filteredOptions.length === 0" class="form-select-icons-empty">No matches</li>

          <li v-for="(option, index) in filteredOptions" :key="option.value">
            <button
              type="button"
              class="form-select-icons-option"
              :class="{
                active: option.value === modelValue,
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
      </div>
    </div>
  </BaseFormField>
</template>

<style scoped>
.form-select-icons {
  position: relative;
}

.form-select-icons-trigger {
  text-align: left;
}

.form-select-icons-value {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.form-select-icons-dropdown {
  position: absolute;
  top: calc(100% - var(--bs-border-width));
  left: 0;
  z-index: 10;
  width: 100%;
  background: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: 0 0 var(--bs-border-radius) var(--bs-border-radius);
  box-shadow: var(--bs-box-shadow);
}

.form-select-icons-search {
  padding: 0.4rem 0.5rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.form-select-icons-search .form-control {
  min-height: 2rem;
}

.form-select-icons-options {
  max-height: 15rem;
  overflow-y: auto;
}

.form-select-icons-empty {
  padding: 0.5rem 0.75rem;
  color: var(--bs-secondary-color);
  font-size: var(--ov-font-size-sm);
}

.form-select-icons-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.5rem 0.75rem;
  color: var(--bs-body-color);
  text-align: left;
  background: transparent;
  border: 0;
}

.form-select-icons-option:hover,
.form-select-icons-option:focus,
.form-select-icons-option.active,
.form-select-icons-option.is-highlighted {
  color: var(--bs-primary-text-emphasis);
  background: var(--bs-primary-bg-subtle);
  outline: 0;
}
</style>
