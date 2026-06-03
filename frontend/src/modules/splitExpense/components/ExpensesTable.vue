<script setup lang="ts">
import  { useExpenses } from '@/modules/splitExpense/composables/useExpenses'
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { computed } from 'vue'
import { categories } from '@/modules/splitExpense/categories.ts';

type ExpenseMonthGroup = { month: string, label: string, items: ApiSeExpense[] }

const expensesState = useExpenses()
expensesState.load()

function formatMonthLabel(key: string): string {
  const [year, month] = key.split('-').map(Number)
  if (!year || !month) return key
  return new Date(year, month - 1, 1).toLocaleDateString(undefined, {
    month: 'long',
    year: 'numeric'
  })
}

function formatDate(value: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString(undefined, { day: 'numeric', month: 'short' })
}

function formatAmount(expense: ApiSeExpense): string {
  return `${expense.amountDisplay} ${expense.currency.symbol}`
}

const expensesByMonth = computed((): ExpenseMonthGroup[] => {
  const list = expensesState.expenses.value ?? []
  const byMonth = new Map<string, ApiSeExpense[]>()

  for (const expense of list) {
    const key = monthKey(expense.expenseDate)
    const group = byMonth.get(key)
    if (group) {
      group.push(expense)
    } else {
      byMonth.set(key, [expense])
    }
  }

  const groups: ExpenseMonthGroup[] = []
  const seen = new Set<string>()

  for (const expense of list) {
    const key = monthKey(expense.expenseDate)
    if (seen.has(key)) continue
    seen.add(key)
    groups.push({
      month: key,
      label: formatMonthLabel(key),
      items: byMonth.get(key) ?? []
    })
  }

  return groups
})

function monthKey(value: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}
</script>

<template>
  <div v-if="expensesState.isLoading.value" class="text-center py-3">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading expenses…</span>
    </div>
  </div>

  <div v-else class="table-responsive expenses-table-scroll">
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th scope="col">Date</th>
          <th scope="col">Category</th>
          <th scope="col">Title</th>
          <th scope="col">Amount</th>
          <th scope="col">Paid by</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="expensesState.expenses.value?.length === 0">
          <td colspan="5" class="text-center py-3 text-muted">No expenses yet</td>
        </tr>
        <template v-for="group in expensesByMonth" :key="group.month">
          <tr class="month-group-row no-hover">
            <th colspan="5" scope="colgroup">{{ group.label }}</th>
          </tr>
          <tr v-for="expense in group.items" :key="expense.id">
            <td>{{ formatDate(expense.expenseDate) }}</td>
            <td>
              <i class="bi fs-5" :class="categories[expense.category?.tag ?? 'other']"></i>

            </td>
            <td>{{ expense.title }}</td>
            <td>{{ formatAmount(expense) }}</td>
            <td>{{ expense.paidByUser.displayName }}</td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.expenses-table-scroll {
  -webkit-overflow-scrolling: touch;
}

.expenses-table-scroll .table {
  width: max-content;
  min-width: 100%;
}

.expenses-table-scroll .table :is(th, td) {
  white-space: nowrap;
}

.text-muted {
  opacity: 0.9;
}

.month-group-row th {
  background-color: var(--bs-secondary-bg);
  font-weight: 600;
  font-size: 0.9rem;
  border-bottom-width: 1px;
  text-align: left;
}

.table-hover > tbody > tr.no-hover:hover > td,
.table-hover > tbody > tr.no-hover:hover > th {
  box-shadow: none;
}
</style>
