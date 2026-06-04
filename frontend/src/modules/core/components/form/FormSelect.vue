<script setup lang="ts">
import { computed } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import {
  validationClass,
  type FormSelectOption
} from '@/modules/core/components/form/types'

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

const onChange = (event: Event) => {
  const rawValue = (event.target as HTMLSelectElement).value
  const option = props.options.find((entry) => String(entry.value) === rawValue)
  emit('update:modelValue', option?.value ?? rawValue)
}
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div class="form-floating">
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
