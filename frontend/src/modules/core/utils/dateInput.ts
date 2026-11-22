// Text side of FormInputDate: the visible text follows the browser locale (e.g. 22/11/2026 or
// 11/22/2026), the model always holds an ISO date (YYYY-MM-DD).

type DatePart = 'day' | 'month' | 'year'

export interface DateFormat {
  order: DatePart[]
  separator: string
}

const PART_LENGTH: Record<DatePart, number> = { day: 2, month: 2, year: 4 }
const TOTAL_DIGITS = 8

const formatCache = new Map<string, DateFormat>()

// Cached per locale: building Intl formatters is the costly part, and every field asks for it.
export function detectDateFormat(locale?: string): DateFormat {
  const key = locale ?? ''
  const cached = formatCache.get(key)
  if (cached) return cached

  const parts = new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).formatToParts(new Date(2000, 10, 22))

  const order = parts
    .map((part) => part.type)
    .filter((type): type is DatePart => type === 'day' || type === 'month' || type === 'year')
  const separator = parts.find((part) => part.type === 'literal')?.value.trim() || '/'

  const format = { order, separator }
  formatCache.set(key, format)
  return format
}

export function placeholderFor(format: DateFormat): string {
  const names: Record<DatePart, string> = { day: 'dd', month: 'mm', year: 'yyyy' }
  return format.order.map((part) => names[part]).join(format.separator)
}

// Inserts separators while typing: "2211" -> "22/11", "22" stays "22" so backspace works.
export function maskInput(raw: string, format: DateFormat): string {
  const digits = raw.replace(/\D/g, '').slice(0, TOTAL_DIGITS)
  let result = ''
  let index = 0

  format.order.forEach((part, position) => {
    const chunk = digits.slice(index, index + PART_LENGTH[part])
    index += PART_LENGTH[part]
    if (chunk === '') return
    result += (position > 0 ? format.separator : '') + chunk
  })

  return result
}

export function formatDisplay(iso: string, format: DateFormat): string {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso)
  if (!match) return ''

  const values: Record<DatePart, string> = { year: match[1], month: match[2], day: match[3] }
  return format.order.map((part) => values[part]).join(format.separator)
}

// Returns an ISO date for complete, real dates only (31/02 is rejected), otherwise null.
export function parseDisplay(text: string, format: DateFormat): string | null {
  const digits = text.replace(/\D/g, '')
  if (digits.length !== TOTAL_DIGITS) return null

  const values = {} as Record<DatePart, number>
  let index = 0
  for (const part of format.order) {
    values[part] = Number(digits.slice(index, index + PART_LENGTH[part]))
    index += PART_LENGTH[part]
  }

  const { year, month, day } = values
  const date = new Date(Date.UTC(year, month - 1, day))
  const isReal =
    year >= 1 &&
    date.getUTCFullYear() === year &&
    date.getUTCMonth() === month - 1 &&
    date.getUTCDate() === day
  if (!isReal) return null

  const pad = (value: number, length: number) => String(value).padStart(length, '0')
  return `${pad(year, 4)}-${pad(month, 2)}-${pad(day, 2)}`
}
