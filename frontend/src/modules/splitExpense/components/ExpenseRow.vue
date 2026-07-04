<script setup lang="ts">
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { formatShortDate } from '@/modules/splitExpense/utils/expenseDates'
import { useExpenseDisplay } from '@/modules/splitExpense/composables/useExpenseDisplay'
import { computed } from 'vue'

const props = defineProps<{
  expense: ApiSeExpense
}>()

const emit = defineEmits<{ toggle: [] }>()

const ed = useExpenseDisplay()
const date = computed(() => formatShortDate(props.expense.expenseDate))
</script>

<template>
  <div
    class="expense-row"
    role="button"
    tabindex="0"
    @click="emit('toggle')"
  >
    <div class="expense-date-cell">
      <span class="expense-date">
        <span class="expense-date-day">{{ date.day }}</span>
        <span class="expense-date-month">{{ date.month }}</span>
      </span>
    </div>
    <div class="expense-type-cell">
      <i class="bi fs-4" :class="ed.mapCategory(expense).icon"></i>
    </div>
    <div class="expense-title-cell">
      <span class="expense-title-text">{{ expense.title }}</span>
    </div>
    <div class="expense-amount-cell" :class="ed.amountColor(expense)">
      {{ ed.currentUserSplitBalance(expense) }}
    </div>
  </div>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.expense-row {
  --expenses-col-date: 2.75rem;
  --expenses-col-type: 2.75rem;

  display: grid;
  grid-template-columns: var(--expenses-col-date) var(--expenses-col-type) minmax(0, 1fr) auto;
  width: 100%;
  cursor: pointer;
  user-select: none;
}

.expense-row:hover {
  background-color: var(--bs-table-hover-bg);
  color: var(--bs-table-hover-color);
}

.expense-date-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  white-space: normal;
  text-align: center;
  padding: 3px 2px 3px 0;
}

.expense-type-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 0;
}

.expense-amount-cell {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-inline: 0.15rem 0.5rem;
  text-align: end;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.expense-title-cell {
  display: flex;
  align-items: center;
  text-align: start;
  min-width: 0;
  overflow: hidden;
}

.expense-title-text {
  flex: 1 1 auto;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.expense-date {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  line-height: 1.05;
}

.expense-date-day {
  font-weight: 500;
  font-size: 1.25rem;
  font-variant-numeric: tabular-nums;
  line-height: 1;
  color: var(--bs-secondary-color);
}

.expense-date-month {
  font-weight: 500;
  font-size: 0.64rem;
  text-transform: uppercase;
  color: var(--bs-secondary-color);
}
</style>
