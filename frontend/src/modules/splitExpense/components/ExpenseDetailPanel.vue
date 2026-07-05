<script setup lang="ts">
import { useExpenseDisplay } from '@/modules/splitExpense/composables/useExpenseDisplay'
import type { ApiSeExpense } from '@/modules/splitExpense/types'
import { formatFullDate } from '@/modules/splitExpense/utils/expenseDates'
import { computed } from 'vue'

const props = defineProps<{
  expense: ApiSeExpense
  open: boolean
}>()

const ed = useExpenseDisplay()
const category = computed(() => ed.mapCategory(props.expense))
const splitUsers = computed(() => ed.mapSplitUsers(props.expense))
</script>

<template>
  <div class="expense-detail-panel" :class="{ 'is-open': open }">
    <div class="expense-detail-collapse">
      <div class="expense-detail-inner">
        <div class="expense-detail-content">
          <p class="expense-detail-title">
            <strong>{{ expense.title }}</strong>
          </p>

          <p v-if="expense.description" class="expense-detail-description">
            {{ expense.description }}
          </p>

          <p class="expense-detail-meta">
            Added by <strong>{{ expense.createdByUser.displayName }}</strong> on
            {{ formatFullDate(expense.expenseDate) }}
          </p>

          <p class="expense-detail-category-line">
            <i class="bi" :class="category.icon" aria-hidden="true"></i>
            {{ category.title }}
          </p>
        </div>

        <div class="expense-detail-side">
          <p class="expense-split-balance">
            Your split balance <strong :class="ed.amountColor(expense)">{{ ed.currentUserSplitBalance(expense) }}</strong>
          </p>

          <ul class="expense-split-users list-unstyled mb-0">
            <li
              v-for="splitUser in splitUsers"
              :key="splitUser.user.id"
              class="expense-split-user"
            >
              <span class="expense-split-user-text">
                <strong>{{ splitUser.user.displayName }}</strong>
                <template v-if="splitUser.paidAmount"> paid <strong>{{ splitUser.paidAmount }}</strong></template>
                <span v-if="splitUser.paidAmount"> share </span>
                <span v-else> owes </span>
                <strong>{{ splitUser.splitAmount }}</strong>
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.expense-detail-panel {
  overflow: hidden;
}

.expense-detail-panel.is-open {
  background-color: var(--bs-tertiary-bg);
}

.expense-detail-collapse {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s ease;
}

.expense-detail-panel.is-open .expense-detail-collapse {
  grid-template-rows: 1fr;
}

.expense-detail-inner {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 0.75rem 0;
  align-items: start;
  min-width: 0;
  overflow: hidden;
  overflow-wrap: anywhere;
  padding: 0;
  opacity: 0;
  transition:
    opacity 0.3s ease,
    padding 0.3s ease;
}

.expense-detail-content,
.expense-detail-side {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
  min-width: 0;
}

.expense-detail-content {
  align-items: flex-end;
  text-align: end;
  padding-inline-end: 1rem;
  border-inline-end: 1px solid var(--bs-border-color-translucent);
}

.expense-detail-side {
  align-items: flex-start;
  text-align: start;
  padding-inline-start: 1rem;
}

.expense-detail-panel.is-open .expense-detail-inner {
  padding: 0.75rem 1rem;
  opacity: 1;
  transition:
    opacity 0.25s ease 0.05s,
    padding 0.3s ease;
}

.expense-detail-title {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
}

.expense-detail-description {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
  color: var(--bs-secondary-color);
}

.expense-detail-meta,
.expense-detail-category-line,
.expense-split-balance,
.expense-split-user {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.35;
}

.expense-detail-category-line {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.expense-split-users {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.35rem;
  width: 100%;
  min-width: 0;
}

.expense-split-user-text {
  display: block;
  min-width: 0;
  overflow-wrap: anywhere;
}

@media (max-width: 767.98px) {
  .expense-detail-inner {
    grid-template-columns: minmax(0, 1fr);
    gap: 0.75rem;
  }

  .expense-detail-content {
    align-items: flex-start;
    text-align: start;
    padding-inline-end: 0;
    padding-bottom: 0.75rem;
    border-inline-end: none;
    border-bottom: 1px solid var(--bs-border-color-translucent);
  }

  .expense-detail-side {
    padding-inline-start: 0;
  }
}
</style>
