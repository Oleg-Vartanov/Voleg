<script setup lang="ts">
import { computed, watch } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import { validationClass } from '@/modules/core/components/form/types'
import moneyUtils from '@/modules/core/utils/moneyUtils'

interface Props {
  id: string
  label: string
  modelValue: string
  decimalPlaces: number
  isValid?: boolean | null
  errorText?: string
  helpText?: string
  disabled?: boolean
  required?: boolean
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  helpText: '',
  required: true,
  placeholder: ''
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

function sanitize(raw: string): string {
  return moneyUtils.sanitizeDecimalPlaces(raw, props.decimalPlaces)
}

const fieldValidationClass = computed(() => validationClass(props.isValid))

watch(
  () => props.decimalPlaces,
  () => {
    const sanitized = sanitize(props.modelValue)
    if (sanitized !== props.modelValue) {
      emit('update:modelValue', sanitized)
    }
  }
)

function onInput(event: Event) {
  const input = event.target as HTMLInputElement
  const sanitized = sanitize(input.value)

  if (input.value !== sanitized) {
    input.value = sanitized
  }

  emit('update:modelValue', sanitized)
}
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div class="form-floating">
      <input
        :id="id"
        type="text"
        inputmode="decimal"
        class="form-control"
        :class="fieldValidationClass"
        :value="modelValue"
        :placeholder="placeholder"
        :aria-describedby="`${id}-validation-feedback`"
        :required="required"
        :disabled="disabled"
        @input="onInput"
      />
      <label :for="id">{{ label }}</label>
    </div>
  </BaseFormField>
</template>
