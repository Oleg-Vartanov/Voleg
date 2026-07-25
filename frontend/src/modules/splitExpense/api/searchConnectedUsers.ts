import client from '@/modules/core/apiClient'
import type { ApiUser } from '@/modules/core/apiType'
import type { UserSearchFn } from '@/modules/core/components/form/types'

export const searchConnectedUsers: UserSearchFn = async (query) => {
  const response = await client.listSplitExpenseConnections(
    0,
    5,
    'accepted',
    true,
    query,
  )
  return response.data as ApiUser[]
}
