<script setup lang="ts">
withDefaults(
  defineProps<{
    hasMore: boolean
    isLoading?: boolean
    ariaLabel?: string
  }>(),
  {
    isLoading: false,
    ariaLabel: 'Load more items'
  }
)

defineEmits<{
  loadMore: []
}>()
</script>

<template>
  <div v-if="hasMore" class="d-flex justify-content-center mt-3">
    <button
      type="button"
      class="btn btn-outline-primary"
      :disabled="isLoading"
      :aria-label="ariaLabel"
      @click="$emit('loadMore')"
    >
      <span
        v-if="isLoading"
        class="spinner-border spinner-border-sm me-1"
        aria-hidden="true"
      ></span>
      {{ isLoading ? 'Loading…' : 'Show more' }}
    </button>
  </div>
</template>
