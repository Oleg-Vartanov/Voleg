import type { ApiSeFeedItem } from '@/modules/splitExpense/types'

export function feedItemKey(item: ApiSeFeedItem): string {
  return item.type === 'expense' ? `expense-${item.expense.id}` : `adjustment-${item.adjustment.id}`
}

export function feedItemDate(item: ApiSeFeedItem): string {
  return item.type === 'expense' ? item.expense.expenseDate : item.adjustment.adjustmentDate
}
