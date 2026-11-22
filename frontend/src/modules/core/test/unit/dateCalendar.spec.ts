import { describe, expect, it } from 'vitest'
import {
  addDays,
  addMonths,
  isValidIso,
  monthGrid,
  monthTitle,
  startOfMonth,
  todayIso,
  weekdayLabels
} from '@/modules/core/utils/dateCalendar'

describe('isValidIso', () => {
  it('accepts only real calendar dates', () => {
    expect(isValidIso('2026-11-21')).toBe(true)
    expect(isValidIso('2028-02-29')).toBe(true)
    expect(isValidIso('2027-02-29')).toBe(false)
    expect(isValidIso('2026-13-01')).toBe(false)
    expect(isValidIso('2026-11-21T10:00')).toBe(false)
    expect(isValidIso('')).toBe(false)
  })
})

describe('todayIso', () => {
  it('uses the local calendar date', () => {
    expect(todayIso(new Date(2026, 0, 5, 23, 59))).toBe('2026-01-05')
  })
})

describe('addDays / addMonths', () => {
  it('crosses month and year boundaries', () => {
    expect(addDays('2026-11-30', 1)).toBe('2026-12-01')
    expect(addDays('2026-01-01', -1)).toBe('2025-12-31')
    expect(addDays('2026-11-21', 7)).toBe('2026-11-28')
  })

  it('is not thrown off by daylight-saving changes', () => {
    // Clocks go back (25-hour day) and forward (23-hour day) on these dates in Europe and the US.
    expect(addDays('2026-10-24', 1)).toBe('2026-10-25')
    expect(addDays('2026-10-25', 1)).toBe('2026-10-26')
    expect(addDays('2026-03-29', 1)).toBe('2026-03-30')
    expect(addDays('2026-11-01', 1)).toBe('2026-11-02')
    expect(addDays('2026-03-08', 1)).toBe('2026-03-09')
    expect(addDays('2026-10-26', -1)).toBe('2026-10-25')
  })

  it('builds month grids of 42 distinct consecutive days around daylight-saving changes', () => {
    for (const month of ['2026-03-10', '2026-10-10', '2026-11-10']) {
      const isos = monthGrid(month, 1).map((cell) => cell.iso)
      expect(new Set(isos).size).toBe(42)
      isos.slice(1).forEach((iso, index) => expect(addDays(isos[index], 1)).toBe(iso))
    }
  })

  it('clamps the day when the target month is shorter', () => {
    expect(addMonths('2026-01-31', 1)).toBe('2026-02-28')
    expect(addMonths('2028-01-31', 1)).toBe('2028-02-29')
    expect(addMonths('2026-03-31', -1)).toBe('2026-02-28')
  })

  it('moves across years', () => {
    expect(addMonths('2026-11-21', 2)).toBe('2027-01-21')
    expect(addMonths('2026-01-15', -2)).toBe('2025-11-15')
    expect(addMonths('2026-11-21', 12)).toBe('2027-11-21')
  })
})

describe('monthGrid', () => {
  it('always has 42 cells and starts on the requested weekday', () => {
    const sundayFirst = monthGrid('2026-11-21', 0) // 1 Nov 2026 is a Sunday
    expect(sundayFirst).toHaveLength(42)
    expect(sundayFirst[0]).toEqual({ iso: '2026-11-01', day: 1, inMonth: true })

    const mondayFirst = monthGrid('2026-11-21', 1)
    expect(mondayFirst[0]).toEqual({ iso: '2026-10-26', day: 26, inMonth: false })
    expect(mondayFirst[6]).toEqual({ iso: '2026-11-01', day: 1, inMonth: true })
  })

  it('marks days outside the month', () => {
    const cells = monthGrid('2026-02-10', 1)
    expect(cells.filter((cell) => cell.inMonth)).toHaveLength(28)
    expect(cells[cells.length - 1].inMonth).toBe(false)
  })
})

describe('labels', () => {
  it('rotates weekday names to the first day', () => {
    expect(weekdayLabels(1, 'en-US')).toEqual(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])
    expect(weekdayLabels(0, 'en-US')[0]).toBe('Sun')
  })

  it('builds the month title and month start', () => {
    expect(monthTitle('2026-11-21', 'en-US')).toBe('November 2026')
    expect(startOfMonth('2026-11-21')).toBe('2026-11-01')
  })
})
