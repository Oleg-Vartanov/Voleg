import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import type { ApiSeCategoryTotal, ApiSeInsights } from '@/modules/splitExpense/types'

export function useInsights() {
  const topAlerts = useTopAlerts()

  const categoryTotals = ref<ApiSeCategoryTotal[]>([])
  const totalMonthCount = ref(0)
  const isLoading = ref(false)
  const isLoadingMore = ref(false)
  const isLoaded = ref(false)
  const hasError = ref(false)
  const activeCategoryId = ref<number | null>(null)
  let lastRequestId = 0

  const isEmpty = computed(() => isLoaded.value && categoryTotals.value.length === 0)
  const loadedMonthCount = computed(
    () => new Set(categoryTotals.value.map((total) => total.month)).size
  )
  const hasMore = computed(() => loadedMonthCount.value < totalMonthCount.value)

  async function load(offset: number, limit: number, append: boolean): Promise<void> {
    if (append && isLoading.value) return

    const requestId = ++lastRequestId
    isLoading.value = true
    isLoadingMore.value = append
    if (!append) {
      hasError.value = false
      isLoaded.value = false
      categoryTotals.value = []
      totalMonthCount.value = 0
    }

    try {
      const response = await client.getSplitExpenseInsights(offset, limit, activeCategoryId.value)
      if (requestId !== lastRequestId) return

      const insights = response.data as ApiSeInsights
      categoryTotals.value = append
        ? [...categoryTotals.value, ...insights.categoryTotals]
        : insights.categoryTotals
      totalMonthCount.value = insights.totalMonthCount
      isLoaded.value = true
    } catch {
      if (requestId !== lastRequestId) return

      if (append) {
        topAlerts.add('Failed to load more insights.', 'danger', 5)
      } else {
        hasError.value = true
        isLoaded.value = true
      }
    } finally {
      if (requestId === lastRequestId) {
        isLoading.value = false
        isLoadingMore.value = false
      }
    }
  }

  function loadInitial(limit = 6, categoryId: number | null = null): Promise<void> {
    activeCategoryId.value = categoryId
    return load(0, limit, false)
  }

  function loadMore(limit = 6): Promise<void> {
    if (!hasMore.value || isLoading.value) return Promise.resolve()
    return load(loadedMonthCount.value, limit, true)
  }

  return {
    categoryTotals,
    totalMonthCount,
    isLoading,
    isLoadingMore,
    isLoaded,
    hasError,
    isEmpty,
    hasMore,
    loadInitial,
    loadMore
  }
}

export type Insights = ReturnType<typeof useInsights>
