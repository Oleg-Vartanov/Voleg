<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import { validationClass } from '@/modules/core/components/form/types'

interface Props {
  id: string
  label: string
  modelValue: string
  type?: string
  isValid?: boolean | null
  errorText?: string
  helpText?: string
  disabled?: boolean
  required?: boolean
  min?: string | number
  step?: string | number
  inputmode?: string
  placeholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  isValid: null,
  errorText: '',
  helpText: '',
  required: true,
  placeholder: ''
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'beforeinput', event: InputEvent): void
}>()

const showPassword = ref(false)

const inputType = computed(() => {
  if (props.type !== 'password') return props.type
  return showPassword.value ? 'text' : 'password'
})

const fieldValidationClass = computed(() => validationClass(props.isValid))

const eyeOffsetClass = computed(() => {
  return props.isValid !== null ? 'me-4' : 'me-2'
})

const onInput = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div class="form-floating position-relative">
      <input
        :id="id"
        :type="inputType"
        class="form-control"
        :class="fieldValidationClass"
        :value="modelValue"
        :placeholder="placeholder"
        :min="min"
        :step="step"
        :inputmode="inputmode"
        :aria-describedby="`${id}-validation-feedback`"
        :required="required"
        :disabled="disabled"
        @beforeinput="emit('beforeinput', $event)"
        @input="onInput"
      />

      <label :for="id">{{ label }}</label>

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
  </BaseFormField>
</template>
