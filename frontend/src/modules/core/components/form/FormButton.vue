<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'outline-primary' | 'outline-secondary' | 'danger' | 'link'
  loading?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  loading: false,
  disabled: false
})

const btnClass = computed(() => ['btn', `btn-${props.variant}`, 'w-100 py-2 mb-3'])
</script>

<template>
  <button :type="type" :class="btnClass" :disabled="disabled || loading">
    <span v-if="loading">
      <span class="spinner-border spinner-border-sm me-2" role="status" />
    </span>
    <span v-else>
      {{ label }}
    </span>
  </button>
</template>
