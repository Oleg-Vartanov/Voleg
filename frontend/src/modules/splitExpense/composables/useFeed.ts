import { computed, reactive, ref, watch } from 'vue'
import client from '@/modules/core/apiClient'
import type {
  ApiSeAdjustment,
  ApiSeExpense,
  ApiSeFeedItem,
  SeFeedFilters,
  SeFeedType
} from '@/modules/splitExpense/types'
import { feedItemKey } from '@/modules/splitExpense/utils/feed'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useFeed() {
  const topAlerts = useTopAlerts()

  const items = ref<ApiSeFeedItem[] | null>(null)
  const totalCount = ref(0)
  const isLoading = ref(false)
  const isDeleting = ref(false)
  const pageSize = ref(100)
  const filters = reactive<SeFeedFilters>({ expense: true, adjustment: true })
  const hasMore = computed(() => items.value !== null && items.value.length < totalCount.value)

  // Only the latest request may write the list, so a filter change isn't overwritten by an older page.
  let lastRequestId = 0

  async function load(offset: number, append: boolean) {
    const requestId = ++lastRequestId
    const types = (Object.keys(filters) as SeFeedType[]).filter((type) => filters[type])

    if (types.length === 0) {
      items.value = []
      totalCount.value = 0
      isLoading.value = false
      return
    }

    isLoading.value = true

    try {
      const response = await client.listSplitExpenseFeed(offset, pageSize.value, types)
      if (requestId !== lastRequestId) return

      const loaded: ApiSeFeedItem[] = response.data
      items.value = append ? [...(items.value ?? []), ...loaded] : loaded

      const responseTotal = Number(response.headers['x-total-count'])
      totalCount.value = Number.isFinite(responseTotal) ? responseTotal : offset + loaded.length
    } catch {
      if (requestId !== lastRequestId) return

      topAlerts.add('Failed to load the feed.', 'danger', 5)
      if (!append) {
        items.value = []
        totalCount.value = 0
      }
    } finally {
      if (requestId === lastRequestId) {
        isLoading.value = false
      }
    }
  }

  async function loadInitial(limit = 100) {
    pageSize.value = limit
    await load(0, false)
  }

  async function loadMore() {
    if (!hasMore.value || isLoading.value) return
    await load(items.value?.length ?? 0, true)
  }

  function replace(updated: ApiSeFeedItem) {
    const key = feedItemKey(updated)
    items.value = (items.value ?? []).map((item) => (feedItemKey(item) === key ? updated : item))
  }

  function replaceExpense(expense: ApiSeExpense) {
    replace({ type: 'expense', expense, adjustment: null })
  }

  function replaceAdjustment(adjustment: ApiSeAdjustment) {
    replace({ type: 'adjustment', expense: null, adjustment })
  }

  async function remove(
    removed: ApiSeFeedItem,
    request: () => Promise<unknown>,
    label: string
  ): Promise<boolean> {
    if (isDeleting.value) return false

    const key = feedItemKey(removed)

    isDeleting.value = true
    try {
      await request()
      items.value = (items.value ?? []).filter((item) => feedItemKey(item) !== key)
      totalCount.value = Math.max(0, totalCount.value - 1)
      topAlerts.add(`${label} deleted.`, 'success', 3)
      return true
    } catch {
      topAlerts.add(`Failed to delete ${label.toLowerCase()}.`, 'danger', 5)
      return false
    } finally {
      isDeleting.value = false
    }
  }

  function removeExpense(expense: ApiSeExpense): Promise<boolean> {
    return remove(
      { type: 'expense', expense, adjustment: null },
      () => client.deleteSplitExpense(expense.id),
      'Expense'
    )
  }

  function removeAdjustment(adjustment: ApiSeAdjustment): Promise<boolean> {
    return remove(
      { type: 'adjustment', expense: null, adjustment },
      () => client.deleteSplitExpenseAdjustment(adjustment.id),
      'Adjustment'
    )
  }

  watch(
    () => ({ ...filters }),
    () => load(0, false)
  )

  return {
    items,
    totalCount,
    filters,
    isLoading,
    isDeleting,
    hasMore,
    loadInitial,
    loadMore,
    replaceExpense,
    replaceAdjustment,
    removeExpense,
    removeAdjustment
  }
}

export type Feed = ReturnType<typeof useFeed>
