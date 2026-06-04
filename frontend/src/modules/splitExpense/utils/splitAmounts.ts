export function buildEqualSplits(
  totalMinor: number,
  userIds: number[]
): { userId: number; amount: number }[] {
  const count = userIds.length
  const base = Math.floor(totalMinor / count)
  const remainder = totalMinor % count

  return userIds.map((userId, index) => ({
    userId,
    amount: base + (index < remainder ? 1 : 0)
  }))
}
