import type { ApiSeExpense } from '@/modules/splitExpense/types'

export type ExpenseMonthGroup = { month: string; label: string; items: ApiSeExpense[] }

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

export function formatDateParts(value: string): { day: string; month: string } {
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
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

export function groupExpensesByMonth(expenses: ApiSeExpense[]): ExpenseMonthGroup[] {
  const groups = new Map<string, ExpenseMonthGroup>()

  for (const expense of expenses) {
    const key = monthKey(expense.expenseDate)
    let group = groups.get(key)
    if (!group) {
      group = { month: key, label: formatMonthLabel(key), items: [] }
      groups.set(key, group)
    }
    group.items.push(expense)
  }

  return [...groups.values()]
}
