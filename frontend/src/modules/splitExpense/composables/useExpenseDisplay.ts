import moneyUtils from '@/modules/core/utils/moneyUtils'
import { categories, type CategoryKey } from '@/modules/splitExpense/categories'
import type { ApiSeExpense, SeCategory } from '@/modules/splitExpense/types';
import { useAuth } from '@/modules/user/stores/useAuth'

export function useExpenseDisplay() {
  const auth = useAuth()

  function mapCategory(expense: ApiSeExpense): SeCategory {
    const tag = expense.category?.tag ?? categories.other
    const icon = tag in categories ? categories[tag as CategoryKey] : categories.other
    return { icon, title: expense.category?.title ?? 'Other', tag }
  }

  function amountColor(expense: ApiSeExpense): string {
    const isOwed = expense.paidByUser.id !== auth.user.id
    return isOwed ? 'text-orange' : 'text-success'
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

  function participantsFor(expense: ApiSeExpense) {
    const payerId = expense.paidByUser.id
    const seen = new Set<number>()
    const result: {
      key: string
      name: string
      paidAmount: string | null
      splitLabel: 'share' | 'owes'
      splitAmount: string | null
    }[] = []

    for (const split of expense.splits) {
      if (seen.has(split.user.id)) continue
      seen.add(split.user.id)

      result.push({
        key: String(split.user.id),
        name: split.user.displayName,
        paidAmount: split.user.id === payerId ? formatMoney(expense.amount, expense) : null,
        splitLabel: split.user.id === payerId ? 'share' : 'owes',
        splitAmount: formatMoney(split.amount, expense)
      })
    }

    if (!seen.has(payerId)) {
      result.push({
        key: String(payerId),
        name: expense.paidByUser.displayName,
        paidAmount: formatMoney(expense.amount, expense),
        splitLabel: 'share',
        splitAmount: null
      })
    }

    return result.sort((a, b) => {
      const aIsPayer = a.key === String(payerId)
      const bIsPayer = b.key === String(payerId)
      if (aIsPayer === bIsPayer) return 0
      return aIsPayer ? -1 : 1
    })
  }

  return {
    mapCategory,
    amountColor,
    currentUserSplitBalance,
    participantsFor
  }
}
