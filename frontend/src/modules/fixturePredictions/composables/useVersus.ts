import { ref } from 'vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import arrayUtils from '@/modules/core/utils/arrayUtils'
import { useRoute, useRouter } from 'vue-router'
import type { ApiUser } from '@/modules/core/apiType'

export type Versus = ReturnType<typeof useVersus>

export function useVersus(): Versus {
  const route = useRoute()
  const router = useRouter()
  const auth = useAuth()

  const wasRequested = ref(false)
  const users = ref<ApiUser[]>([])

  function addUser(user: ApiUser) {
    if (!users.value.includes(user) && auth.user.id !== user.id) {
      users.value.push(user)
    }
  }

  function removeUser(user: ApiUser) {
    arrayUtils.removeItem(users.value, user)

    router.replace({
      query: {
        ...route.query,
        ...routeQuery()
      }
    })
  }

  function onLoadFixtures(apiUsers: ApiUser[]): void {
    wasRequested.value = true
    users.value = apiUsers.filter(function (user) {
      return user.id !== auth.user.id
    })
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
    addUser,
    removeUser,
    onLoadFixtures,
    routeQuery,
    getUserIds
  }
}
