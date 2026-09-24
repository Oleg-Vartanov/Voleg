<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import FormSelectMenu from '@/modules/core/components/form/FormSelectMenu.vue'
import type { FormSelectOption } from '@/modules/core/components/form/types'

const pageIndex = defineModel<number>('pageIndex', { required: true })
const pageSize = defineModel<number>('pageSize')

const props = withDefaults(
  defineProps<{
    totalPages: number
    ariaLabel?: string
    pageSizeOptions?: number[]
  }>(),
  {
    ariaLabel: 'Pagination',
    pageSizeOptions: () => []
  }
)

// Keeps huge totals (e.g. "of 300M") from pushing the paginator past narrow screens.
const COMPACT_TOTAL_FROM = 10000
const compactFormat = new Intl.NumberFormat('en', { notation: 'compact' })
const fullFormat = new Intl.NumberFormat('en')

const hasPrev = computed(() => pageIndex.value > 1)
const hasNext = computed(() => pageIndex.value < props.totalPages)
const showPageSizeSelect = computed(
  () => pageSize.value !== undefined && props.pageSizeOptions.length > 0
)
const totalPagesFull = computed(() => fullFormat.format(props.totalPages))
const totalPagesLabel = computed(() =>
  props.totalPages >= COMPACT_TOTAL_FROM
    ? compactFormat.format(props.totalPages)
    : String(props.totalPages)
)
const pageInputDigits = computed(() => String(props.totalPages).length)
const pageSizeSelectOptions = computed<FormSelectOption[]>(() =>
  props.pageSizeOptions.map((option) => ({ value: option, label: String(option) }))
)

const pageInput = ref(String(pageIndex.value))
watch(pageIndex, (value) => {
  pageInput.value = String(value)
})

function goTo(nextPageIndex: number) {
  if (!Number.isFinite(nextPageIndex)) return
  if (nextPageIndex < 1 || nextPageIndex > props.totalPages) return
  if (nextPageIndex === pageIndex.value) return
  pageIndex.value = nextPageIndex
}

function commitPageInput() {
  goTo(Math.trunc(Number(pageInput.value)))
  pageInput.value = String(pageIndex.value)
}

function onPageSizeChange(value: string | number) {
  pageSize.value = Number(value)
  pageIndex.value = 1
}
</script>

<template>
  <nav class="d-flex justify-content-center mt-3" :aria-label="ariaLabel">
    <div class="app-pagination">
      <button
        type="button"
        class="app-pagination-control app-pagination-btn"
        :disabled="!hasPrev"
        aria-label="First page"
        @click="goTo(1)"
      >
        <i class="bi bi-chevron-double-left" aria-hidden="true"></i>
      </button>
      <button
        type="button"
        class="app-pagination-control app-pagination-btn"
        :disabled="!hasPrev"
        aria-label="Previous page"
        @click="goTo(pageIndex - 1)"
      >
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
      </button>

      <input
        v-model="pageInput"
        type="number"
        min="1"
        :max="totalPages"
        class="app-pagination-control app-pagination-input"
        :style="{ '--app-pagination-input-digits': pageInputDigits }"
        aria-label="Current page"
        @keyup.enter="commitPageInput"
        @blur="commitPageInput"
      />
      <span class="app-pagination-control app-pagination-text" :title="totalPagesFull">
        <span aria-hidden="true">of {{ totalPagesLabel }}</span>
        <span class="visually-hidden">of {{ totalPagesFull }}</span>
      </span>

      <button
        type="button"
        class="app-pagination-control app-pagination-btn"
        :disabled="!hasNext"
        aria-label="Next page"
        @click="goTo(pageIndex + 1)"
      >
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
      </button>
      <button
        type="button"
        class="app-pagination-control app-pagination-btn"
        :disabled="!hasNext"
        aria-label="Last page"
        @click="goTo(totalPages)"
      >
        <i class="bi bi-chevron-double-right" aria-hidden="true"></i>
      </button>

      <div v-if="showPageSizeSelect && pageSize !== undefined" class="app-pagination-size">
        <div class="app-pagination-page-size">
          <FormSelectMenu
            :model-value="pageSize"
            :options="pageSizeSelectOptions"
            aria-label="Items per page"
            @update:model-value="onPageSizeChange"
          />
        </div>
        <span class="app-pagination-control app-pagination-text">per page</span>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.app-pagination {
  --app-pagination-field-width: 3rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: stretch;
  gap: 0.25rem 0.125rem;
  min-width: 0;
  max-width: 100%;
  color: var(--ov-text);
}

/* The page size select and its label wrap to the next line together. */
.app-pagination-size {
  display: flex;
  align-items: stretch;
  gap: 0.125rem;
}

.app-pagination-control {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 1.625rem;
  margin: 0;
  padding: 0.125rem 0.4rem;
  border: 0;
  border-radius: 0;
  background: transparent;
  color: var(--ov-text);
  font-size: var(--ov-font-size-sm);
  line-height: 1.25;
}

.app-pagination-btn {
  cursor: pointer;
}

.app-pagination-text {
  white-space: nowrap;
}

.app-pagination-btn:hover:not(:disabled),
.app-pagination-btn:focus-visible,
.app-pagination-input:hover,
.app-pagination-input:focus {
  position: relative;
  z-index: 2;
  border-color: var(--bs-primary);
  outline: 0;
  background: var(--bs-primary);
  color: var(--bs-body-bg);
}

.app-pagination-btn:disabled {
  cursor: default;
  opacity: 0.4;
}

.app-pagination-input,
.app-pagination-page-size {
  box-sizing: border-box;
  flex: 0 0 var(--app-pagination-field-width);
  width: var(--app-pagination-field-width);
  min-width: var(--app-pagination-field-width);
  max-width: var(--app-pagination-field-width);
}

.app-pagination-input {
  /* Grows with the digit count of the last page so any page number fits. */
  --app-pagination-input-width: max(
    var(--app-pagination-field-width),
    calc(var(--app-pagination-input-digits, 1) * 1ch + 1rem)
  );
  flex-basis: var(--app-pagination-input-width);
  width: var(--app-pagination-input-width);
  min-width: var(--app-pagination-input-width);
  max-width: var(--app-pagination-input-width);
  border: var(--bs-border-width) solid var(--bs-primary);
  border-radius: var(--bs-border-radius-sm);
  text-align: center;
  -moz-appearance: textfield;
}

.app-pagination-input::-webkit-outer-spin-button,
.app-pagination-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.app-pagination-page-size {
  display: flex;
  align-self: stretch;
  padding: 0;
  border: 0;
}

.app-pagination-page-size :deep(.form-select-menu-trigger) {
  min-height: 1.625rem;
  padding: 0.125rem 0.35rem;
  border: var(--bs-border-width) solid var(--bs-primary);
  border-radius: var(--bs-border-radius-sm);
  background: transparent;
  color: var(--ov-text);
  font-size: var(--ov-font-size-sm);
  line-height: 1.25;
}

.app-pagination-page-size
  :deep(.form-select-menu--open:not(.form-select-menu--up) .form-select-menu-trigger) {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.app-pagination-page-size :deep(.form-select-menu--up .form-select-menu-trigger) {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.app-pagination-page-size :deep(.form-select-menu-trigger:hover),
.app-pagination-page-size :deep(.form-select-menu-trigger:focus),
.app-pagination-page-size :deep(.form-select-menu--open .form-select-menu-trigger) {
  background: var(--bs-primary);
  color: var(--bs-body-bg);
}
</style>
