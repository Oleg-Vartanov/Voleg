import type { ApiUser } from '@/modules/core/apiType.ts'

export type SplitExpenseTabTag = 'expenses' | 'connections' | 'charts'

export type SplitExpenseTab = {
  tag: SplitExpenseTabTag
  title: string
  route: string
  icon: string
  disabled?: boolean
}

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
  status: 'accepted' | 'rejected' | 'pending' | 'blocked'
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
