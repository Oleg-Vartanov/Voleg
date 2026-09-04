import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import { usePagePagination } from '@/modules/core/components/pagination/usePagePagination'
import type { ApiSeConnection } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export type ConnectionRequestTab = 'incoming' | 'outgoing' | 'rejected'

const requestsPageSize = 5

function createTabState() {
  return {
    items: ref<ApiSeConnection[]>([]),
    pagination: usePagePagination(requestsPageSize),
    loaded: false
  }
}

export function useConnectionRequests() {
  const topAlerts = useTopAlerts()
  const activeTab = ref<ConnectionRequestTab>('incoming')
  const isListLoading = ref(false)
  const isLoading = ref(false)

  const tabs: Record<ConnectionRequestTab, ReturnType<typeof createTabState>> = {
    incoming: createTabState(),
    outgoing: createTabState(),
    rejected: createTabState()
  }

  const activeState = computed(() => tabs[activeTab.value])
  const items = computed(() => activeState.value.items.value)
  const pageIndex = computed(() => activeState.value.pagination.pageIndex.value)
  const pageSize = computed(() => activeState.value.pagination.pageSize.value)
  const totalPages = computed(() => activeState.value.pagination.totalPages.value)

  async function loadTab(tab: ConnectionRequestTab, force = false): Promise<void> {
    const state = tabs[tab]
    if (!force && state.loaded) return

    const { offset, limit, setTotalItems, setPageIndex } = state.pagination

    isListLoading.value = true
    try {
      const response =
        tab === 'rejected'
          ? await client.listSplitExpenseConnections(offset.value, limit.value, 'rejected')
          : await client.listSplitExpenseConnections(
              offset.value,
              limit.value,
              null,
              false,
              null,
              tab
            )

      state.items.value = response.data
      setTotalItems(Number(response.headers['x-total-count'] ?? response.data.length))
      state.loaded = true

      if (state.pagination.pageIndex.value > state.pagination.totalPages.value) {
        setPageIndex(state.pagination.totalPages.value)
        await loadTab(tab, true)
      }
    } catch {
      topAlerts.add('Failed to load requests.', 'danger', 5)
      state.items.value = []
      setTotalItems(0)
    } finally {
      isListLoading.value = false
    }
  }

  async function selectTab(tab: ConnectionRequestTab): Promise<void> {
    activeTab.value = tab
    await loadTab(tab)
  }

  async function setPage(page: number): Promise<void> {
    const state = activeState.value
    if (state.loaded && state.pagination.pageIndex.value === page) return

    state.pagination.setPageIndex(page)
    await loadTab(activeTab.value, true)
  }

  async function setPageSize(size: number): Promise<void> {
    for (const tab of Object.keys(tabs) as ConnectionRequestTab[]) {
      tabs[tab].pagination.setPageSize(size)
      tabs[tab].loaded = false
    }
    await loadTab(activeTab.value, true)
  }

  async function refreshTabs(...changed: ConnectionRequestTab[]): Promise<void> {
    changed.forEach((tab) => {
      tabs[tab].loaded = false
    })
    await loadTab(activeTab.value, true)
  }

  function invalidateTab(tab: ConnectionRequestTab): void {
    tabs[tab].loaded = false
  }

  async function acceptRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true
    try {
      await client.respondSplitExpenseConnection(connection.id, 'accepted')
      topAlerts.add('Connection accepted.', 'success', 3)
      await refreshTabs('incoming')
    } catch {
      topAlerts.add('Failed to accept connection.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  async function rejectRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true
    try {
      await client.respondSplitExpenseConnection(connection.id, 'rejected')
      topAlerts.add('Connection request rejected.', 'success', 3)
      await refreshTabs('incoming', 'rejected')
    } catch {
      topAlerts.add('Failed to reject connection.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  async function cancelRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true
    try {
      await client.deleteSplitExpenseConnection(connection.id)
      topAlerts.add('Connection request cancelled.', 'success', 3)
      await refreshTabs('outgoing')
    } catch {
      topAlerts.add('Failed to cancel connection request.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  async function removeConnection(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true
    try {
      await client.deleteSplitExpenseConnection(connection.id)
      topAlerts.add('Connection removed.', 'success', 3)
      await refreshTabs('rejected')
    } catch {
      topAlerts.add('Failed to remove connection.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  return {
    activeTab,
    items,
    isListLoading,
    isLoading,
    pageIndex,
    pageSize,
    totalPages,
    loadTab,
    selectTab,
    setPage,
    setPageSize,
    invalidateTab,
    acceptRequest,
    rejectRequest,
    cancelRequest,
    removeConnection
  }
}
