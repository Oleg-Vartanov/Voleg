import moneyUtils from '@/modules/core/utils/moneyUtils'
import { categories } from '@/modules/splitExpense/categories'
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { useAuth } from '@/modules/user/stores/useAuth'

export function useExpenseDisplay() {
  const auth = useAuth()

  function categoryFor(expense: ApiSeExpense) {
    const tag = expense.category?.tag ?? categories.other
    const icon = tag in categories ? categories[tag as keyof typeof categories] : categories.other
    return { icon, title: expense.category?.title ?? 'Other', tag }
  }

  function balanceColor(isOwed: boolean): string {
    return isOwed ? 'text-orange' : 'text-success'
  }

  function amountColor(expense: ApiSeExpense): string {
    return balanceColor(auth.user.id !== expense.paidByUser.id)
  }

  function splitColor(userId: number): string {
    return balanceColor(userId === auth.user.id)
  }

  function formatMoney(amountMinor: string | number, expense: ApiSeExpense): string {
    const amount = moneyUtils.fromMinorUnits(Number(amountMinor), expense.currency.decimalPlaces)
    return `${amount}${expense.currency.symbol}`
  }

  function formatSignedMoney(
    sign: '+' | '-',
    amountMinor: string | number,
    expense: ApiSeExpense
  ): string {
    const amount = moneyUtils.fromMinorUnits(Number(amountMinor), expense.currency.decimalPlaces)
    return `${sign}${amount}${expense.currency.symbol}`
  }

  function formatAmount(expense: ApiSeExpense): string {
    if (auth.user.id === expense.paidByUser.id) {
      let totalMinor = 0
      for (const split of expense.splits) {
        if (split.user.id !== auth.user.id) {
          totalMinor += Number(split.amount)
        }
      }
      return formatSignedMoney('+', totalMinor, expense)
    }

    const split = expense.splits.find((s) => s.user.id === auth.user.id)
    return formatSignedMoney('-', split?.amount ?? 0, expense)
  }

  function formatPaid(expense: ApiSeExpense): string {
    return formatMoney(expense.amount, expense)
  }

  function formatSplitMoney(
    userId: number,
    amountMinor: string | number,
    expense: ApiSeExpense
  ): string {
    const sign = userId === auth.user.id ? '-' : '+'
    return formatSignedMoney(sign, amountMinor, expense)
  }

  return {
    categoryFor,
    amountColor,
    splitColor,
    formatAmount,
    formatPaid,
    formatSplitMoney
  }
}
