export type MonthGroup<T> = { month: string; label: string; items: T[] }

export function parseExpenseDate(value: string): Date | null {
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? null : date
}

export function monthKey(value: string): string {
  const date = parseExpenseDate(value)
  if (!date) return value
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}

export function formatMonthLabel(key: string): string {
  const [year, month] = key.split('-').map(Number)
  if (!year || !month) return key
  return new Date(year, month - 1, 1).toLocaleDateString(undefined, {
    month: 'long',
    year: 'numeric'
  })
}

export function formatShortDate(value: string): { day: string; month: string } {
  const date = parseExpenseDate(value)
  if (!date) return { day: value, month: '' }
  return {
    day: String(date.getDate()).padStart(2, '0'),
    month: date.toLocaleDateString(undefined, { month: 'short' })
  }
}

export function formatFullDate(value: string): string {
  const date = parseExpenseDate(value)
  if (!date) return value
  return date.toLocaleDateString(undefined, {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

export function groupByMonth<T>(items: T[], dateOf: (item: T) => string): MonthGroup<T>[] {
  const groups = new Map<string, MonthGroup<T>>()

  for (const item of items) {
    const key = monthKey(dateOf(item))
    let group = groups.get(key)
    if (!group) {
      group = { month: key, label: formatMonthLabel(key), items: [] }
      groups.set(key, group)
    }
    group.items.push(item)
  }

  return [...groups.values()]
}
