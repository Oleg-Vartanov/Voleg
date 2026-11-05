<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import type { InsightChartModel } from '@/modules/splitExpense/utils/insightChart'

const PAD_TOP = 12
const PLOT_HEIGHT = 168
const BASELINE_Y = PAD_TOP + PLOT_HEIGHT
const X_MONTH_Y = 14
const X_YEAR_Y = 28
const SVG_HEIGHT = BASELINE_Y + X_YEAR_Y + 14
const PLOT_PAD_X = 20
const MONTH_SLOT = 40
const DEFAULT_AXIS = 28
const AXIS_LABEL_GAP = 8

interface ChartTooltip {
  currencyId: number
  monthIndex: number
  left: number
  top: number
}

interface ScrollAnchor {
  key: string
  offset: number
  atEnd: boolean
}

const props = defineProps<{
  charts: InsightChartModel[]
}>()

const COLUMN_REM = 26

const rootEl = ref<HTMLElement | null>(null)
const availableWidth = ref(0)
const horizontalPad = ref(0)
const yAxisWidths = ref<Record<string, number>>({})
const tooltip = ref<ChartTooltip | null>(null)
const selectedCurrencyId = ref<number | null>(null)

const axisEls = new Map<number, SVGSVGElement>()
const scrollEls = new Map<number, HTMLElement>()
const scrollAnchors = new Map<number, ScrollAnchor>()
let resizeObserver: ResizeObserver | null = null

interface ChartView {
  chart: InsightChartModel
  axis: number
  width: number
  slot: number
  ticks: number[]
  top: number
}

const activeCurrencyId = computed(() => {
  const charts = props.charts
  if (charts.length === 0) return null
  if (charts.some((chart) => chart.currencyId === selectedCurrencyId.value)) {
    return selectedCurrencyId.value
  }

  const newest = charts.filter((chart) => (chart.amounts.at(-1) ?? 0) > 0)
  return (newest[0] ?? charts[0]).currencyId
})

const view = computed<ChartView | null>(() => {
  const chart = props.charts.find((item) => item.currencyId === activeCurrencyId.value)
  if (!chart) return null

  const ticks = axisTicks(maxAmount(chart))
  const top = ticks[ticks.length - 1] || 1
  const axis = yAxisWidths.value[chart.currencyId] ?? DEFAULT_AXIS
  const count = Math.max(chart.months.length, 1)
  const slot = MONTH_SLOT
  const width = PLOT_PAD_X * 2 + slot * count
  return { chart, axis, width, slot, ticks, top }
})

/** Match the page column, then grow with the plot when the screen has room. */
const outerWidth = computed(() => {
  const current = view.value
  const available = availableWidth.value
  if (!current || available <= 0) return null

  const rootFont = Number.parseFloat(getComputedStyle(document.documentElement).fontSize)
  const column = (Number.isNaN(rootFont) ? 16 : rootFont) * COLUMN_REM
  const natural = current.axis + current.width + horizontalPad.value
  return Math.round(Math.min(available, Math.max(column, natural)))
})

const activeTooltip = computed(() => {
  const current = tooltip.value
  const currentView = view.value
  if (!current || !currentView || currentView.chart.currencyId !== current.currencyId) return null
  const month = currentView.chart.months[current.monthIndex]
  if (!month) return null
  return { ...current, view: currentView, month }
})

function maxAmount(chart: InsightChartModel): number {
  return chart.amounts.reduce((max, amount) => Math.max(max, amount), 0)
}

/** Nice steps of 1, 2, or 5 × a power of ten, in minor units. */
function axisTicks(maxMinor: number): number[] {
  if (maxMinor <= 0) return [0]

  const rough = maxMinor / 4
  const magnitude = 10 ** Math.floor(Math.log10(rough))
  const normalized = rough / magnitude
  const nice = normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10
  const step = Math.max(1, Math.round(nice * magnitude))
  const count = Math.max(1, Math.ceil(maxMinor / step - 1e-9))

  return Array.from({ length: count + 1 }, (_, index) => index * step)
}

function yOf(amount: number, top: number): number {
  const scale = top <= 0 ? 1 : top
  return BASELINE_Y - (amount / scale) * PLOT_HEIGHT
}

function pointX(view: ChartView, index: number): number {
  return PLOT_PAD_X + view.slot * index + view.slot / 2
}

function linePoints(view: ChartView): string {
  return view.chart.amounts
    .map((amount, index) => `${pointX(view, index)},${yOf(amount, view.top)}`)
    .join(' ')
}

function formatAxisValue(minor: number, decimals: number): string {
  const text = moneyUtils.fromMinorUnits(minor, decimals)
  if (!text.includes('.')) return text
  return text.replace(/0+$/, '').replace(/\.$/, '')
}

function formatAmount(minor: number, chart: InsightChartModel): string {
  return `${moneyUtils.fromMinorUnits(minor, chart.decimalPlaces)} ${chart.currencyCode}`
}

function isActive(view: ChartView, index: number): boolean {
  const current = tooltip.value
  return current?.currencyId === view.chart.currencyId && current.monthIndex === index
}

function pointRadius(view: ChartView, index: number): number {
  return isActive(view, index) ? 4.2 : 3
}

function setAxisEl(currencyId: number, el: unknown) {
  if (el instanceof SVGSVGElement) axisEls.set(currencyId, el)
  else if (el === null) axisEls.delete(currencyId)
}

function setScrollEl(currencyId: number, el: unknown) {
  if (el instanceof HTMLElement) scrollEls.set(currencyId, el)
  else if (el === null) scrollEls.delete(currencyId)
}

function updateAvailableWidth() {
  const el = rootEl.value
  const parent = el?.parentElement
  if (!el || !parent) return

  const parentStyle = getComputedStyle(parent)
  const parentPad =
    Number.parseFloat(parentStyle.paddingLeft) + Number.parseFloat(parentStyle.paddingRight)
  availableWidth.value = Math.max(0, parent.clientWidth - (Number.isNaN(parentPad) ? 0 : parentPad))

  const style = getComputedStyle(el)
  const pad = Number.parseFloat(style.paddingLeft) + Number.parseFloat(style.paddingRight)
  horizontalPad.value = Number.isNaN(pad) ? 0 : pad
}

function measureAxes() {
  const chart = view.value?.chart
  if (!chart) return

  const svg = axisEls.get(chart.currencyId)
  let max = 0
  svg?.querySelectorAll('text').forEach((node) => {
    const box = typeof node.getBBox === 'function' ? node.getBBox() : null
    const length =
      box && box.width > 0
        ? box.width
        : typeof node.getComputedTextLength === 'function'
          ? node.getComputedTextLength()
          : 0
    max = Math.max(max, length)
  })
  const width = max > 0 ? Math.ceil(max) + AXIS_LABEL_GAP : DEFAULT_AXIS
  if (yAxisWidths.value[chart.currencyId] === width) return
  yAxisWidths.value = { ...yAxisWidths.value, [chart.currencyId]: width }
}

function rememberAnchor(view: ChartView, el: HTMLElement) {
  if (view.chart.months.length === 0 || view.slot <= 0) return
  const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 2
  const index = Math.min(
    view.chart.months.length - 1,
    Math.max(0, Math.round((el.scrollLeft - PLOT_PAD_X) / view.slot))
  )
  scrollAnchors.set(view.chart.currencyId, {
    key: view.chart.months[index].key,
    offset: pointX(view, index) - el.scrollLeft,
    atEnd
  })
}

function syncScroll() {
  const current = view.value
  if (!current) return
  const el = scrollEls.get(current.chart.currencyId)
  if (!el) return

  const anchor = scrollAnchors.get(current.chart.currencyId)
  if (!anchor || anchor.atEnd) {
    el.scrollLeft = el.scrollWidth
  } else {
    const index = current.chart.months.findIndex((month) => month.key === anchor.key)
    el.scrollLeft = index >= 0 ? pointX(current, index) - anchor.offset : el.scrollWidth
  }

  rememberAnchor(current, el)
}

function onChartScroll(view: ChartView, event: Event) {
  const el = event.currentTarget
  if (!(el instanceof HTMLElement)) return
  tooltip.value = null
  rememberAnchor(view, el)
}

function onPointerMove(view: ChartView, event: PointerEvent) {
  if (event.pointerType === 'touch') return
  const svg = event.currentTarget
  if (!(svg instanceof SVGSVGElement) || !rootEl.value || view.chart.months.length === 0) return

  const svgRect = svg.getBoundingClientRect()
  const rootRect = rootEl.value.getBoundingClientRect()
  const x = event.clientX - svgRect.left
  const index = Math.min(
    view.chart.months.length - 1,
    Math.max(0, Math.floor((x - PLOT_PAD_X) / view.slot))
  )
  const anchorX = svgRect.left - rootRect.left + pointX(view, index)
  const anchorY = svgRect.top - rootRect.top + yOf(view.chart.amounts[index] ?? 0, view.top)
  const tipWidth = 220
  const tipHeight = 62
  let left = anchorX + 14
  if (left + tipWidth > rootRect.width) left = Math.max(8, anchorX - tipWidth - 14)
  let top = anchorY - tipHeight
  if (top < 4) top = anchorY + 14

  tooltip.value = { currencyId: view.chart.currencyId, monthIndex: index, left, top }
}

function clearTooltip() {
  tooltip.value = null
}

async function relayout() {
  await nextTick()
  measureAxes()
  await nextTick()
  syncScroll()
}

function selectCurrency(currencyId: number) {
  tooltip.value = null
  selectedCurrencyId.value = currencyId
}

watch(() => props.charts, relayout, { flush: 'post' })
watch(outerWidth, relayout, { flush: 'post' })
watch(activeCurrencyId, relayout, { flush: 'post' })

onMounted(() => {
  updateAvailableWidth()
  const parent = rootEl.value?.parentElement
  if (parent && typeof ResizeObserver !== 'undefined') {
    resizeObserver = new ResizeObserver(() => updateAvailableWidth())
    resizeObserver.observe(parent)
  }
  void document.fonts?.ready.then(() => {
    relayout()
  })
  relayout()
})

onUnmounted(() => {
  resizeObserver?.disconnect()
})
</script>

<template>
  <section
    ref="rootEl"
    class="insights-chart"
    :style="outerWidth ? { width: `${outerWidth}px` } : undefined"
    aria-label="Monthly spending"
  >
    <p class="visually-hidden">
      One line shows the monthly total. The value axis stays visible while months scroll
      horizontally.
    </p>

    <div
      v-if="props.charts.length > 1"
      class="insights-chart__currencies"
      role="group"
      aria-label="Currency"
    >
      <button
        v-for="chart in props.charts"
        :key="chart.currencyId"
        type="button"
        class="insights-chart__currency"
        :class="{ 'is-active': chart.currencyId === activeCurrencyId }"
        :aria-pressed="chart.currencyId === activeCurrencyId"
        @click="selectCurrency(chart.currencyId)"
      >
        {{ chart.currencyCode }}
      </button>
    </div>

    <div v-if="view" class="insights-chart__block">
      <p class="insights-chart__unit" :style="{ width: `${view.axis}px` }">
        {{ view.chart.currencyCode }}
      </p>

      <div class="insights-chart__frame">
        <svg
          :ref="(el) => setAxisEl(view.chart.currencyId, el)"
          class="insights-chart__y"
          :width="view.axis"
          :height="SVG_HEIGHT"
          aria-hidden="true"
        >
          <line
            class="insights-chart__axis"
            :x1="view.axis - 1"
            :y1="PAD_TOP"
            :x2="view.axis - 1"
            :y2="BASELINE_Y"
          />
          <g v-for="tick in view.ticks" :key="tick">
            <line
              class="insights-chart__axis"
              :x1="view.axis - 4"
              :y1="yOf(tick, view.top)"
              :x2="view.axis"
              :y2="yOf(tick, view.top)"
            />
            <text
              class="insights-chart__label"
              :x="view.axis - AXIS_LABEL_GAP"
              :y="yOf(tick, view.top)"
              text-anchor="end"
              dominant-baseline="middle"
            >
              {{ formatAxisValue(tick, view.chart.decimalPlaces) }}
            </text>
          </g>
        </svg>

        <div
          :ref="(el) => setScrollEl(view.chart.currencyId, el)"
          class="insights-chart__scroll"
          role="region"
          tabindex="0"
          :aria-label="`${view.chart.currencyCode} chart. Scroll horizontally to see more months.`"
          @scroll="onChartScroll(view, $event)"
        >
          <svg
            class="insights-chart__plot"
            :width="view.width"
            :height="SVG_HEIGHT"
            aria-hidden="true"
            @pointermove="onPointerMove(view, $event)"
            @pointerleave="clearTooltip"
          >
            <line
              v-for="tick in view.ticks"
              :key="`grid-${tick}`"
              :class="tick === 0 ? 'insights-chart__axis' : 'insights-chart__grid'"
              x1="0"
              :x2="view.width"
              :y1="yOf(tick, view.top)"
              :y2="yOf(tick, view.top)"
            />

            <line
              v-if="tooltip?.currencyId === view.chart.currencyId"
              class="insights-chart__guide"
              :x1="pointX(view, tooltip.monthIndex)"
              :x2="pointX(view, tooltip.monthIndex)"
              :y1="PAD_TOP"
              :y2="BASELINE_Y"
            />

            <polyline class="insights-chart__series" :points="linePoints(view)" />
            <circle
              v-for="(amount, index) in view.chart.amounts"
              :key="view.chart.months[index].key"
              :cx="pointX(view, index)"
              :cy="yOf(amount, view.top)"
              :r="pointRadius(view, index)"
            />

            <text
              v-for="(month, index) in view.chart.months"
              :key="`month-${month.key}`"
              class="insights-chart__label"
              :x="pointX(view, index)"
              :y="BASELINE_Y + X_MONTH_Y"
              text-anchor="middle"
            >
              {{ month.monthLabel }}
            </text>
            <text
              v-for="(month, index) in view.chart.months"
              :key="`year-${month.key}`"
              class="insights-chart__year"
              :x="pointX(view, index)"
              :y="BASELINE_Y + X_YEAR_Y"
              text-anchor="middle"
            >
              {{ month.yearLabel }}
            </text>
          </svg>
        </div>
      </div>
    </div>

    <div
      v-if="activeTooltip"
      class="insights-chart__tooltip"
      :style="{ left: `${activeTooltip.left}px`, top: `${activeTooltip.top}px` }"
    >
      <p class="insights-chart__tooltip-title">
        {{ activeTooltip.month.label }}
      </p>
      <p class="insights-chart__tooltip-row">
        <span>{{ activeTooltip.view.chart.label }}</span>
        <span>{{
          formatAmount(
            activeTooltip.view.chart.amounts[activeTooltip.monthIndex],
            activeTooltip.view.chart
          )
        }}</span>
      </p>
    </div>
  </section>
</template>

<style scoped>
.insights-chart {
  position: relative;
  box-sizing: border-box;
  width: min(100%, 26rem);
  max-width: 100%;
  margin-inline: auto;
  padding-inline: var(--ov-page-inset);
  color: var(--ov-text);
  user-select: none;
}

.insights-chart__block {
  max-width: 100%;
  min-width: 0;
}

.insights-chart__block + .insights-chart__block {
  margin-top: 1.25rem;
}

.insights-chart__currencies {
  display: flex;
  justify-content: center;
  gap: 0.35rem;
  margin: 0 0 0.35rem;
}

.insights-chart__currency {
  border: 0;
  background: transparent;
  color: var(--bs-secondary-color);
  font: inherit;
  font-size: var(--ov-font-size-sm);
  font-weight: 600;
  padding: 0.1rem 0.4rem;
  cursor: pointer;
}

.insights-chart__currency.is-active {
  color: var(--ov-text);
  box-shadow: inset 0 -1px 0 currentColor;
}

.insights-chart__unit {
  margin: 0 0 0.15rem;
  padding-right: 8px;
  box-sizing: border-box;
  text-align: right;
  font-size: var(--ov-font-size-xs);
  font-weight: 600;
  letter-spacing: 0.04em;
  color: var(--bs-secondary-color);
}

.insights-chart__frame {
  display: flex;
  align-items: flex-start;
  width: 100%;
  min-width: 0;
}

.insights-chart__y {
  display: block;
  flex: none;
  overflow: visible;
}

.insights-chart__scroll {
  flex: 1 1 auto;
  min-width: 0;
  overflow-x: auto;
  overflow-y: hidden;
  overscroll-behavior-x: contain;
  scrollbar-width: thin;
  scrollbar-color: var(--bs-border-color) transparent;
}

.insights-chart__scroll:focus-visible {
  outline: 2px solid var(--bs-focus-ring-color);
  outline-offset: 2px;
}

.insights-chart__plot {
  display: block;
  cursor: crosshair;
}

.insights-chart__grid {
  stroke: currentColor;
  stroke-opacity: 0.16;
}

.insights-chart__axis {
  stroke: currentColor;
  stroke-opacity: 0.75;
}

.insights-chart__guide {
  stroke: currentColor;
  stroke-opacity: 0.45;
  stroke-dasharray: 3 3;
}

.insights-chart__label,
.insights-chart__plot text {
  fill: currentColor;
  font-family: inherit;
  font-size: 0.75rem;
}

text.insights-chart__year {
  fill: var(--bs-secondary-color);
  font-size: 0.6875rem;
}

.insights-chart__series {
  fill: none;
  stroke: currentColor;
  stroke-width: 2.25;
  stroke-linejoin: round;
  stroke-linecap: round;
}

.insights-chart__plot circle {
  fill: currentColor;
}

.insights-chart__tooltip {
  position: absolute;
  z-index: 3;
  pointer-events: none;
  min-width: 9rem;
  max-width: 16rem;
  padding: 0.4rem 0.6rem 0.45rem;
  border: 1px solid var(--bs-border-color);
  border-radius: 0.4rem;
  background: var(--ov-paper);
  text-align: start;
  font-size: var(--ov-font-size-sm);
  line-height: 1.35;
}

.insights-chart__tooltip-title {
  margin: 0 0 0.25rem;
  font-weight: 600;
}

.insights-chart__tooltip-row {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  margin: 0.1rem 0 0;
}

.insights-chart__tooltip-row span:last-child {
  margin-left: auto;
  padding-left: 0.75rem;
  font-variant-numeric: tabular-nums;
}
</style>
