<script setup lang="ts">
import AddExpenseModal from '@/modules/splitExpense/components/AddExpenseModal.vue'
import ExpenseDetailPanel from '@/modules/splitExpense/components/ExpenseDetailPanel.vue'
import ExpenseRow from '@/modules/splitExpense/components/ExpenseRow.vue'
import { useExpenses } from '@/modules/splitExpense/composables/useExpenses'
import { groupExpensesByMonth } from '@/modules/splitExpense/utils/expenseDates'
import { computed, onMounted, ref } from 'vue'
import type { ApiSeExpense } from '@/modules/splitExpense/types.ts';

const expensesState = useExpenses()

const isEmpty = computed(() => (expensesState.expenses.value?.length ?? 0) === 0)
const expandedExpenseId = ref<number | null>(null)
const expensesByMonth = computed(() => groupExpensesByMonth(expensesState.expenses.value ?? []))

function toggleExpense(expense: ApiSeExpense) {
  expandedExpenseId.value = expandedExpenseId.value === expense.id ? null : expense.id
}

function isExpanded(expense: ApiSeExpense) {
  return expandedExpenseId.value === expense.id
}

onMounted(() => {
  expensesState.load()
})
</script>

<template>
  <button
    type="button"
    class="btn btn-outline-primary w-100"
    data-bs-toggle="modal"
    data-bs-target="#addExpenseModal"
  >
    <i class="bi bi-plus-lg" aria-hidden="true"></i>
    Add expense
  </button>

  <AddExpenseModal :expenses="expensesState" />

  <div v-if="expensesState.isLoading.value" class="text-center py-3">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading expenses…</span>
    </div>
  </div>

  <div v-else class="expenses-list-scroll">
    <div class="expenses-list">
      <p v-if="isEmpty" class="expenses-empty">No expenses yet</p>

      <template v-for="group in expensesByMonth" :key="group.month">
        <div class="expenses-month-header">{{ group.label }}</div>

        <article
          v-for="expense in group.items"
          :key="expense.id"
          class="expense-item"
          :class="{ 'is-expanded': isExpanded(expense) }"
        >
          <ExpenseRow
            :expense="expense"
            :expanded="isExpanded(expense)"
            @toggle="toggleExpense(expense)"
          />
          <ExpenseDetailPanel :expense="expense" :open="isExpanded(expense)" />
        </article>
      </template>
    </div>
  </div>
</template>

<style scoped>
.expenses-list-scroll {
  -webkit-overflow-scrolling: touch;
  max-width: 100%;
}

.expenses-list {
  width: 100%;
  --bs-table-hover-bg: rgba(var(--bs-emphasis-color-rgb), 0.075);
  --bs-table-hover-color: var(--bs-emphasis-color);
}

.expenses-empty {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
  opacity: 0.9;
}

.expenses-month-header {
  padding: 0.2rem 0.5rem;
  background-color: var(--bs-secondary-bg);
  font-weight: 600;
  font-size: 0.85rem;
  line-height: 1.2;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.expense-item:not(.is-expanded) :deep(.expense-row) {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.expense-item.is-expanded {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}
</style>
