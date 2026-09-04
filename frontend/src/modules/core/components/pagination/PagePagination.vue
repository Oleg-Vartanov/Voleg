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

const hasPrev = computed(() => pageIndex.value > 1)
const hasNext = computed(() => pageIndex.value < props.totalPages)
const showPageSizeSelect = computed(
  () => pageSize.value !== undefined && props.pageSizeOptions.length > 0
)
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
        aria-label="Current page"
        @keyup.enter="commitPageInput"
        @blur="commitPageInput"
      />
      <span class="app-pagination-control app-pagination-text">of {{ totalPages }}</span>

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

      <template v-if="showPageSizeSelect && pageSize !== undefined">
        <div class="app-pagination-page-size">
          <FormSelectMenu
            :model-value="pageSize"
            :options="pageSizeSelectOptions"
            aria-label="Items per page"
            @update:model-value="onPageSizeChange"
          />
        </div>
        <span class="app-pagination-control app-pagination-text">per page</span>
      </template>
    </div>
  </nav>
</template>

<style scoped>
.app-pagination {
  --app-pagination-field-width: 3rem;
  display: inline-flex;
  align-items: stretch;
  gap: 0.125rem;
  color: var(--ov-text);
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
  font-size: 0.8125rem;
  line-height: 1.25;
}

.app-pagination-btn {
  cursor: pointer;
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
  font-size: 0.8125rem;
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
