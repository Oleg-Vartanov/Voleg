export default {
  fromMinorUnits(amountMinor: number, decimals: number): string {
    return (amountMinor / Math.pow(10, decimals)).toFixed(decimals)
  },

  toMinorUnits(amountMajor: number | string, decimals: number): number {
    return Math.round(Number(amountMajor) * Math.pow(10, decimals))
  },

  sanitizeDecimalPlaces(raw: string, decimalPlaces: number, signed = false): string {
    if (raw === '') return ''

    const trimmed = raw.trim()
    const isNegative = signed && trimmed.startsWith('-')
    if (signed && (trimmed === '-' || trimmed === '-.')) {
      return trimmed
    }

    let value = trimmed.replace(',', '.').replace(/[^\d.]/g, '')

    const firstDot = value.indexOf('.')
    if (firstDot !== -1) {
      value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, '')
    }

    if (decimalPlaces <= 0) {
      value = value.replace(/\./g, '')
    } else if (firstDot !== -1) {
      const intPart = value.slice(0, firstDot)
      const decPart = value.slice(firstDot + 1, firstDot + 1 + decimalPlaces)
      value = `${intPart}.${decPart}`
    }

    if (value === '') {
      return isNegative ? '-' : ''
    }

    return isNegative ? `-${value}` : value
  }
}
