<script setup lang="ts">
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'
import { formatShortDate } from '@/modules/splitExpense/utils/expenseDates'
import { useAdjustmentDisplay } from '@/modules/splitExpense/composables/useAdjustmentDisplay'
import { computed } from 'vue'

const props = defineProps<{
  adjustment: ApiSeAdjustment
}>()

const emit = defineEmits<{ toggle: [] }>()

const display = useAdjustmentDisplay()
const date = computed(() => formatShortDate(props.adjustment.adjustmentDate))
</script>

<template>
  <div class="adjustment-row" role="button" tabindex="0" @click="emit('toggle')">
    <div class="adjustment-date-cell">
      <span class="adjustment-date">
        <span class="adjustment-date-day">{{ date.day }}</span>
        <span class="adjustment-date-month">{{ date.month }}</span>
      </span>
    </div>
    <div class="adjustment-type-cell">
      <i class="bi bi-arrow-left-right fs-4" aria-hidden="true"></i>
    </div>
    <div class="adjustment-title-cell">
      <span class="adjustment-title-text">{{ display.title(adjustment) }}</span>
    </div>
    <div class="adjustment-amount-cell" :class="display.amountColor(adjustment)">
      {{ display.currentUserBalance(adjustment) }}
    </div>
  </div>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.adjustment-row {
  --adjustments-col-date: 2.75rem;
  --adjustments-col-type: 2.75rem;

  display: grid;
  grid-template-columns: var(--adjustments-col-date) var(--adjustments-col-type) minmax(0, 1fr) auto;
  width: 100%;
  cursor: pointer;
  user-select: none;
}

.adjustment-row:hover {
  background-color: var(--bs-table-hover-bg);
  color: var(--bs-table-hover-color);
}

.adjustment-date-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  white-space: normal;
  text-align: center;
  padding: 3px 2px 3px 0;
}

.adjustment-type-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 0;
}

.adjustment-amount-cell {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-inline: 0.15rem 0.5rem;
  text-align: end;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.adjustment-title-cell {
  display: flex;
  align-items: center;
  text-align: start;
  min-width: 0;
  overflow: hidden;
}

.adjustment-title-text {
  flex: 1 1 auto;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.adjustment-date {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  line-height: 1.05;
}

.adjustment-date-day {
  font-weight: 500;
  font-size: 1.25rem;
  font-variant-numeric: tabular-nums;
  line-height: 1;
  color: var(--bs-secondary-color);
}

.adjustment-date-month {
  font-weight: 500;
  font-size: 0.64rem;
  text-transform: uppercase;
  color: var(--bs-secondary-color);
}
</style>
