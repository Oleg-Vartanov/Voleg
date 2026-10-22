import {
  computed,
  nextTick,
  onMounted,
  onUnmounted,
  ref,
  toValue,
  watch,
  type MaybeRefOrGetter,
  type Ref
} from 'vue'

/** Above Bootstrap modal (1055) / popover (1070). */
const DEFAULT_Z_INDEX = 1080
const DEFAULT_MARGIN_PX = 8
const DEFAULT_MAX_HEIGHT_PX = 240
const DEFAULT_OFFSET_PX = 4
const MIN_HEIGHT_PX = 72

export type FixedDropdownPlacement = 'bottom' | 'top'

export interface UseFixedDropdownOptions {
  /** Element the menu is anchored to (trigger / control). */
  anchorEl: Ref<HTMLElement | null>
  /** In-page field root (label + control) — used for outside-click checks. */
  rootEl?: Ref<HTMLElement | null>
  /** Teleported panel — used for outside-click checks. */
  panelEl?: Ref<HTMLElement | null>
  /** When true, position listeners run and style is kept in sync. */
  isOpen: MaybeRefOrGetter<boolean>
  /** Soft cap before viewport clamping (px). */
  maxHeightPx?: number
  /** Gap from the viewport edge (px). */
  marginPx?: number
  /** Gap between the anchor and the panel (px). */
  offsetPx?: number
  zIndex?: number
}

/**
 * Fixed, teleported dropdown positioning: flip up/down and clamp max-height
 * to available viewport space so the menu never forces the modal to scroll.
 */
export function useFixedDropdown(options: UseFixedDropdownOptions) {
  const {
    anchorEl,
    rootEl,
    panelEl,
    isOpen,
    maxHeightPx = DEFAULT_MAX_HEIGHT_PX,
    marginPx = DEFAULT_MARGIN_PX,
    offsetPx = DEFAULT_OFFSET_PX,
    zIndex = DEFAULT_Z_INDEX
  } = options

  const placement = ref<FixedDropdownPlacement>('bottom')
  const dropdownStyle = ref<Record<string, string>>({})

  const isOpenUp = computed(() => placement.value === 'top')

  function syncDropdownPosition(): void {
    if (!toValue(isOpen)) {
      dropdownStyle.value = {}
      placement.value = 'bottom'
      return
    }

    const anchor = anchorEl.value
    if (!anchor) return

    const rect = anchor.getBoundingClientRect()
    const spaceBelow = window.innerHeight - rect.bottom - marginPx - offsetPx
    const spaceAbove = rect.top - marginPx - offsetPx
    const openUp = spaceBelow < maxHeightPx && spaceAbove > spaceBelow

    placement.value = openUp ? 'top' : 'bottom'
    const available = openUp ? spaceAbove : spaceBelow
    const clampedMax = Math.max(MIN_HEIGHT_PX, Math.min(maxHeightPx, available))

    if (openUp) {
      dropdownStyle.value = {
        top: 'auto',
        bottom: `${window.innerHeight - rect.top + offsetPx}px`,
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        maxHeight: `${clampedMax}px`,
        zIndex: String(zIndex)
      }
    } else {
      dropdownStyle.value = {
        top: `${rect.bottom + offsetPx}px`,
        bottom: 'auto',
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        maxHeight: `${clampedMax}px`,
        zIndex: String(zIndex)
      }
    }
  }

  function onReposition(): void {
    if (!toValue(isOpen)) return
    syncDropdownPosition()
  }

  function isEventInside(event: Event): boolean {
    const target = event.target as Node | null
    if (!target) return false
    if (rootEl?.value?.contains(target)) return true
    if (anchorEl.value?.contains(target)) return true
    if (panelEl?.value?.contains(target)) return true
    return false
  }

  watch(
    () => toValue(isOpen),
    async (open) => {
      if (!open) {
        dropdownStyle.value = {}
        placement.value = 'bottom'
        return
      }
      syncDropdownPosition()
      await nextTick()
      syncDropdownPosition()
    }
  )

  onMounted(() => {
    window.addEventListener('resize', onReposition)
    document.addEventListener('scroll', onReposition, true)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', onReposition)
    document.removeEventListener('scroll', onReposition, true)
  })

  return {
    dropdownStyle,
    placement,
    isOpenUp,
    syncDropdownPosition,
    isEventInside
  }
}
