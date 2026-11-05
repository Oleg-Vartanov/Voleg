import type { ApiSeCurrency } from '@/modules/splitExpense/types'

export interface InsightAmountInput {
  currency: ApiSeCurrency
  amount: number
}

export interface InsightMonthInput {
  month: string
  label: string
  totals: InsightAmountInput[]
}

export interface InsightChartMonth {
  key: string
  label: string
  monthLabel: string
  yearLabel: string
}

export interface InsightChartModel {
  currencyId: number
  currencyCode: string
  decimalPlaces: number
  /** "Total", or the selected category title. */
  label: string
  months: InsightChartMonth[]
  amounts: number[]
}

function amountFor(totals: InsightAmountInput[], currencyId: number): number {
  return totals.find((total) => total.currency.id === currencyId)?.amount ?? 0
}

function axisMonth(key: string): Pick<InsightChartMonth, 'monthLabel' | 'yearLabel'> {
  const [year, month] = key.split('-').map(Number)
  if (!year || !month) return { monthLabel: key, yearLabel: '' }

  const date = new Date(year, month - 1, 1)
  return {
    monthLabel: date.toLocaleDateString(undefined, { month: 'short' }),
    yearLabel: String(year)
  }
}

/**
 * One line per currency, oldest month first.
 * `months` is already the total, or a single category when the insights request is filtered.
 */
export function buildInsightCharts(
  months: InsightMonthInput[],
  label: string
): InsightChartModel[] {
  const chronological = [...months].reverse()
  const currencyById = new Map<number, ApiSeCurrency>()

  for (const month of chronological) {
    for (const total of month.totals) currencyById.set(total.currency.id, total.currency)
  }

  return [...currencyById.values()]
    .sort((a, b) => a.code.localeCompare(b.code))
    .map((currency) => ({
      currencyId: currency.id,
      currencyCode: currency.code,
      decimalPlaces: currency.decimalPlaces,
      label,
      months: chronological.map((month) => ({
        key: month.month,
        label: month.label,
        ...axisMonth(month.month)
      })),
      amounts: chronological.map((month) => amountFor(month.totals, currency.id))
    }))
}
