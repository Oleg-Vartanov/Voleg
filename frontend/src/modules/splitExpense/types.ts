import type { ApiUser } from '@/modules/core/apiType.ts'
import type { CategoryKey } from '@/modules/splitExpense/categories'

export interface ApiSeCategory {
  id: number
  tag: string
  title: string
}

export interface ApiSeCurrency {
  id: number
  code: string
  symbol: string
  decimalPlaces: number
}

export interface ApiSeExpense {
  id: number
  title: string
  amount: string
  amountDisplay: string
  expenseDate: string
  description: string | null
  paidByUser: ApiUser
  category: ApiSeCategory
  currency: ApiSeCurrency
  splits: ApiSeExpenseSplit[]
}

export interface ApiSeExpenseSplit {
  id: number
  amount: string
  user: ApiUser
}

export interface ApiSeConnection {
  id: number
  status: 'accepted' | 'rejected' | 'pending'
  requestedBy: ApiUser
  userA: ApiUser
  userB: ApiUser
}

export interface SeExpenseCreatePayload {
  title: string
  amount: number
  currencyId: number
  expenseDate: string
  description?: string | null
  paidByUserId?: number
  categoryId?: number
  splits: { userId: number; amount: number }[]
}

export interface SeCategory {
  icon: CategoryKey
  title: string
  tag: string
}
