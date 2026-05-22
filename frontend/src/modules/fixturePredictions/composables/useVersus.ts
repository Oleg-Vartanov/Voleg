import { type Ref, ref } from 'vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import Client from '@/modules/core/apiClient'
import arrayUtils from '@/modules/core/utils/arrayUtils'
import { useRoute, useRouter } from 'vue-router'
import type { ApiUser } from '@/modules/core/apiType'

export interface Versus {
  searchUsers: Ref<ApiUser[]>
  users: Ref<ApiUser[]>
  input: Ref<{ value: string; error: string }>
  showContacts: () => Promise<void>
  searchUser: () => Promise<void>
  isLoading: Ref<boolean>
  addUser: (user: ApiUser) => void
  removeUser: (user: ApiUser) => void
  onLoadFixtures: (apiUsers: ApiUser[]) => void
  routeQuery: () => { userIds?: string }
  getUserIds: () => number[]
}

export function useVersus(): Versus {
  const route = useRoute()
  const router = useRouter()
  const auth = useAuth()

  const wasRequested = ref(false)
  const isLoading = ref(false)
  const searchUsers = ref<ApiUser>([])
  const users = ref<ApiUser>([])
  const input = ref({ value: '', error: '' })

  async function showContacts() {
    if (auth.user.id === null) return

    isLoading.value = true
    Client.listContacts(auth.user.id)
      .then((response) => {
        input.value.value = ''
        setSearchUsers(response.data)
      })
      .catch(() => {
        searchUsers.value = []
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  async function searchUser() {
    if (input.value.value === '') return
    isLoading.value = true
    Client.listUsers(input.value.value)
      .then((response) => {
        setSearchUsers(response.data)
      })
      .catch(() => {
        input.value.error = 'Unexpected error :('
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  function setSearchUsers(values: ApiUser[]) {
    input.value.error = ''
    const selectedIds = new Set(users.value.map((u) => u.id))
    searchUsers.value = values.filter(
      (user) => user.id !== auth.user.id && !selectedIds.has(user.id)
    )
  }

  function addUser(user: ApiUser) {
    if (!users.value.includes(user) && auth.user.id !== user.id) {
      users.value.push(user)
      arrayUtils.removeItem(searchUsers.value, user)
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
    isLoading,
    searchUsers,
    users,
    input,
    searchUser,
    showContacts,
    addUser,
    removeUser,
    onLoadFixtures,
    routeQuery,
    getUserIds
  }
}
