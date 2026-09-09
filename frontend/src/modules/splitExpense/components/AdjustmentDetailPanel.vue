<script setup lang="ts">
import { useAdjustmentDisplay } from '@/modules/splitExpense/composables/useAdjustmentDisplay'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'
import { formatFullDate } from '@/modules/splitExpense/utils/expenseDates'

defineProps<{
  adjustment: ApiSeAdjustment
  open: boolean
  deleting?: boolean
}>()

const emit = defineEmits<{
  edit: []
  delete: []
}>()

const display = useAdjustmentDisplay()
</script>

<template>
  <div class="adjustment-detail-panel" :class="{ 'is-open': open }">
    <div class="adjustment-detail-collapse">
      <div class="adjustment-detail-inner">
        <div class="adjustment-detail-content">
          <p class="adjustment-detail-title">
            <strong>{{ display.title(adjustment) }}</strong>
          </p>

          <p v-if="adjustment.description" class="adjustment-detail-description">
            {{ adjustment.description }}
          </p>

          <p class="adjustment-detail-meta">
            Added by <strong>{{ adjustment.createdByUser.username }}</strong> on
            {{ formatFullDate(adjustment.adjustmentDate) }}
          </p>

          <button
            type="button"
            class="btn btn-outline-secondary btn-sm adjustment-detail-action"
            @click.stop="emit('edit')"
          >
            Edit
          </button>
        </div>

        <div class="adjustment-detail-side">
          <p class="adjustment-split-balance">
            Your balance
            <strong :class="display.amountColor(adjustment)">{{
              display.currentUserBalance(adjustment)
            }}</strong>
          </p>

          <p class="adjustment-party">
            <strong>{{ adjustment.createdByUser.username }}</strong>
            {{ display.userBalance(adjustment, adjustment.createdByUser.id) }}
          </p>
          <p class="adjustment-party">
            <strong>{{ adjustment.otherUser.username }}</strong>
            {{ display.userBalance(adjustment, adjustment.otherUser.id) }}
          </p>

          <button
            type="button"
            class="btn btn-outline-danger btn-sm adjustment-detail-action"
            :disabled="deleting"
            @click.stop="emit('delete')"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.adjustment-detail-panel {
  overflow: hidden;
}

.adjustment-detail-panel.is-open {
  background-color: var(--bs-tertiary-bg);
}

.adjustment-detail-collapse {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s ease;
}

.adjustment-detail-panel.is-open .adjustment-detail-collapse {
  grid-template-rows: 1fr;
}

.adjustment-detail-inner {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 0.75rem 0;
  align-items: stretch;
  min-width: 0;
  overflow: hidden;
  overflow-wrap: anywhere;
  padding: 0;
  opacity: 0;
  transition:
    opacity 0.3s ease,
    padding 0.3s ease;
}

.adjustment-detail-content,
.adjustment-detail-side {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
  min-width: 0;
}

.adjustment-detail-content {
  align-items: flex-end;
  text-align: end;
  padding-inline-end: 1rem;
  border-inline-end: 1px solid var(--bs-border-color-translucent);
}

.adjustment-detail-side {
  align-items: flex-start;
  text-align: start;
  padding-inline-start: 1rem;
}

.adjustment-detail-panel.is-open .adjustment-detail-inner {
  padding: 0.75rem 1rem;
  opacity: 1;
  transition:
    opacity 0.25s ease 0.05s,
    padding 0.3s ease;
}

.btn.adjustment-detail-action {
  margin-top: auto;
  box-sizing: border-box;
  width: 4.75rem;
  min-height: 0;
  padding: 0.05rem 0.35rem;
  font-size: 0.85rem;
  line-height: 1.2;
  text-align: center;
}

.adjustment-detail-title {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
}

.adjustment-detail-description {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
  color: var(--bs-secondary-color);
}

.adjustment-detail-meta,
.adjustment-split-balance,
.adjustment-party {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
}

@media (max-width: 767.98px) {
  .adjustment-detail-inner {
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
  }

  .adjustment-detail-content {
    align-items: flex-start;
    text-align: start;
    padding-inline-end: 0;
    padding-bottom: 0.75rem;
    border-inline-end: none;
    border-bottom: 1px solid var(--bs-border-color-translucent);
  }

  .adjustment-detail-side {
    padding-inline-start: 0;
  }
}
</style>
