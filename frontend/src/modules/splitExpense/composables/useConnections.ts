import { computed, ref } from 'vue'
import axios from 'axios'
import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import type { ApiSeConnection } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useConnections() {
  const topAlerts = useTopAlerts()

  const connections = ref<ApiSeConnection[]>([])
  const totalCount = ref(0)
  const isLoading = ref(false)
  const isListLoading = ref(false)

  const acceptedConnections = computed(() =>
    connections.value.filter((connection) => connection.status === 'accepted')
  )

  async function loadConnections(
    offset = 0,
    limit = 100,
    status: 'accepted' | 'pending' | 'rejected' | null = null
  ): Promise<void> {
    isListLoading.value = true
    try {
      const response = await client.listSplitExpenseConnections(offset, limit, status)
      connections.value = response.data
      totalCount.value = Number(response.headers['x-total-count'] ?? response.data.length)
    } catch {
      topAlerts.add('Failed to load connections.', 'danger', 5)
      connections.value = []
      totalCount.value = 0
    } finally {
      isListLoading.value = false
    }
  }

  async function sendRequest(
    user: ApiUser
  ): Promise<{ ok: true } | { ok: false; message: string | null }> {
    isLoading.value = true
    try {
      const response = await client.requestSplitExpenseConnection(user.id)
      connections.value.unshift(response.data)
      topAlerts.add('Connection request sent.', 'success', 3)
      return { ok: true }
    } catch (error) {
      const message = axios.isAxiosError(error) ? error.response?.data?.message : undefined

      if (axios.isAxiosError(error) && error.response?.status === 400) {
        return { ok: false, message: message ?? 'Failed to send connection request.' }
      }

      topAlerts.add(message ?? 'Failed to send connection request.', 'danger', 5)
      return { ok: false, message: null }
    } finally {
      isLoading.value = false
    }
  }

  async function removeConnection(connection: ApiSeConnection): Promise<boolean> {
    isLoading.value = true
    try {
      await client.deleteSplitExpenseConnection(connection.id)
      connections.value = connections.value.filter((item) => item.id !== connection.id)
      topAlerts.add('Connection removed.', 'success', 3)
      return true
    } catch {
      topAlerts.add('Failed to remove connection.', 'danger', 5)
      return false
    } finally {
      isLoading.value = false
    }
  }

  return {
    connections,
    totalCount,
    acceptedConnections,
    isLoading,
    isListLoading,
    loadConnections,
    sendRequest,
    removeConnection
  }
}
