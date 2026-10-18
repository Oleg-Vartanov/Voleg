<script setup lang="ts">
import AddAdjustmentModal from '@/modules/splitExpense/components/AddAdjustmentModal.vue'
import AddExpenseModal from '@/modules/splitExpense/components/AddExpenseModal.vue'
import AdjustmentDetailPanel from '@/modules/splitExpense/components/AdjustmentDetailPanel.vue'
import AdjustmentRow from '@/modules/splitExpense/components/AdjustmentRow.vue'
import DeleteAdjustmentModal from '@/modules/splitExpense/components/DeleteAdjustmentModal.vue'
import DeleteExpenseModal from '@/modules/splitExpense/components/DeleteExpenseModal.vue'
import EditAdjustmentModal from '@/modules/splitExpense/components/EditAdjustmentModal.vue'
import EditExpenseModal from '@/modules/splitExpense/components/EditExpenseModal.vue'
import ExpenseDetailPanel from '@/modules/splitExpense/components/ExpenseDetailPanel.vue'
import ExpenseRow from '@/modules/splitExpense/components/ExpenseRow.vue'
import BalanceTotal from '@/modules/splitExpense/components/BalanceTotal.vue'
import SePageNav from '@/modules/splitExpense/components/SePageNav.vue'
import ShowMorePagination from '@/modules/core/components/pagination/ShowMorePagination.vue'
import { useBalances } from '@/modules/splitExpense/composables/useBalances'
import { useFeed } from '@/modules/splitExpense/composables/useFeed'
import { groupByMonth } from '@/modules/splitExpense/utils/expenseDates'
import { feedItemDate, feedItemKey } from '@/modules/splitExpense/utils/feed'
import { computed, onMounted, ref } from 'vue'
import type { ApiSeAdjustment, ApiSeExpense, ApiSeFeedItem } from '@/modules/splitExpense/types'

const PAGE_SIZE = 25
const feed = useFeed()
const balances = useBalances()

const isAddExpenseOpen = ref(false)
const isEditExpenseOpen = ref(false)
const expenseToEdit = ref<ApiSeExpense | null>(null)
const isDeleteExpenseOpen = ref(false)
const expenseToDelete = ref<ApiSeExpense | null>(null)

const isAddAdjustmentOpen = ref(false)
const isEditAdjustmentOpen = ref(false)
const adjustmentToEdit = ref<ApiSeAdjustment | null>(null)
const isDeleteAdjustmentOpen = ref(false)
const adjustmentToDelete = ref<ApiSeAdjustment | null>(null)

const expandedKey = ref<string | null>(null)
const itemsByMonth = computed(() => groupByMonth(feed.items.value ?? [], feedItemDate))

const emptyText = computed(() => {
  const { expense, adjustment } = feed.filters
  if (expense && adjustment) return 'No expenses or adjustments yet'
  if (expense) return 'No expenses yet'
  if (adjustment) return 'No adjustments yet'
  return 'Select what to show'
})

const showBalanceSpinner = computed(() => balances.isLoading.value || !balances.isLoaded.value)

const showFeedSpinner = computed(
  () => feed.items.value === null || (feed.isLoading.value && !feed.isLoadingMore.value)
)

function toggleItem(item: ApiSeFeedItem) {
  const key = feedItemKey(item)
  expandedKey.value = expandedKey.value === key ? null : key
}

function isExpanded(item: ApiSeFeedItem) {
  return expandedKey.value === feedItemKey(item)
}

function reload() {
  feed.loadInitial(PAGE_SIZE)
  balances.load()
}

function requestEditExpense(expense: ApiSeExpense) {
  expenseToEdit.value = expense
  isEditExpenseOpen.value = true
}

function onExpenseUpdated(expense: ApiSeExpense) {
  feed.replaceExpense(expense)
  expenseToEdit.value = null
  balances.load()
}

function requestDeleteExpense(expense: ApiSeExpense) {
  expenseToDelete.value = expense
  isDeleteExpenseOpen.value = true
}

async function confirmDeleteExpense() {
  const expense = expenseToDelete.value
  if (!expense) return
  if (!(await feed.removeExpense(expense))) return

  isDeleteExpenseOpen.value = false
  expenseToDelete.value = null
  if (expandedKey.value === `expense-${expense.id}`) {
    expandedKey.value = null
  }
  balances.load()
}

function requestEditAdjustment(adjustment: ApiSeAdjustment) {
  adjustmentToEdit.value = adjustment
  isEditAdjustmentOpen.value = true
}

function onAdjustmentUpdated(adjustment: ApiSeAdjustment) {
  feed.replaceAdjustment(adjustment)
  adjustmentToEdit.value = null
  balances.load()
}

function requestDeleteAdjustment(adjustment: ApiSeAdjustment) {
  adjustmentToDelete.value = adjustment
  isDeleteAdjustmentOpen.value = true
}

async function confirmDeleteAdjustment() {
  const adjustment = adjustmentToDelete.value
  if (!adjustment) return
  if (!(await feed.removeAdjustment(adjustment))) return

  isDeleteAdjustmentOpen.value = false
  adjustmentToDelete.value = null
  if (expandedKey.value === `adjustment-${adjustment.id}`) {
    expandedKey.value = null
  }
  balances.load()
}

onMounted(reload)
</script>

<template>
  <div class="ov-center">
    <div class="container se-page d-flex flex-column align-items-center gap-2">
      <SePageNav class="se-page-inset" />

      <div class="se-panel">
        <div v-if="showBalanceSpinner" class="feed-balance feed-balance--status">
          <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading balance…</span>
          </div>
        </div>

        <router-link
          v-else-if="!balances.hasError.value"
          :to="{ name: 'seBalance' }"
          class="feed-balance"
          aria-label="View balance details"
        >
          <p v-if="balances.isSettled.value" class="feed-balance__settled">All settled up</p>

          <BalanceTotal v-else :amounts="balances.totalAmounts.value" compact />

          <i class="bi bi-chevron-right feed-balance__chevron" aria-hidden="true" />
        </router-link>

        <div class="feed-actions">
          <button type="button" class="btn btn-outline-primary" @click="isAddExpenseOpen = true">
            Add expense
          </button>
          <button type="button" class="btn btn-outline-primary" @click="isAddAdjustmentOpen = true">
            Add adjustment
          </button>
        </div>

        <div class="feed-filters">
          <div class="form-check form-check-inline">
            <input
              id="se-filter-expenses"
              v-model="feed.filters.expense"
              class="form-check-input"
              type="checkbox"
            />
            <label class="form-check-label" for="se-filter-expenses">Expenses</label>
          </div>
          <div class="form-check form-check-inline">
            <input
              id="se-filter-adjustments"
              v-model="feed.filters.adjustment"
              class="form-check-input"
              type="checkbox"
            />
            <label class="form-check-label" for="se-filter-adjustments">Adjustments</label>
          </div>
        </div>

        <AddExpenseModal v-model:open="isAddExpenseOpen" @created="reload" />

        <EditExpenseModal
          v-model:open="isEditExpenseOpen"
          :expense="expenseToEdit"
          @updated="onExpenseUpdated"
        />

        <DeleteExpenseModal
          v-model:open="isDeleteExpenseOpen"
          :expense="expenseToDelete"
          :deleting="feed.isDeleting.value"
          @confirm="confirmDeleteExpense"
        />

        <AddAdjustmentModal v-model:open="isAddAdjustmentOpen" @created="reload" />

        <EditAdjustmentModal
          v-model:open="isEditAdjustmentOpen"
          :adjustment="adjustmentToEdit"
          @updated="onAdjustmentUpdated"
        />

        <DeleteAdjustmentModal
          v-model:open="isDeleteAdjustmentOpen"
          :adjustment="adjustmentToDelete"
          :deleting="feed.isDeleting.value"
          @confirm="confirmDeleteAdjustment"
        />

        <div v-if="showFeedSpinner" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading feed…</span>
          </div>
        </div>

        <div v-else class="feed-list-scroll">
          <div class="feed-list">
            <p v-if="(feed.items.value ?? []).length === 0" class="feed-empty">{{ emptyText }}</p>

            <template v-for="group in itemsByMonth" :key="group.month">
              <div class="feed-month-header">{{ group.label }}</div>

              <article
                v-for="item in group.items"
                :key="feedItemKey(item)"
                class="feed-item"
                :class="{ 'is-expanded': isExpanded(item) }"
              >
                <template v-if="item.type === 'expense'">
                  <ExpenseRow :expense="item.expense" @toggle="toggleItem(item)" />
                  <ExpenseDetailPanel
                    :expense="item.expense"
                    :open="isExpanded(item)"
                    :deleting="feed.isDeleting.value"
                    @edit="requestEditExpense(item.expense)"
                    @delete="requestDeleteExpense(item.expense)"
                  />
                </template>
                <template v-else>
                  <AdjustmentRow :adjustment="item.adjustment" @toggle="toggleItem(item)" />
                  <AdjustmentDetailPanel
                    :adjustment="item.adjustment"
                    :open="isExpanded(item)"
                    :deleting="feed.isDeleting.value"
                    @edit="requestEditAdjustment(item.adjustment)"
                    @delete="requestDeleteAdjustment(item.adjustment)"
                  />
                </template>
              </article>
            </template>
          </div>

          <ShowMorePagination
            :has-more="feed.hasMore.value"
            :is-loading="feed.isLoadingMore.value"
            aria-label="Load more records"
            @load-more="feed.loadMore"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.feed-balance {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.5rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
  color: inherit;
  text-decoration: none;
}

.feed-balance--status {
  justify-content: center;
  min-height: 2.5rem;
}

.feed-balance:hover {
  background-color: rgba(var(--bs-emphasis-color-rgb), 0.04);
}

.feed-balance--status:hover {
  background-color: transparent;
}

.feed-balance__settled {
  flex: 1 1 auto;
  margin: 0;
  color: var(--bs-secondary-color);
}

.feed-balance__chevron {
  flex-shrink: 0;
  color: var(--bs-secondary-color);
}

.feed-actions {
  display: flex;
  gap: 0.5rem;
  margin-inline: var(--ov-page-inset);
}

.feed-actions > .btn {
  flex: 1 1 0;
}

.feed-filters {
  display: flex;
  justify-content: center;
}

.feed-filters .form-check-inline:last-child {
  margin-inline-end: 0;
}

.feed-list-scroll {
  -webkit-overflow-scrolling: touch;
  max-width: 100%;
}

.feed-list {
  width: 100%;
  --bs-table-hover-bg: rgba(var(--bs-emphasis-color-rgb), 0.075);
  --bs-table-hover-color: var(--bs-emphasis-color);
}

.feed-empty {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
  opacity: 0.9;
}

.feed-month-header {
  padding: 0.2rem 0.5rem;
  background-color: var(--bs-secondary-bg);
  font-weight: 600;
  font-size: var(--ov-font-size-label);
  line-height: 1.2;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.feed-item:not(.is-expanded) :deep(.expense-row),
.feed-item:not(.is-expanded) :deep(.adjustment-row) {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.feed-item.is-expanded {
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}
</style>
