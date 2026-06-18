import { ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export function useContacts() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()

  const users = ref<ApiUser[]>([])
  const isLoading = ref(false)
  const isListLoading = ref(false)

  async function loadContacts() {
    if (auth.user.id === null) return

    isListLoading.value = true
    try {
      const response = await client.listContacts(auth.user.id)
      users.value = response.data
    } catch {
      topAlerts.add('Failed to load contacts.', 'danger', 5)
    } finally {
      isListLoading.value = false
    }
  }

  async function addContact(user: ApiUser) {
    if (auth.user.id === null) return
    if (auth.user.id === user.id) {
      topAlerts.add("It's you :)", 'info')
      return
    }

    isLoading.value = true
    try {
      await client.addContact(auth.user.id, user.id)
      users.value.push(user)
      topAlerts.add('Contact added.', 'success', 3)
    } catch {
      topAlerts.add('Failed to add contact.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  async function removeContact(user: ApiUser) {
    if (auth.user.id === null) return

    isLoading.value = true
    try {
      await client.deleteContact(auth.user.id, user.id)
      users.value = users.value.filter((contact) => contact.id !== user.id)
      topAlerts.add('Contact removed.', 'success', 3)
    } catch {
      topAlerts.add('Failed to remove contact.', 'danger', 5)
    } finally {
      isLoading.value = false
    }
  }

  return {
    users,
    isLoading,
    isListLoading,
    loadContacts,
    addContact,
    removeContact
  }
}
