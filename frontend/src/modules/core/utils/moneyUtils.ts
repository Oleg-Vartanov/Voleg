export default {
  fromMinorUnits(amountMinor: number, decimals: number): string {
    return (amountMinor / Math.pow(10, decimals)).toFixed(decimals)
  },

  toMinorUnits(amountMajor: number | string, decimals: number): number {
    return Math.round(Number(amountMajor) * Math.pow(10, decimals))
  },

  sanitizeDecimalPlaces(raw: string, decimalPlaces: number): string {
    if (raw === '') return ''

    let value = raw.replace(',', '.').replace(/[^\d.]/g, '')

    const firstDot = value.indexOf('.')
    if (firstDot !== -1) {
      value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, '')
    }

    if (decimalPlaces <= 0) {
      return value.replace(/\./g, '')
    }

    if (firstDot !== -1) {
      const intPart = value.slice(0, firstDot)
      const decPart = value.slice(firstDot + 1, firstDot + 1 + decimalPlaces)
      return `${intPart}.${decPart}`
    }

    return value
  }
}
