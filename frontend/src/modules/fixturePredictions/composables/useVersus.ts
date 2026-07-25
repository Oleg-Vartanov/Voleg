import { ref } from 'vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useRoute, useRouter } from 'vue-router'
import type { ApiUser } from '@/modules/core/apiType'
import arrayUtils from '@/modules/core/utils/arrayUtils'

export type Versus = ReturnType<typeof useVersus>

export function useVersus(): Versus {
  const route = useRoute()
  const router = useRouter()
  const auth = useAuth()

  const wasRequested = ref(false)
  const users = ref<ApiUser[]>([])

  function syncRoute() {
    router.replace({
      query: {
        ...route.query,
        ...routeQuery()
      }
    })
  }

  function setUsers(next: ApiUser[]) {
    users.value = arrayUtils.uniqueBy(
      next.filter((user) => user.id !== auth.user.id),
      (user) => user.id
    )
    syncRoute()
  }

  function onLoadFixtures(apiUsers: ApiUser[]): void {
    wasRequested.value = true
    users.value = apiUsers.filter((user) => user.id !== auth.user.id)
  }

  function routeQuery() {
    return {
      userIds: users.value.length > 0 ? users.value.map((u) => u.id).join(',') : undefined
    }
  }

  function getUserIds(): number[] {
    const queryIds = route.query.userIds
      ? route.query.userIds
          .split(',')
          .map((s) => Number(s.trim()))
          .filter((n) => !isNaN(n))
      : []
    const filterIds = users.value.map((u) => u.id)

    return wasRequested.value ? filterIds : queryIds
  }

  return {
    users,
    setUsers,
    onLoadFixtures,
    routeQuery,
    getUserIds
  }
}
