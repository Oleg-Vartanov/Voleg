export type SplitExpenseTabTag = 'balance' | 'expenses' | 'contacts' | 'charts'

export type SplitExpenseTab = {
  tag: SplitExpenseTabTag
  title: string
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
  expenseDate: string
  description: string | null
  paidByUser: { id: number; displayName: string; tag: string }
  category: ApiSeCategory
  currency: ApiSeCurrency
}
