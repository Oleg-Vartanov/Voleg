import { onMounted, ref, watch } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import { useAuth } from '@/modules/user/stores/useAuth'

export function useUserSearch(excludeUserIds: () => number[]) {
  const auth = useAuth()

  const users = ref<ApiUser[]>([])
  const searchTag = ref('')
  const searchError = ref('')
  const isLoading = ref(false)

  function filterResults(results: ApiUser[]): ApiUser[] {
    const excluded = new Set(
      [auth.user.id, ...excludeUserIds()].filter((id): id is number => id !== null)
    )
    return results.filter((user) => !excluded.has(user.id))
  }

  watch(
    () => excludeUserIds().join(','),
    () => {
      users.value = filterResults(users.value)
    }
  )

  watch(searchTag, (value) => {
    if (value !== '') return

    searchError.value = ''
    loadContacts()
  })

  async function searchUser() {
    if (searchTag.value === '') return

    isLoading.value = true
    searchError.value = ''
    try {
      const response = await client.listUsers(searchTag.value)
      users.value = filterResults(response.data)
    } catch {
      searchError.value = 'Failed to search users.'
      users.value = []
    } finally {
      isLoading.value = false
    }
  }

  async function loadContacts() {
    if (auth.user.id === null) return

    isLoading.value = true
    searchError.value = ''
    try {
      const response = await client.listContacts(auth.user.id)
      users.value = filterResults(response.data)
    } catch {
      users.value = []
    } finally {
      isLoading.value = false
    }
  }

  onMounted(() => {
    loadContacts()
  })

  return {
    users,
    searchTag,
    searchError,
    isLoading,
    searchUser,
    loadContacts
  }
}
