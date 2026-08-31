import { ref } from 'vue';
import client from '@/modules/core/apiClient';
import type { ApiSeConnection } from '@/modules/splitExpense/types';
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts';

export type ConnectionRequestTab = 'incoming' | 'outgoing' | 'rejected';

const emptyTabCache = (): Record<ConnectionRequestTab, ApiSeConnection[]> => ({
  incoming: [],
  outgoing: [],
  rejected: [],
});

export function useConnectionRequests() {
  const topAlerts = useTopAlerts();
  const activeTab = ref<ConnectionRequestTab>('incoming');
  const items = ref<ApiSeConnection[]>([]);
  const isListLoading = ref(false);
  const isLoading = ref(false);
  const tabCache = ref(emptyTabCache());
  const loadedTabs = new Set<ConnectionRequestTab>();

  async function loadTab(tab: ConnectionRequestTab, force = false): Promise<void> {
    if (!force && loadedTabs.has(tab)) {
      items.value = tabCache.value[tab];
      return;
    }

    isListLoading.value = true;
    try {
      const response =
        tab === 'rejected'
          ? await client.listSplitExpenseConnections(0, 100, 'rejected')
          : await client.listSplitExpenseConnections(0, 100, null, false, null, tab);

      tabCache.value[tab] = response.data;
      items.value = tabCache.value[tab];
      loadedTabs.add(tab);
    } catch {
      topAlerts.add('Failed to load requests.', 'danger', 5);
      tabCache.value[tab] = [];
      items.value = [];
    } finally {
      isListLoading.value = false;
    }
  }

  async function selectTab(tab: ConnectionRequestTab): Promise<void> {
    activeTab.value = tab;
    await loadTab(tab);
  }

  function removeFromTab(tab: ConnectionRequestTab, connectionId: number): void {
    tabCache.value[tab] = tabCache.value[tab].filter((item) => item.id !== connectionId);
    if (activeTab.value === tab) {
      items.value = tabCache.value[tab];
    }
  }

  function invalidateTab(tab: ConnectionRequestTab): void {
    loadedTabs.delete(tab);
  }

  async function acceptRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true;
    try {
      await client.respondSplitExpenseConnection(connection.id, 'accepted');
      removeFromTab('incoming', connection.id);
      topAlerts.add('Connection accepted.', 'success', 3);
    } catch {
      topAlerts.add('Failed to accept connection.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  async function rejectRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true;
    try {
      await client.respondSplitExpenseConnection(connection.id, 'rejected');
      removeFromTab('incoming', connection.id);
      invalidateTab('rejected');
      topAlerts.add('Connection request rejected.', 'success', 3);
    } catch {
      topAlerts.add('Failed to reject connection.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  async function cancelRequest(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true;
    try {
      await client.deleteSplitExpenseConnection(connection.id);
      removeFromTab('outgoing', connection.id);
      topAlerts.add('Connection request cancelled.', 'success', 3);
    } catch {
      topAlerts.add('Failed to cancel connection request.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  async function removeConnection(connection: ApiSeConnection): Promise<void> {
    isLoading.value = true;
    try {
      await client.deleteSplitExpenseConnection(connection.id);
      removeFromTab('rejected', connection.id);
      topAlerts.add('Connection removed.', 'success', 3);
    } catch {
      topAlerts.add('Failed to remove connection.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  return {
    activeTab,
    items,
    isListLoading,
    isLoading,
    loadTab,
    selectTab,
    acceptRequest,
    rejectRequest,
    cancelRequest,
    removeConnection,
  };
}
