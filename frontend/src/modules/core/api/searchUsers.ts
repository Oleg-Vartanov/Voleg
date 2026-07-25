import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import type { UserSearchFn } from '@/modules/core/components/form/types'

export const searchUsers: UserSearchFn = async (query) => {
  const response = await client.listUsers(query, 0, 5)
  return response.data as ApiUser[]
}
