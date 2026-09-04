import moneyUtils from '@/modules/core/utils/moneyUtils'
import type { ApiSeBalanceAmount } from '@/modules/splitExpense/types'

/**
 * A per-user balance is never zero, but a total can be, when what one user owes the
 * current user cancels out what the current user owes another.
 */
export function formatBalance(balance: ApiSeBalanceAmount): string {
  const amount = moneyUtils.fromMinorUnits(Math.abs(balance.amount), balance.currency.decimalPlaces)
  if (balance.amount === 0) {
    return `${amount} ${balance.currency.code}`
  }

  const sign = balance.amount < 0 ? '-' : '+'

  return `${sign}${amount} ${balance.currency.code}`
}

export function balanceColor(balance: ApiSeBalanceAmount): string {
  if (balance.amount === 0) return 'text-muted'

  return balance.amount < 0 ? 'text-orange' : 'text-success'
}

export function balanceLabel(balance: ApiSeBalanceAmount): string {
  if (balance.amount === 0) return 'settled'

  return balance.amount < 0 ? 'you owe' : 'owes you'
}

export function totalLabel(balance: ApiSeBalanceAmount): string {
  if (balance.amount === 0) return 'settled'

  return balance.amount < 0 ? 'you owe' : 'you are owed'
}
