import { describe, expect, it } from 'vitest'
import {
  detectDateFormat,
  formatDisplay,
  maskInput,
  parseDisplay,
  placeholderFor
} from '@/modules/core/utils/dateInput'

const us = detectDateFormat('en-US')
const gb = detectDateFormat('en-GB')
const de = detectDateFormat('de-DE')
const se = detectDateFormat('sv-SE')

describe('detectDateFormat', () => {
  it('follows the locale order and separator', () => {
    expect(us).toEqual({ order: ['month', 'day', 'year'], separator: '/' })
    expect(gb).toEqual({ order: ['day', 'month', 'year'], separator: '/' })
    expect(de).toEqual({ order: ['day', 'month', 'year'], separator: '.' })
    expect(se).toEqual({ order: ['year', 'month', 'day'], separator: '-' })
  })

  it('builds a placeholder', () => {
    expect(placeholderFor(gb)).toBe('dd/mm/yyyy')
    expect(placeholderFor(us)).toBe('mm/dd/yyyy')
  })
})

describe('maskInput', () => {
  it('adds separators only before the next digit', () => {
    expect(maskInput('2', gb)).toBe('2')
    expect(maskInput('22', gb)).toBe('22')
    expect(maskInput('221', gb)).toBe('22/1')
    expect(maskInput('22/11/2026', gb)).toBe('22/11/2026')
    expect(maskInput('2026-11-2', se)).toBe('2026-11-2')
  })

  it('drops non-digits and extra digits', () => {
    expect(maskInput('ab22x11', gb)).toBe('22/11')
    expect(maskInput('221120269999', gb)).toBe('22/11/2026')
  })
})

describe('formatDisplay', () => {
  it('formats an ISO date, ignoring any time part', () => {
    expect(formatDisplay('2026-11-22', gb)).toBe('22/11/2026')
    expect(formatDisplay('2026-11-22T10:00:00+00:00', us)).toBe('11/22/2026')
    expect(formatDisplay('', gb)).toBe('')
  })
})

describe('parseDisplay', () => {
  it('parses complete real dates to ISO', () => {
    expect(parseDisplay('22/11/2026', gb)).toBe('2026-11-22')
    expect(parseDisplay('11/22/2026', us)).toBe('2026-11-22')
    expect(parseDisplay('29/02/2028', gb)).toBe('2028-02-29')
  })

  it('rejects incomplete and impossible dates', () => {
    expect(parseDisplay('22/11', gb)).toBeNull()
    expect(parseDisplay('31/02/2026', gb)).toBeNull()
    expect(parseDisplay('29/02/2027', gb)).toBeNull()
    expect(parseDisplay('00/11/2026', gb)).toBeNull()
    expect(parseDisplay('', gb)).toBeNull()
  })
})
