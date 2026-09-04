<script setup lang="ts">
import AddExpenseModal from '@/modules/splitExpense/components/AddExpenseModal.vue'
import DeleteExpenseModal from '@/modules/splitExpense/components/DeleteExpenseModal.vue'
import EditExpenseModal from '@/modules/splitExpense/components/EditExpenseModal.vue'
import ExpenseDetailPanel from '@/modules/splitExpense/components/ExpenseDetailPanel.vue'
import ExpenseRow from '@/modules/splitExpense/components/ExpenseRow.vue'
import ShowMorePagination from '@/modules/core/components/pagination/ShowMorePagination.vue'
import { useExpenses } from '@/modules/splitExpense/composables/useExpenses'
import { groupExpensesByMonth } from '@/modules/splitExpense/utils/expenseDates'
import { computed, onMounted, ref } from 'vue'
import type { ApiSeExpense } from '@/modules/splitExpense/types'

const EXPENSES_PAGE_SIZE = 25
const expensesState = useExpenses()
const isAddExpenseOpen = ref(false)
const isDeleteModalOpen = ref(false)
const expenseToDelete = ref<ApiSeExpense | null>(null)
const isEditModalOpen = ref(false)
const expenseToEdit = ref<ApiSeExpense | null>(null)

const isEmpty = computed(() => (expensesState.expenses.value?.length ?? 0) === 0)
const expandedExpenseId = ref<number | null>(null)
const expensesByMonth = computed(() => groupExpensesByMonth(expensesState.expenses.value ?? []))

function toggleExpense(expense: ApiSeExpense) {
  expandedExpenseId.value = expandedExpenseId.value === expense.id ? null : expense.id
}

function isExpanded(expense: ApiSeExpense) {
  return expandedExpenseId.value === expense.id
}

function requestEdit(expense: ApiSeExpense) {
  expenseToEdit.value = expense
  isEditModalOpen.value = true
}

function onUpdated(expense: ApiSeExpense) {
  expensesState.replaceExpense(expense)
  expenseToEdit.value = null
}

function requestDelete(expense: ApiSeExpense) {
  expenseToDelete.value = expense
  isDeleteModalOpen.value = true
}

async function confirmDelete() {
  const expense = expenseToDelete.value
  if (!expense) return
  if (!(await expensesState.removeExpense(expense))) return

  isDeleteModalOpen.value = false
  expenseToDelete.value = null
  if (expandedExpenseId.value === expense.id) {
    expandedExpenseId.value = null
  }
}

onMounted(() => {
  expensesState.loadInitial(EXPENSES_PAGE_SIZE)
})
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column align-items-center gap-2">
      <div class="se-panel">
        <button
          type="button"
          class="btn btn-outline-primary w-100"
          @click="isAddExpenseOpen = true"
        >
          <i class="bi bi-plus-lg" aria-hidden="true"></i>
          Add expense
        </button>

        <AddExpenseModal
          v-model:open="isAddExpenseOpen"
          @created="expensesState.loadInitial(EXPENSES_PAGE_SIZE)"
        />

        <EditExpenseModal
          v-model:open="isEditModalOpen"
          :expense="expenseToEdit"
          @updated="onUpdated"
        />

        <DeleteExpenseModal
          v-model:open="isDeleteModalOpen"
          :expense="expenseToDelete"
          :deleting="expensesState.isDeleting.value"
          @confirm="confirmDelete"
        />

        <div
          v-if="expensesState.isLoading.value && expensesState.expenses.value === null"
          class="text-center py-3"
        >
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
                v-for="expense in group.expenses"
                :key="expense.id"
                class="expense-item"
                :class="{ 'is-expanded': isExpanded(expense) }"
              >
                <ExpenseRow :expense="expense" @toggle="toggleExpense(expense)" />
                <ExpenseDetailPanel
                  :expense="expense"
                  :open="isExpanded(expense)"
                  :deleting="expensesState.isDeleting.value"
                  @edit="requestEdit(expense)"
                  @delete="requestDelete(expense)"
                />
              </article>
            </template>
          </div>

          <ShowMorePagination
            :has-more="expensesState.hasMore.value"
            :is-loading="expensesState.isLoading.value"
            aria-label="Load more expenses"
            @load-more="expensesState.loadMore"
          />
        </div>
      </div>
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
