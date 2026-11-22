import { todayIso } from '@/modules/core/utils/dateCalendar'

export default {
  format(date, format = 'YYYY-MM-DDThh:mm:ss') {
    const pad = (n) => n.toString().padStart(2, '0')

    const map = {
      YYYY: date.getFullYear(),
      MM: pad(date.getMonth() + 1),
      DD: pad(date.getDate()),
      hh: pad(date.getHours()),
      mm: pad(date.getMinutes()),
      ss: pad(date.getSeconds())
    }

    return Object.entries(map).reduce((prev, entry) => prev.replace(...entry), format)
  },
  getTimezone(date) {
    const pad = (n) => n.toString().padStart(2, '0')

    // getTimezoneOffset returns minutes difference from UTC:
    const offsetMinutes = date.getTimezoneOffset()
    const offsetSign = offsetMinutes > 0 ? '-' : '+'
    const offsetHours = pad(Math.floor(Math.abs(offsetMinutes) / 60))
    const offsetMins = pad(Math.abs(offsetMinutes) % 60)

    return `${offsetSign}${offsetHours}:${offsetMins}`
  },
  // The user's own calendar day. toISOString() would give the UTC day, which differs from the
  // local day for part of every day outside UTC (evening in the Americas, early morning in Asia).
  todayIsoDate(): string {
    return todayIso()
  }
}
