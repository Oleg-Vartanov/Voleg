import moneyUtils from '@/modules/core/utils/moneyUtils'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'
import { useAuth } from '@/modules/user/stores/useAuth'

export function useAdjustmentDisplay() {
  const auth = useAuth()

  function otherParty(adjustment: ApiSeAdjustment) {
    return adjustment.createdByUser.id === auth.user.id
      ? adjustment.otherUser
      : adjustment.createdByUser
  }

  function title(adjustment: ApiSeAdjustment): string {
    return `Adjustment with @${otherParty(adjustment).username}`
  }

  function viewerAmountMinor(adjustment: ApiSeAdjustment): number {
    const stored = Number(adjustment.amount)
    return adjustment.createdByUser.id === auth.user.id ? stored : -stored
  }

  function amountColor(adjustment: ApiSeAdjustment): string {
    return viewerAmountMinor(adjustment) < 0 ? 'text-orange' : 'text-success'
  }

  function formatSignedMoney(amountMinor: number, adjustment: ApiSeAdjustment): string {
    const amount = moneyUtils.fromMinorUnits(
      Math.abs(amountMinor),
      adjustment.currency.decimalPlaces
    )
    const sign = amountMinor < 0 ? '-' : '+'
    return `${sign}${amount} ${adjustment.currency.code}`
  }

  function currentUserBalance(adjustment: ApiSeAdjustment): string {
    return formatSignedMoney(viewerAmountMinor(adjustment), adjustment)
  }

  function userBalance(adjustment: ApiSeAdjustment, userId: number): string {
    const stored = Number(adjustment.amount)
    const minor = adjustment.createdByUser.id === userId ? stored : -stored
    return formatSignedMoney(minor, adjustment)
  }

  return {
    title,
    otherParty,
    amountColor,
    currentUserBalance,
    userBalance
  }
}
