<script setup lang="ts">
import AddAdjustmentModal from '@/modules/splitExpense/components/AddAdjustmentModal.vue'
import DeleteAdjustmentModal from '@/modules/splitExpense/components/DeleteAdjustmentModal.vue'
import EditAdjustmentModal from '@/modules/splitExpense/components/EditAdjustmentModal.vue'
import AdjustmentDetailPanel from '@/modules/splitExpense/components/AdjustmentDetailPanel.vue'
import AdjustmentRow from '@/modules/splitExpense/components/AdjustmentRow.vue'
import ShowMorePagination from '@/modules/core/components/pagination/ShowMorePagination.vue'
import { useAdjustments } from '@/modules/splitExpense/composables/useAdjustments'
import { groupAdjustmentsByMonth } from '@/modules/splitExpense/utils/adjustmentDates'
import { computed, onMounted, ref } from 'vue'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'

const ADJUSTMENTS_PAGE_SIZE = 25
const adjustmentsState = useAdjustments()
const isAddAdjustmentOpen = ref(false)
const isDeleteModalOpen = ref(false)
const adjustmentToDelete = ref<ApiSeAdjustment | null>(null)
const isEditModalOpen = ref(false)
const adjustmentToEdit = ref<ApiSeAdjustment | null>(null)

const isEmpty = computed(() => (adjustmentsState.adjustments.value?.length ?? 0) === 0)
const expandedAdjustmentId = ref<number | null>(null)
const adjustmentsByMonth = computed(() =>
  groupAdjustmentsByMonth(adjustmentsState.adjustments.value ?? [])
)

function toggleAdjustment(adjustment: ApiSeAdjustment) {
  expandedAdjustmentId.value = expandedAdjustmentId.value === adjustment.id ? null : adjustment.id
}

function isExpanded(adjustment: ApiSeAdjustment) {
  return expandedAdjustmentId.value === adjustment.id
}

function requestEdit(adjustment: ApiSeAdjustment) {
  adjustmentToEdit.value = adjustment
  isEditModalOpen.value = true
}

function onUpdated(adjustment: ApiSeAdjustment) {
  adjustmentsState.replaceAdjustment(adjustment)
  adjustmentToEdit.value = null
}

function requestDelete(adjustment: ApiSeAdjustment) {
  adjustmentToDelete.value = adjustment
  isDeleteModalOpen.value = true
}

async function confirmDelete() {
  const adjustment = adjustmentToDelete.value
  if (!adjustment) return
  if (!(await adjustmentsState.removeAdjustment(adjustment))) return

  isDeleteModalOpen.value = false
  adjustmentToDelete.value = null
  if (expandedAdjustmentId.value === adjustment.id) {
    expandedAdjustmentId.value = null
  }
}

onMounted(() => {
  adjustmentsState.loadInitial(ADJUSTMENTS_PAGE_SIZE)
})
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column align-items-center gap-2">
      <div class="se-panel">
        <button
          type="button"
          class="btn btn-outline-primary w-100"
          @click="isAddAdjustmentOpen = true"
        >
          <i class="bi bi-plus-lg" aria-hidden="true"></i>
          Add adjustment
        </button>

        <AddAdjustmentModal
          v-model:open="isAddAdjustmentOpen"
          @created="adjustmentsState.loadInitial(ADJUSTMENTS_PAGE_SIZE)"
        />

        <EditAdjustmentModal
          v-model:open="isEditModalOpen"
          :adjustment="adjustmentToEdit"
          @updated="onUpdated"
        />

        <DeleteAdjustmentModal
          v-model:open="isDeleteModalOpen"
          :adjustment="adjustmentToDelete"
          :deleting="adjustmentsState.isDeleting.value"
          @confirm="confirmDelete"
        />

        <div
          v-if="adjustmentsState.isLoading.value && adjustmentsState.adjustments.value === null"
          class="text-center py-3"
        >
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading adjustments…</span>
          </div>
        </div>

        <div v-else class="adjustments-list-scroll">
          <div class="adjustments-list">
            <p v-if="isEmpty" class="adjustments-empty">No adjustments yet</p>

            <template v-for="group in adjustmentsByMonth" :key="group.month">
              <div class="adjustments-month-header">{{ group.label }}</div>

              <article
                v-for="adjustment in group.adjustments"
                :key="adjustment.id"
                class="adjustment-item"
                :class="{ 'is-expanded': isExpanded(adjustment) }"
              >
                <AdjustmentRow :adjustment="adjustment" @toggle="toggleAdjustment(adjustment)" />
                <AdjustmentDetailPanel
                  :adjustment="adjustment"
                  :open="isExpanded(adjustment)"
                  :deleting="adjustmentsState.isDeleting.value"
                  @edit="requestEdit(adjustment)"
                  @delete="requestDelete(adjustment)"
                />
              </article>
            </template>
          </div>

          <ShowMorePagination
            :has-more="adjustmentsState.hasMore.value"
            :is-loading="adjustmentsState.isLoading.value"
            aria-label="Load more adjustments"
            @load-more="adjustmentsState.loadMore"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.adjustments-list-scroll {
  -webkit-overflow-scrolling: touch;
  max-width: 100%;
}

.adjustments-list {
  width: 100%;
  --bs-table-hover-bg: rgba(var(--bs-emphasis-color-rgb), 0.075);
  --bs-table-hover-color: var(--bs-emphasis-color);
}

.adjustments-empty {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
  opacity: 0.9;
}

.adjustments-month-header {
  padding: 0.2rem 0.5rem;
  background-color: var(--bs-secondary-bg);
  font-weight: 600;
  font-size: 0.85rem;
  line-height: 1.2;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.adjustment-item:not(.is-expanded) :deep(.adjustment-row) {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.adjustment-item.is-expanded {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}
</style>
