<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import SePageNav from '@/modules/splitExpense/components/SePageNav.vue'
import ShowMorePagination from '@/modules/core/components/pagination/ShowMorePagination.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { useCategories } from '@/modules/splitExpense/composables/useCategories'
import { useInsights } from '@/modules/splitExpense/composables/useInsights'
import { searchConnectedUsers } from '@/modules/splitExpense/api/searchConnectedUsers'
import { categories as categoryIcons, type CategoryKey } from '@/modules/splitExpense/categories'
import { formatMonthLabel } from '@/modules/splitExpense/utils/expenseDates'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import { useAuth } from '@/modules/user/stores/useAuth'
import type { ApiUser } from '@/modules/core/apiType'
import type { ApiSeCategory, ApiSeCategoryTotal, ApiSeCurrency } from '@/modules/splitExpense/types'

interface CategoryGroup {
  category: ApiSeCategory
  totals: Array<{ currency: ApiSeCurrency; amount: number }>
}

interface MonthGroup {
  month: string
  label: string
  categories: CategoryGroup[]
}

const auth = useAuth()
const insights = useInsights()
const categoryData = useCategories()
const PAGE_SIZE = 6
const selectedCategoryId = ref<string | number>(0)
const selectedUsers = ref<ApiUser[]>([])

const lockedUsers = computed(() => [auth.user])

const categoryOptions = computed(() => [
  { value: 0, label: 'All categories' },
  ...categoryData.categoryOptions.value
])

const insightsHint = computed(() =>
  selectedUsers.value.length > 0
    ? 'Total cost paid by you and selected users'
    : 'Total cost of expenses you paid'
)

const emptyText = computed(() =>
  Number(selectedCategoryId.value) === 0
    ? 'Add an expense to see your spending here.'
    : 'No expenses in this category.'
)

const monthGroups = computed<MonthGroup[]>(() => {
  const months = new Map<string, { group: MonthGroup; categories: Map<number, CategoryGroup> }>()

  for (const total of insights.categoryTotals.value) {
    let month = months.get(total.month)
    if (!month) {
      month = {
        group: {
          month: total.month,
          label: formatMonthLabel(total.month),
          categories: []
        },
        categories: new Map()
      }
      months.set(total.month, month)
    }

    const category = month.categories.get(total.category.id)
    if (category) {
      category.totals.push({ currency: total.currency, amount: total.amount })
    } else {
      const newCategory = {
        category: total.category,
        totals: [{ currency: total.currency, amount: total.amount }]
      }
      month.categories.set(total.category.id, newCategory)
      month.group.categories.push(newCategory)
    }
  }

  return [...months.values()].map(({ group }) => group)
})

function categoryIcon(category: ApiSeCategory): string {
  return category.tag in categoryIcons
    ? categoryIcons[category.tag as CategoryKey]
    : categoryIcons.other
}

function formatTotal(total: ApiSeCategoryTotal | CategoryGroup['totals'][number]): string {
  const amount = moneyUtils.fromMinorUnits(total.amount, total.currency.decimalPlaces)
  return `${amount} ${total.currency.code}`
}

function reloadInsights(): void {
  const categoryId = Number(selectedCategoryId.value)
  insights.loadInitial(
    PAGE_SIZE,
    categoryId === 0 ? null : categoryId,
    selectedUsers.value.map((user) => user.id)
  )
}

onMounted(() => {
  categoryData.load()
  reloadInsights()
})

watch(selectedCategoryId, reloadInsights)
watch(selectedUsers, reloadInsights, { deep: true })
</script>

<template>
  <div class="ov-center">
    <div class="container se-page d-flex flex-column align-items-center gap-2">
      <SePageNav class="se-page-inset" />

      <section class="se-panel" aria-labelledby="insights-category-title">
        <header class="insights-header">
          <h1 id="insights-category-title" class="se-panel__title">Expenses by category</h1>
          <p class="insights-hint">{{ insightsHint }}</p>
        </header>

        <div class="insights-filter">
          <FormSelect
            id="se-insights-category"
            v-model="selectedCategoryId"
            label="Category"
            :options="categoryOptions"
            :disabled="categoryData.categories.value === null"
          />

          <FormSelectUsers
            id="se-insights-users"
            v-model="selectedUsers"
            multiple
            label="Include payers"
            :search="searchConnectedUsers"
            :locked-users="lockedUsers"
          />
        </div>

        <div v-if="!insights.isLoaded.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading insights…</span>
          </div>
        </div>

        <div v-else-if="insights.hasError.value" class="insights-message">
          <p class="mb-2">Insights could not be loaded.</p>
          <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            :disabled="insights.isLoading.value"
            @click="reloadInsights"
          >
            Try again
          </button>
        </div>

        <p v-else-if="insights.isEmpty.value" class="insights-message">
          {{ emptyText }}
        </p>

        <div v-else class="insights-list">
          <section v-for="month in monthGroups" :key="month.month">
            <h2 class="se-month-header se-month-header--compact">{{ month.label }}</h2>

            <ul class="list-group list-group-flush insights-categories">
              <li
                v-for="category in month.categories"
                :key="category.category.id"
                class="list-group-item insights-category"
              >
                <span class="insights-category__label">
                  <i
                    class="bi insights-category__icon"
                    :class="categoryIcon(category.category)"
                    aria-hidden="true"
                  ></i>
                  <span>{{ category.category.title }}</span>
                </span>

                <span class="insights-category__totals">
                  <span v-for="total in category.totals" :key="total.currency.id">
                    {{ formatTotal(total) }}
                  </span>
                </span>
              </li>
            </ul>
          </section>

          <ShowMorePagination
            :has-more="insights.hasMore.value"
            :is-loading="insights.isLoadingMore.value"
            aria-label="Load more months"
            @load-more="insights.loadMore(PAGE_SIZE)"
          />
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.insights-header {
  padding-inline: var(--ov-page-inset);
  text-align: center;
}

.insights-hint {
  margin: -0.15rem 0 0;
  color: var(--bs-secondary-color);
  font-size: var(--ov-font-size-sm);
}

.insights-message {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
}

.insights-filter {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding-inline: var(--ov-page-inset);
}

.insights-filter :deep(.mb-3) {
  margin-bottom: 0 !important;
}

.insights-list {
  margin-top: 0.25rem;
}

.insights-category {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.insights-category__label {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  min-width: 0;
}

.insights-category__icon {
  width: 1.25rem;
  flex-shrink: 0;
  text-align: center;
}

.insights-category__totals {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  flex-shrink: 0;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
</style>
