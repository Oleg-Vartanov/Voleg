<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
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
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  helpText: '',
  required: true
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const fieldValidationClass = computed(() => validationClass(props.isValid))
const hasIcons = computed(() => props.options.some((option) => option.icon))
const selectedOption = computed(() =>
  props.options.find((option) => option.value === props.modelValue)
)
const rootEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)

const onChange = (event: Event) => {
  const rawValue = (event.target as HTMLSelectElement).value
  const option = props.options.find((entry) => String(entry.value) === rawValue)
  emit('update:modelValue', option?.value ?? rawValue)
}

function toggle(): void {
  if (!props.disabled) {
    isOpen.value = !isOpen.value
  }
}

function select(option: FormSelectOption): void {
  emit('update:modelValue', option.value)
  isOpen.value = false
}

function onDocumentClick(event: MouseEvent): void {
  if (!rootEl.value?.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div
      v-if="hasIcons"
      ref="rootEl"
      class="form-floating form-select-icons"
      @keydown.esc="isOpen = false"
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
          <i class="bi" :class="selectedOption.icon" aria-hidden="true"></i>
          {{ selectedOption.label }}
        </span>
      </button>

      <label :for="id">{{ label }}</label>

      <ul
        v-if="isOpen"
        :id="`${id}-options`"
        class="form-select-icons-options list-unstyled mb-0"
        role="listbox"
      >
        <li v-for="option in options" :key="option.value">
          <button
            type="button"
            class="form-select-icons-option"
            :class="{ active: option.value === modelValue }"
            role="option"
            :aria-selected="option.value === modelValue"
            @click="select(option)"
          >
            <i class="bi" :class="option.icon" aria-hidden="true"></i>
            <span>{{ option.label }}</span>
          </button>
        </li>
      </ul>
    </div>

    <div v-else class="form-floating">
      <select
        :id="id"
        class="form-select"
        :class="fieldValidationClass"
        :value="modelValue ?? ''"
        :aria-describedby="`${id}-validation-feedback`"
        :required="required"
        :disabled="disabled"
        @change="onChange"
      >
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>

      <label :for="id">{{ label }}</label>
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

.form-select-icons-options {
  position: absolute;
  top: calc(100% - var(--bs-border-width));
  left: 0;
  z-index: 10;
  width: 100%;
  max-height: 15rem;
  overflow-y: auto;
  background: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: 0 0 var(--bs-border-radius) var(--bs-border-radius);
  box-shadow: var(--bs-box-shadow);
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
.form-select-icons-option.active {
  color: var(--bs-primary-text-emphasis);
  background: var(--bs-primary-bg-subtle);
  outline: 0;
}
</style>
