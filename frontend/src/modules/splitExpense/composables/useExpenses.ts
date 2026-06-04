import { ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useExpenses() {
  const topAlerts = useTopAlerts()

  const expenses = ref<ApiSeExpense[] | null>(null)
  const isLoading = ref(false)

  async function load() {
    if (isLoading.value) return
    isLoading.value = true

    client
      .listSplitExpenses()
      .then((response) => {
        expenses.value = response.data
      })
      .catch(() => {
        topAlerts.add('Failed to load expenses.', 'danger', 5)
        expenses.value = []
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  return {
    expenses,
    isLoading,
    load
  }
}

export type Expenses = ReturnType<typeof useExpenses>
