// Calendar side of FormInputDate. All dates are ISO strings (YYYY-MM-DD); arithmetic goes through
// UTC so daylight-saving changes can never shift a day.

export interface CalendarCell {
  iso: string
  day: number
  inMonth: boolean
}

const pad = (value: number, length = 2) => String(value).padStart(length, '0')
const DAY_MS = 86_400_000

function fromIso(iso: string): Date | null {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso)
  return match ? new Date(Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3]))) : null
}

function toIso(date: Date): string {
  return `${pad(date.getUTCFullYear(), 4)}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`
}

export function isValidIso(iso: string): boolean {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) return false
  const date = fromIso(iso)
  return date !== null && toIso(date) === iso
}

export function todayIso(now = new Date()): string {
  return `${pad(now.getFullYear(), 4)}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`
}

export function startOfMonth(iso: string): string {
  return `${iso.slice(0, 7)}-01`
}

export function addDays(iso: string, days: number): string {
  const date = fromIso(iso)
  if (!date) return iso
  return toIso(new Date(date.getTime() + days * DAY_MS))
}

// Moves by whole months; the day is clamped, so Jan 31 + 1 month is the last day of February.
export function addMonths(iso: string, months: number): string {
  const date = fromIso(iso)
  if (!date) return iso

  const target = date.getUTCFullYear() * 12 + date.getUTCMonth() + months
  const year = Math.floor(target / 12)
  const month = ((target % 12) + 12) % 12
  const lastDay = new Date(Date.UTC(year, month + 1, 0)).getUTCDate()

  return `${pad(year, 4)}-${pad(month + 1)}-${pad(Math.min(date.getUTCDate(), lastDay))}`
}

// Always 6 weeks (42 cells) so the popup keeps the same height from month to month.
// firstDay: 0 = Sunday ... 6 = Saturday.
export function monthGrid(iso: string, firstDay: number): CalendarCell[] {
  const first = fromIso(startOfMonth(iso))
  if (!first) return []

  const offset = (first.getUTCDay() - firstDay + 7) % 7
  const start = first.getTime() - offset * DAY_MS
  const month = first.getUTCMonth()

  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(start + index * DAY_MS)
    return { iso: toIso(date), day: date.getUTCDate(), inMonth: date.getUTCMonth() === month }
  })
}

// The Intl lookups below are costly to build and never change, so each is cached per locale.
const cached = <T>(store: Map<string, T>, locale: string | undefined, build: () => T): T => {
  const key = locale ?? ''
  if (!store.has(key)) store.set(key, build())
  return store.get(key) as T
}

const firstDayCache = new Map<string, number>()
const weekdayCache = new Map<string, string[]>()
const titleFormatCache = new Map<string, Intl.DateTimeFormat>()

// 0 = Sunday ... 6 = Saturday; Monday when the browser does not say.
export function detectFirstDay(locale?: string): number {
  return cached(firstDayCache, locale, () => lookUpFirstDay(locale))
}

function lookUpFirstDay(locale?: string): number {
  try {
    const intlLocale = new Intl.Locale(locale ?? navigator.language) as unknown as {
      getWeekInfo?: () => { firstDay: number }
      weekInfo?: { firstDay: number }
    }
    const firstDay = (intlLocale.getWeekInfo?.() ?? intlLocale.weekInfo)?.firstDay
    return firstDay === undefined ? 1 : firstDay % 7 // the API uses 7 for Sunday
  } catch {
    return 1
  }
}

export function weekdayLabels(firstDay: number, locale?: string): string[] {
  return cached(weekdayCache, `${locale ?? ''}|${firstDay}`, () => {
    const format = new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: 'UTC' })
    // 2023-01-01 was a Sunday.
    return Array.from({ length: 7 }, (_, index) =>
      format.format(new Date(Date.UTC(2023, 0, 1 + ((firstDay + index) % 7))))
    )
  })
}

export function monthTitle(iso: string, locale?: string): string {
  const date = fromIso(iso)
  if (!date) return ''
  const format = cached(
    titleFormatCache,
    locale,
    () => new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric', timeZone: 'UTC' })
  )
  return format.format(date)
}
