import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useExpenses() {
  const topAlerts = useTopAlerts()

  const expenses = ref<ApiSeExpense[] | null>(null)
  const totalCount = ref(0)
  const isLoading = ref(false)
  const isDeleting = ref(false)
  const pageSize = ref(100)
  const hasMore = computed(
    () => expenses.value !== null && expenses.value.length < totalCount.value
  )

  async function load(offset: number, append: boolean) {
    if (isLoading.value) return
    isLoading.value = true

    try {
      const response = await client.listSplitExpenses(offset, pageSize.value)
      const loadedExpenses: ApiSeExpense[] = response.data
      expenses.value = append ? [...(expenses.value ?? []), ...loadedExpenses] : loadedExpenses

      const responseTotal = Number(response.headers['x-total-count'])
      totalCount.value = Number.isFinite(responseTotal)
        ? responseTotal
        : offset + loadedExpenses.length
    } catch {
      topAlerts.add('Failed to load expenses.', 'danger', 5)
      if (!append) {
        expenses.value = []
        totalCount.value = 0
      }
    } finally {
      isLoading.value = false
    }
  }

  async function loadInitial(limit = 100) {
    pageSize.value = limit
    await load(0, false)
  }

  async function loadMore() {
    if (!hasMore.value) return
    await load(expenses.value?.length ?? 0, true)
  }

  function replaceExpense(expense: ApiSeExpense) {
    expenses.value = (expenses.value ?? []).map((item) => (item.id === expense.id ? expense : item))
  }

  async function removeExpense(expense: ApiSeExpense): Promise<boolean> {
    if (isDeleting.value) return false

    isDeleting.value = true
    try {
      await client.deleteSplitExpense(expense.id)
      expenses.value = (expenses.value ?? []).filter((item) => item.id !== expense.id)
      totalCount.value = Math.max(0, totalCount.value - 1)
      topAlerts.add('Expense deleted.', 'success', 3)
      return true
    } catch {
      topAlerts.add('Failed to delete expense.', 'danger', 5)
      return false
    } finally {
      isDeleting.value = false
    }
  }

  return {
    expenses,
    totalCount,
    isLoading,
    isDeleting,
    hasMore,
    loadInitial,
    loadMore,
    replaceExpense,
    removeExpense
  }
}

export type Expenses = ReturnType<typeof useExpenses>
