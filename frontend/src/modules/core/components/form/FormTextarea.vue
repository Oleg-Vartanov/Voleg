<script setup lang="ts">
import { computed } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import { validationClass } from '@/modules/core/components/form/types'

interface Props {
  id: string
  label: string
  modelValue: string
  rows?: number
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
  required: true,
  rows: 2
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const fieldValidationClass = computed(() => validationClass(props.isValid))

const onInput = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value)
}
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText" :help-text="helpText">
    <div class="form-floating">
      <textarea
        :id="id"
        class="form-control"
        :class="fieldValidationClass"
        :value="modelValue"
        :rows="rows"
        :aria-describedby="`${id}-validation-feedback`"
        placeholder=""
        :required="required"
        :disabled="disabled"
        @input="onInput"
      ></textarea>

      <label :for="id">{{ label }}</label>
    </div>
  </BaseFormField>
</template>
