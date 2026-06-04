<script setup lang="ts">
defineProps<{ label: string }>()
</script>

<template>
  <div class="expense-detail-field">
    <div class="expense-detail-label">{{ label }}</div>
    <div class="expense-detail-value">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.expense-detail-label {
  margin-bottom: 0.15rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--bs-secondary-color);
}

.expense-detail-value {
  font-size: 0.9rem;
}
</style>
