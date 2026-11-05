<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import SePageNav from '@/modules/splitExpense/components/SePageNav.vue'
import ShowMorePagination from '@/modules/core/components/pagination/ShowMorePagination.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { useCategories } from '@/modules/splitExpense/composables/useCategories'
import { useInsights } from '@/modules/splitExpense/composables/useInsights'
import { searchConnectedUsers } from '@/modules/splitExpense/api/searchConnectedUsers'
import InsightsLineChart from '@/modules/splitExpense/components/InsightsLineChart.vue'
import { formatMonthLabel } from '@/modules/splitExpense/utils/expenseDates'
import { buildInsightCharts } from '@/modules/splitExpense/utils/insightChart'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import { useAuth } from '@/modules/user/stores/useAuth'
import type { ApiUser } from '@/modules/core/apiType'
import type { ApiSeCategory, ApiSeCategoryTotal, ApiSeCurrency } from '@/modules/splitExpense/types'

interface CurrencyTotal {
  currency: ApiSeCurrency
  amount: number
}

interface CategoryGroup {
  category: ApiSeCategory
  totals: CurrencyTotal[]
}

interface MonthGroup {
  month: string
  label: string
  totals: CurrencyTotal[]
  categories: CategoryGroup[]
}

const auth = useAuth()
const insights = useInsights()
const categoryData = useCategories()
const PAGE_SIZE = 6
const selectedCategoryId = ref<string | number>(0)
const selectedUsers = ref<ApiUser[]>([])

const lockedUsers = computed(() => [auth.user])
const isCategoryFiltered = computed(() => Number(selectedCategoryId.value) !== 0)

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
  isCategoryFiltered.value
    ? 'No expenses in this category.'
    : 'Add an expense to see your spending here.'
)

const monthGroups = computed<MonthGroup[]>(() => {
  const months = new Map<
    string,
    {
      group: MonthGroup
      categories: Map<number, CategoryGroup>
      totals: Map<number, CurrencyTotal>
    }
  >()

  for (const total of insights.categoryTotals.value) {
    let month = months.get(total.month)
    if (!month) {
      month = {
        group: {
          month: total.month,
          label: formatMonthLabel(total.month),
          totals: [],
          categories: []
        },
        categories: new Map(),
        totals: new Map()
      }
      months.set(total.month, month)
    }

    const monthTotal = month.totals.get(total.currency.id)
    if (monthTotal) {
      monthTotal.amount += total.amount
    } else {
      const newTotal = { currency: total.currency, amount: total.amount }
      month.totals.set(total.currency.id, newTotal)
      month.group.totals.push(newTotal)
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

const showChart = computed(
  () => insights.isLoaded.value && !insights.hasError.value && !insights.isEmpty.value
)

const chartLabel = computed(() => {
  if (!isCategoryFiltered.value) return 'Total'
  return (
    monthGroups.value.find((month) => month.categories.length > 0)?.categories[0].category.title ??
    'Total'
  )
})

const insightCharts = computed(() => buildInsightCharts(monthGroups.value, chartLabel.value))

function formatTotal(total: ApiSeCategoryTotal | CurrencyTotal): string {
  const amount = moneyUtils.fromMinorUnits(total.amount, total.currency.decimalPlaces)
  return `${amount} ${total.currency.code}`
}

function formatLine(label: string, totals: CurrencyTotal[]): string {
  return `${label}: ${totals.map((total) => formatTotal(total)).join(', ')}`
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
          <FormSelectUsers
            id="se-insights-users"
            v-model="selectedUsers"
            multiple
            label="Include payers"
            :search="searchConnectedUsers"
            :locked-users="lockedUsers"
          />

          <FormSelect
            id="se-insights-category"
            v-model="selectedCategoryId"
            label="Category"
            :options="categoryOptions"
            :disabled="categoryData.categories.value === null"
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
      </section>
    </div>

    <InsightsLineChart v-if="showChart" class="insights-page__chart" :charts="insightCharts" />

    <div v-if="showChart" class="container se-page d-flex flex-column align-items-center">
      <section class="se-panel" aria-label="Monthly totals">
        <div class="insights-list">
          <section v-for="month in monthGroups" :key="month.month" class="insights-month">
            <h2 class="insights-month__title">{{ month.label }}</h2>

            <ul class="insights-rows">
              <li>{{ formatLine('Total', month.totals) }}</li>

              <template v-if="!isCategoryFiltered">
                <li v-for="category in month.categories" :key="category.category.id">
                  {{ formatLine(category.category.title, category.totals) }}
                </li>
              </template>
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
.insights-page__chart {
  margin-block: 0.85rem 0.75rem;
}

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
  margin-top: 0.35rem;
  padding-inline: var(--ov-page-inset);
  line-height: 1.45;
}

.insights-month + .insights-month {
  margin-top: 0.85rem;
}

.insights-month__title {
  margin: 0;
  font: inherit;
}

.insights-rows {
  margin: 0.1rem 0 0;
  padding: 0 0 0 1rem;
  list-style: none;
}
</style>
