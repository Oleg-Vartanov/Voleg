import { computed, ref } from 'vue';
import axios from 'axios';
import client from '@/modules/core/apiClient';
import type { ApiUser } from '@/modules/core/apiType';
import type { ApiSeConnection } from '@/modules/splitExpense/types';
import {
  isIncomingRequest,
  isOutgoingRequest,
} from '@/modules/splitExpense/utils/connections';
import { useAuth } from '@/modules/user/stores/useAuth';
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts';

export function useConnections() {
  const auth = useAuth();
  const topAlerts = useTopAlerts();

  const connections = ref<ApiSeConnection[]>([]);
  const totalCount = ref(0);
  const isLoading = ref(false);
  const isListLoading = ref(false);

  const acceptedConnections = computed(() =>
    connections.value.filter((connection) => connection.status === 'accepted'),
  );

  const incomingRequests = computed(() => {
    if (auth.user.id === null) return [];

    return connections.value.filter((connection) =>
      isIncomingRequest(connection, auth.user.id!),
    );
  });

  const outgoingRequests = computed(() => {
    if (auth.user.id === null) return [];

    return connections.value.filter((connection) =>
      isOutgoingRequest(connection, auth.user.id!),
    );
  });

  const rejectedConnections = computed(() => {
    if (auth.user.id === null) return [];

    return connections.value.filter(
      (connection) =>
        connection.status === 'rejected' &&
        (connection.userA.id === auth.user.id || connection.userB.id === auth.user.id),
    );
  });

  async function loadConnections(
    offset = 0,
    limit = 100,
    status: 'accepted' | 'pending' | 'rejected' | null = null,
  ): Promise<void> {
    isListLoading.value = true;
    try {
      const response = await client.listSplitExpenseConnections(offset, limit, status);
      connections.value = response.data;
      totalCount.value = Number(response.headers['x-total-count'] ?? response.data.length);
    } catch {
      topAlerts.add('Failed to load connections.', 'danger', 5);
      connections.value = [];
      totalCount.value = 0;
    } finally {
      isListLoading.value = false;
    }
  }

  async function sendRequest(
    user: ApiUser,
  ): Promise<{ ok: true } | { ok: false; message: string | null }> {
    isLoading.value = true;
    try {
      const response = await client.requestSplitExpenseConnection(user.id);
      connections.value.unshift(response.data);
      topAlerts.add('Connection request sent.', 'success', 3);
      return { ok: true };
    } catch (error) {
      const message = axios.isAxiosError(error)
        ? error.response?.data?.message
        : undefined;

      if (axios.isAxiosError(error) && error.response?.status === 400) {
        return { ok: false, message: message ?? 'Failed to send connection request.' };
      }

      topAlerts.add(message ?? 'Failed to send connection request.', 'danger', 5);
      return { ok: false, message: null };
    } finally {
      isLoading.value = false;
    }
  }

  async function acceptRequest(connection: ApiSeConnection) {
    isLoading.value = true;
    try {
      const response = await client.respondSplitExpenseConnection(connection.id, 'accepted');
      connections.value = connections.value.map((item) =>
        item.id === connection.id ? response.data : item,
      );
      topAlerts.add('Connection accepted.', 'success', 3);
    } catch {
      topAlerts.add('Failed to accept connection.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  async function rejectRequest(connection: ApiSeConnection) {
    isLoading.value = true;
    try {
      const response = await client.respondSplitExpenseConnection(connection.id, 'rejected');
      connections.value = connections.value.map((item) =>
        item.id === connection.id ? response.data : item,
      );
      topAlerts.add('Connection request rejected.', 'success', 3);
    } catch {
      topAlerts.add('Failed to reject connection.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  async function removeConnection(connection: ApiSeConnection): Promise<boolean> {
    isLoading.value = true;
    try {
      await client.deleteSplitExpenseConnection(connection.id);
      connections.value = connections.value.filter((item) => item.id !== connection.id);
      topAlerts.add('Connection removed.', 'success', 3);
      return true;
    } catch {
      topAlerts.add('Failed to remove connection.', 'danger', 5);
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  async function cancelRequest(connection: ApiSeConnection) {
    isLoading.value = true;
    try {
      await client.deleteSplitExpenseConnection(connection.id);
      connections.value = connections.value.filter((item) => item.id !== connection.id);
      topAlerts.add('Connection request cancelled.', 'success', 3);
    } catch {
      topAlerts.add('Failed to cancel connection request.', 'danger', 5);
    } finally {
      isLoading.value = false;
    }
  }

  return {
    connections,
    totalCount,
    acceptedConnections,
    incomingRequests,
    outgoingRequests,
    rejectedConnections,
    isLoading,
    isListLoading,
    loadConnections,
    sendRequest,
    acceptRequest,
    rejectRequest,
    removeConnection,
    cancelRequest,
  };
}
