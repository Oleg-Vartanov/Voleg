<script setup lang="ts">
interface Props {
  id: string
  isValid?: boolean | null
  errorText?: string
  helpText?: string
}

withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  helpText: ''
})
</script>

<template>
  <div class="mb-3">
    <slot />

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
