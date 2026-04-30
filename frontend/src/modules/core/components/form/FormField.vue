<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  id: string
  label: string
  modelValue: string
  type?: string
  error?: string
  isValidationError?: boolean
  helpText?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  error: '',
  isValidationError: false,
  helpText: ''
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const validationClass = computed(() => {
  if (!props.isValidationError) return ''
  return props.error ? 'is-invalid' : 'is-valid'
})
</script>

<template>
  <div class="form-floating mb-3">
    <input
      :id="id"
      :type="type"
      class="form-control"
      :class="validationClass"
      :value="modelValue"
      :aria-describedby="`${id}-validation-feedback`"
      placeholder=""
      required
      :disabled="disabled"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />

    <label :for="id">{{ label }}</label>

    <div
      v-if="isValidationError && error"
      :id="`${id}-validation-feedback`"
      class="invalid-feedback"
      style="white-space: pre-line"
    >
      {{ error }}
    </div>

    <div v-if="helpText" class="form-text">
      {{ helpText }}
    </div>
  </div>
</template>
