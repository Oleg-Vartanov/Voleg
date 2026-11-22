import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import dateUtils from '@/modules/core/utils/dateUtils'

// "Today" is the user's own calendar day. Run with TZ=America/Los_Angeles or TZ=Asia/Tokyo: the
// UTC day differs from the local day for part of every day there, which is what this guards against.
describe('dateUtils.todayIsoDate', () => {
  beforeEach(() => vi.useFakeTimers())
  afterEach(() => vi.useRealTimers())

  it('is the local day in the evening', () => {
    vi.setSystemTime(new Date(2026, 10, 21, 17, 30)) // 21 Nov 17:30 local
    expect(dateUtils.todayIsoDate()).toBe('2026-11-21')
  })

  it('is the local day just after midnight', () => {
    vi.setSystemTime(new Date(2026, 10, 22, 1, 30)) // 22 Nov 01:30 local
    expect(dateUtils.todayIsoDate()).toBe('2026-11-22')
  })

  it('handles the last moment of the year', () => {
    vi.setSystemTime(new Date(2026, 11, 31, 23, 59, 59)) // 31 Dec 23:59:59 local
    expect(dateUtils.todayIsoDate()).toBe('2026-12-31')
  })
})
