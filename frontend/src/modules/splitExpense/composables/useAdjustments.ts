import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useAdjustments() {
  const topAlerts = useTopAlerts()

  const adjustments = ref<ApiSeAdjustment[] | null>(null)
  const totalCount = ref(0)
  const isLoading = ref(false)
  const isDeleting = ref(false)
  const pageSize = ref(100)
  const hasMore = computed(
    () => adjustments.value !== null && adjustments.value.length < totalCount.value
  )

  async function load(offset: number, append: boolean) {
    if (isLoading.value) return
    isLoading.value = true

    try {
      const response = await client.listSplitExpenseAdjustments(offset, pageSize.value)
      const loaded: ApiSeAdjustment[] = response.data
      adjustments.value = append ? [...(adjustments.value ?? []), ...loaded] : loaded

      const responseTotal = Number(response.headers['x-total-count'])
      totalCount.value = Number.isFinite(responseTotal) ? responseTotal : offset + loaded.length
    } catch {
      topAlerts.add('Failed to load adjustments.', 'danger', 5)
      if (!append) {
        adjustments.value = []
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
    await load(adjustments.value?.length ?? 0, true)
  }

  function replaceAdjustment(adjustment: ApiSeAdjustment) {
    adjustments.value = (adjustments.value ?? []).map((item) =>
      item.id === adjustment.id ? adjustment : item
    )
  }

  async function removeAdjustment(adjustment: ApiSeAdjustment): Promise<boolean> {
    if (isDeleting.value) return false

    isDeleting.value = true
    try {
      await client.deleteSplitExpenseAdjustment(adjustment.id)
      adjustments.value = (adjustments.value ?? []).filter((item) => item.id !== adjustment.id)
      totalCount.value = Math.max(0, totalCount.value - 1)
      topAlerts.add('Adjustment deleted.', 'success', 3)
      return true
    } catch {
      topAlerts.add('Failed to delete adjustment.', 'danger', 5)
      return false
    } finally {
      isDeleting.value = false
    }
  }

  return {
    adjustments,
    totalCount,
    isLoading,
    isDeleting,
    hasMore,
    loadInitial,
    loadMore,
    replaceAdjustment,
    removeAdjustment
  }
}

export type Adjustments = ReturnType<typeof useAdjustments>
