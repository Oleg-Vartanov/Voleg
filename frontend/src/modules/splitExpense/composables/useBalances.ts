import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type {
  ApiSeBalance,
  ApiSeBalanceAmount,
  ApiSeUserBalance
} from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useBalances() {
  const topAlerts = useTopAlerts()

  const totalAmounts = ref<ApiSeBalanceAmount[]>([])
  const byUserAmounts = ref<ApiSeUserBalance[]>([])
  const isLoading = ref(false)
  /** Whether a first attempt has finished, successfully or not. */
  const isLoaded = ref(false)
  const hasError = ref(false)

  const isSettled = computed(() => byUserAmounts.value.length === 0)

  async function load() {
    if (isLoading.value) return
    isLoading.value = true
    hasError.value = false

    try {
      const response = await client.getSplitExpenseBalances()
      const balance: ApiSeBalance = response.data
      totalAmounts.value = balance.totalAmounts
      byUserAmounts.value = balance.byUserAmounts
    } catch {
      hasError.value = true
      topAlerts.add('Failed to load balances.', 'danger', 5)
      totalAmounts.value = []
      byUserAmounts.value = []
    } finally {
      isLoading.value = false
      isLoaded.value = true
    }
  }

  return {
    totalAmounts,
    byUserAmounts,
    isLoading,
    isLoaded,
    hasError,
    isSettled,
    load
  }
}

export type Balances = ReturnType<typeof useBalances>
