<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

export type ResponsiveTabItem = {
  value: string
  label: string
  icon?: string
  badge?: number | string
  disabled?: boolean
}

const props = withDefaults(
  defineProps<{
    tabs: ResponsiveTabItem[]
    ariaLabel?: string
  }>(),
  {
    ariaLabel: 'Sections'
  }
)

const model = defineModel({ required: true })

const tablistRef = ref<HTMLElement | null>(null)
const tabRefs = ref<Partial<Record<string, HTMLButtonElement>>>({})
const canScrollLeft = ref(false)
const canScrollRight = ref(false)
const isOverflowing = ref(false)
const scrollThumb = ref({ width: 100, offset: 0 })

let resizeObserver: ResizeObserver | null = null

function setTabRef(value: string, el: HTMLButtonElement | null) {
  if (el) {
    tabRefs.value[value] = el
    return
  }

  delete tabRefs.value[value]
}

function scrollActiveTabToCenter(behavior: ScrollBehavior = 'smooth') {
  const container = tablistRef.value
  const tab = tabRefs.value[model.value]
  if (!container || !tab) return

  const maxScroll = container.scrollWidth - container.clientWidth
  if (maxScroll <= 0) return

  const containerRect = container.getBoundingClientRect()
  const tabRect = tab.getBoundingClientRect()
  const tabLeft = tabRect.left - containerRect.left + container.scrollLeft
  const scrollLeft = Math.max(
    0,
    Math.min(tabLeft + tabRect.width / 2 - containerRect.width / 2, maxScroll)
  )

  container.scrollTo({ left: scrollLeft, behavior })
}

async function syncActiveTabScroll(behavior: ScrollBehavior = 'smooth') {
  updateScrollMetrics()
  await nextTick()
  await new Promise<void>((resolve) => {
    requestAnimationFrame(() => {
      scrollActiveTabToCenter(behavior)
      resolve()
    })
  })
  updateScrollMetrics()
}

function selectTab(tab: ResponsiveTabItem) {
  if (tab.disabled || tab.value === model.value) return
  model.value = tab.value
}

function updateScrollMetrics() {
  const el = tablistRef.value
  if (!el) return

  const maxScroll = el.scrollWidth - el.clientWidth
  isOverflowing.value = maxScroll > 2
  canScrollLeft.value = el.scrollLeft > 2
  canScrollRight.value = isOverflowing.value && el.scrollLeft < maxScroll - 2

  if (!isOverflowing.value) {
    scrollThumb.value = { width: 100, offset: 0 }
    return
  }

  const thumbWidth = (el.clientWidth / el.scrollWidth) * 100
  const scrollRatio = maxScroll > 0 ? el.scrollLeft / maxScroll : 0
  const thumbOffset = scrollRatio * (100 - thumbWidth)

  scrollThumb.value = { width: thumbWidth, offset: thumbOffset }
}

watch(
  () => props.tabs,
  () => syncActiveTabScroll('instant'),
  { deep: true }
)

watch(model, () => syncActiveTabScroll())

onMounted(async () => {
  const el = tablistRef.value
  if (!el) return

  el.addEventListener('scroll', updateScrollMetrics, { passive: true })
  resizeObserver = new ResizeObserver(() => updateScrollMetrics())
  resizeObserver.observe(el)

  await syncActiveTabScroll('instant')
})

onUnmounted(() => {
  tablistRef.value?.removeEventListener('scroll', updateScrollMetrics)
  resizeObserver?.disconnect()
})
</script>

<template>
  <div class="responsive-tabs-wrap">
    <span
      class="responsive-tabs-fade responsive-tabs-fade--start"
      :class="{ visible: canScrollLeft }"
      aria-hidden="true"
    />
    <nav
      ref="tablistRef"
      class="responsive-tabs"
      :class="{ 'is-overflowing': isOverflowing }"
      role="tablist"
      :aria-label="ariaLabel"
    >
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :ref="(el) => setTabRef(tab.value, el as HTMLButtonElement | null)"
        type="button"
        class="responsive-tab"
        :class="{ active: model === tab.value }"
        role="tab"
        :aria-selected="model === tab.value"
        :disabled="tab.disabled"
        @click="selectTab(tab)"
      >
        <span class="responsive-tab-content">
          <i v-if="tab.icon" class="bi" :class="tab.icon" aria-hidden="true"></i>
          <span class="responsive-tab-label">{{ tab.label }}</span>
          <span
            v-if="tab.badge != null && tab.badge !== ''"
            class="responsive-tab-badge"
          >
            {{ tab.badge }}
          </span>
        </span>
      </button>
    </nav>
    <span
      class="responsive-tabs-fade responsive-tabs-fade--end"
      :class="{ visible: canScrollRight }"
      aria-hidden="true"
    />
    <div
      v-if="isOverflowing"
      class="responsive-tabs-progress"
      aria-hidden="true"
    >
      <div
        class="responsive-tabs-progress-thumb"
        :style="{
          width: `${scrollThumb.width}%`,
          marginLeft: `${scrollThumb.offset}%`
        }"
      />
    </div>
  </div>
</template>

<style scoped>
.responsive-tabs-wrap {
  --tabs-line-width: 2px;
  position: relative;
}

.responsive-tabs-fade {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2.75rem;
  pointer-events: none;
  z-index: 1;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.responsive-tabs-fade.visible {
  opacity: 1;
}

.responsive-tabs-fade--start {
  left: 0;
  background: linear-gradient(
    to right,
    var(--bs-body-bg) 0%,
    var(--bs-body-bg) 25%,
    transparent 100%
  );
}

.responsive-tabs-fade--end {
  right: 0;
  background: linear-gradient(
    to left,
    var(--bs-body-bg) 0%,
    var(--bs-body-bg) 25%,
    transparent 100%
  );
}

.responsive-tabs {
  display: flex;
  flex-wrap: nowrap;
  justify-content: center;
  gap: 0;
  box-shadow: inset 0 calc(-1 * var(--tabs-line-width)) 0 0 var(--bs-border-color);
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.responsive-tabs.is-overflowing {
  justify-content: flex-start;
  box-shadow: none;
}

.responsive-tabs::-webkit-scrollbar {
  display: none;
}

.responsive-tab {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1 1 0;
  min-width: max-content;
  margin: 0;
  padding: 0.5rem 1rem 0.65rem;
  border: 0;
  position: relative;
  background: transparent;
  color: var(--bs-secondary-color);
  font-size: 0.95rem;
  font-weight: 500;
  line-height: 1.25;
  text-align: center;
  white-space: nowrap;
  transition: color 0.15s ease;
}

.responsive-tab-content {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
}

.responsive-tab:hover:not(:disabled):not(.active) {
  color: var(--bs-body-color);
}

.responsive-tab.active {
  color: var(--bs-nav-tabs-link-active-color);
  background-color: var(--bs-secondary-bg);
  border-radius: 0.25rem 0.25rem 0 0;
}

.responsive-tab:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.responsive-tab-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.25rem;
  padding: 0.1rem 0.4rem;
  border-radius: 999px;
  background-color: var(--bs-primary);
  color: var(--bs-white);
  font-size: 0.7rem;
  font-weight: 600;
  line-height: 1.2;
}

.responsive-tabs-progress {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: var(--tabs-line-width);
  background-color: var(--bs-border-color);
  overflow: hidden;
  z-index: 2;
}

.responsive-tabs-progress-thumb {
  height: 100%;
  background-color: var(--bs-primary);
}
</style>
