import type { ApiSeConnection } from '@/modules/splitExpense/types'
import type { ApiUser } from '@/modules/core/apiType'

export function getConnectionPartner(connection: ApiSeConnection, currentUserId: number): ApiUser {
  return connection.userA.id === currentUserId ? connection.userB : connection.userA
}

export function getConnectionPartnerName(
  connection: ApiSeConnection,
  currentUserId: number
): string {
  const partner = getConnectionPartner(connection, currentUserId)
  return `@${partner.username}`
}
