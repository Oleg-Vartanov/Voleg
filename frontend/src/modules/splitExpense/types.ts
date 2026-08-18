import type { ApiUser } from '@/modules/core/apiType.ts'
import type { CategoryKey } from '@/modules/splitExpense/categories'

export interface ApiSeCategory {
  id: number
  tag: string
  title: string
}

export interface ApiSeCurrency {
  id: number
  name: string
  code: string
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
  createdByUser: ApiUser
  category: ApiSeCategory
  currency: ApiSeCurrency
  splits: ApiSeExpenseSplit[]
}

export interface ApiSeExpenseSplit {
  id: number
  amount: string
  user: ApiUser
}

export interface ApiSeBalanceAmount {
  currency: ApiSeCurrency
  amount: number
}

export interface ApiSeUserBalance {
  user: ApiUser
  amounts: ApiSeBalanceAmount[]
}

export interface ApiSeBalance {
  totalAmounts: ApiSeBalanceAmount[]
  byUserAmounts: ApiSeUserBalance[]
}

export interface ApiSeConnection {
  id: number
  status: 'accepted' | 'rejected' | 'pending'
  requestedBy: ApiUser
  userA: ApiUser
  userB: ApiUser
}

export interface ApiSeExpensePayload {
  title: string
  amount: number
  currencyId: number
  expenseDate: string
  description: string | null
  paidByUserId: number
  categoryId: number
  splits: { userId: number; amount: number }[]
}

export interface SeCategory {
  icon: CategoryKey
  title: string
  tag: string
}

export interface SeExpenseSplitUser {
  user: ApiUser
  paidAmount: string | null
  splitAmount: string
}