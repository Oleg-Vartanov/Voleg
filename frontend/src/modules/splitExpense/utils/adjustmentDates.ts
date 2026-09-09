import type { ApiSeAdjustment } from '@/modules/splitExpense/types'
import { formatMonthLabel, monthKey } from '@/modules/splitExpense/utils/expenseDates'

export type AdjustmentMonthGroup = {
  month: string
  label: string
  adjustments: ApiSeAdjustment[]
}

export function groupAdjustmentsByMonth(adjustments: ApiSeAdjustment[]): AdjustmentMonthGroup[] {
  const groups = new Map<string, AdjustmentMonthGroup>()

  for (const adjustment of adjustments) {
    const key = monthKey(adjustment.adjustmentDate)
    let group = groups.get(key)
    if (!group) {
      group = { month: key, label: formatMonthLabel(key), adjustments: [] }
      groups.set(key, group)
    }
    group.adjustments.push(adjustment)
  }

  return [...groups.values()]
}
