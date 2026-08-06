import moneyUtils from '@/modules/core/utils/moneyUtils'
import { categories, type CategoryKey } from '@/modules/splitExpense/categories'
import type { ApiSeExpense, SeCategory, SeExpenseSplitUser } from '@/modules/splitExpense/types';
import { useAuth } from '@/modules/user/stores/useAuth'

export function useExpenseDisplay() {
  const auth = useAuth()

  function mapCategory(expense: ApiSeExpense): SeCategory {
    const tag = expense.category?.tag ?? categories.other
    const icon = tag in categories ? categories[tag as CategoryKey] : categories.other
    return { icon, title: expense.category?.title ?? 'Other', tag }
  }

  function mapSplitUsers(expense: ApiSeExpense): SeExpenseSplitUser[] {
    const result: SeExpenseSplitUser[] = []

    for (const split of expense.splits) {
      if (split.user.id === expense.paidByUser.id) {
        result.unshift({
          user: split.user,
          paidAmount: formatMoney(expense.amount, expense),
          splitAmount: formatMoney(split.amount, expense)
        })
      } else {
        result.push({
          user: split.user,
          paidAmount: null,
          splitAmount: formatMoney(split.amount, expense)
        })
      }
    }

    return result
  }

  function amountColor(expense: ApiSeExpense): string {
    const isOwed = expense.paidByUser.id !== auth.user.id
    return isOwed ? 'text-orange' : 'text-success'
  }

  function formatMoney(amountMinor: string | number, expense: ApiSeExpense): string {
    const amount = moneyUtils.fromMinorUnits(Number(amountMinor), expense.currency.decimalPlaces)
    return `${amount} ${expense.currency.code}`
  }

  function formatSignedMoney(
    sign: '+' | '-',
    amountMinor: string | number,
    expense: ApiSeExpense
  ): string {
    const amount = moneyUtils.fromMinorUnits(Number(amountMinor), expense.currency.decimalPlaces)
    return `${sign}${amount} ${expense.currency.code}`
  }

  function currentUserSplitBalance(expense: ApiSeExpense): string {
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

  return {
    mapCategory,
    amountColor,
    currentUserSplitBalance,
    mapSplitUsers
  }
}
