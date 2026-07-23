import { onMounted, onUnmounted, ref, watch } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import { useAuth } from '@/modules/user/stores/useAuth'

const SEARCH_DEBOUNCE_MS = 300

export function useUserSearch(
  excludeUserIds: () => number[],
  excludeSelf = true,
  enabled = true
) {
  const auth = useAuth()

  const users = ref<ApiUser[]>([])
  const searchUsername = ref('')
  const searchError = ref('')
  const isLoading = ref(false)

  let debounceTimer: ReturnType<typeof setTimeout> | null = null

  function filterResults(results: ApiUser[]): ApiUser[] {
    const excluded = new Set(
      [
        ...(excludeSelf && auth.user.id !== null ? [auth.user.id] : []),
        ...excludeUserIds(),
      ].filter((id): id is number => id !== null),
    )
    return results.filter((user) => !excluded.has(user.id))
  }

  watch(
    () => excludeUserIds().join(','),
    () => {
      users.value = filterResults(users.value)
    }
  )

  watch(searchUsername, (value) => {
    if (debounceTimer !== null) {
      clearTimeout(debounceTimer)
      debounceTimer = null
    }

    if (value === '') {
      searchError.value = ''
      loadContacts()
      return
    }

    debounceTimer = setTimeout(() => {
      debounceTimer = null
      searchUser()
    }, SEARCH_DEBOUNCE_MS)
  })

  async function searchUser() {
    if (searchUsername.value === '') return

    isLoading.value = true
    searchError.value = ''
    try {
      const response = await client.listUsers(searchUsername.value)
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
    if (enabled) {
      loadContacts()
    }
  })

  onUnmounted(() => {
    if (debounceTimer !== null) {
      clearTimeout(debounceTimer)
    }
  })

  return {
    users,
    searchUsername,
    searchError,
    isLoading,
    loadContacts
  }
}
