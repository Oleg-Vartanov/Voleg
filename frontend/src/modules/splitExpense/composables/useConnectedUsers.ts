import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { useAuth } from '@/modules/user/stores/useAuth.ts';

export function useConnectedUsers() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()

  const users = ref<ApiUser[]>([])
  let loadPromise: Promise<void> | null = null

  const payerOptions = computed<ApiUser[]>(() => {
    return [auth.user, ...users.value]
  })

  async function load(): Promise<void> {
    if (loadPromise) return loadPromise

    loadPromise = client
      .listSplitExpenseConnections(0, 100, 'accepted', true)
      .then((response) => {
        users.value = response.data
      })
      .catch(() => {
        users.value = []
        topAlerts.add('Failed to load connections.', 'danger', 5)
      })

    return loadPromise
  }

  return {
    users,
    payerOptions,
    load
  }
}
