<script setup lang="ts">
import { computed, ref } from 'vue'

interface Props {
  id: string
  label: string
  modelValue: string
  type?: string
  isValid?: boolean
  errorText?: string
  helpText?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  isValid: null,
  errorText: '',
  helpText: ''
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type !== 'password') return props.type
  return showPassword.value ? 'text' : 'password'
})
const eyeOffsetClass = computed(() => {
  return props.isValid !== null ? 'me-4' : 'me-2'
})

const validationClass = computed(() => {
  if (props.isValid === null) return ''
  return !props.isValid ? 'is-invalid' : 'is-valid'
})
</script>

<template>
  <div class="mb-3">
    <div class="form-floating position-relative">
      <input
        :id="id"
        :type="inputType"
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

      <!-- 👁 Password toggle -->
      <button
        v-if="type === 'password'"
        type="button"
        :class="['btn btn-sm position-absolute end-0 top-50 translate-middle-y', eyeOffsetClass]"
        tabindex="-1"
        @click="showPassword = !showPassword"
      >
        <i :class="showPassword ? 'bi bi-eye' : 'bi bi-eye-slash'"></i>
      </button>
    </div>

    <div
      v-if="!isValid && errorText"
      :id="`${id}-validation-feedback`"
      class="invalid-feedback d-block"
      style="white-space: pre-line"
    >
      {{ errorText }}
    </div>

    <div v-if="helpText" class="form-text">
      {{ helpText }}
    </div>
  </div>
</template>
