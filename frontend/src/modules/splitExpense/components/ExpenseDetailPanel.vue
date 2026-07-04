<script setup lang="ts">
import ExpenseDetailField from '@/modules/splitExpense/components/ExpenseDetailField.vue'
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
const participants = computed(() => ed.participantsFor(props.expense))
</script>

<template>
  <div class="expense-detail-panel" :class="{ 'is-open': open }">
    <div class="expense-detail-collapse">
      <div class="expense-detail-inner">
        <ExpenseDetailField label="Title">
          {{ expense.title }}
        </ExpenseDetailField>

        <ExpenseDetailField v-if="expense.description" label="Description">
          {{ expense.description }}
        </ExpenseDetailField>

        <ExpenseDetailField label="Date">
          {{ formatFullDate(expense.expenseDate) }}
        </ExpenseDetailField>

        <ExpenseDetailField label="Category">
          <span class="expense-detail-category">
            <i class="bi" :class="category.icon"></i>
            {{ category.title }}
          </span>
        </ExpenseDetailField>

        <ExpenseDetailField label="Your split balance">
          <span :class="ed.amountColor(expense)">
            {{ ed.currentUserSplitBalance(expense) }}
          </span>
        </ExpenseDetailField>

        <ul class="expense-participants list-unstyled mb-0">
          <li
            v-for="participant in participants"
            :key="participant.key"
            class="expense-participant"
          >
            <span class="expense-participant-text">
              <strong>{{ participant.name }}</strong
              ><template v-if="participant.paidAmount"
                > paid <strong>{{ participant.paidAmount }}</strong></template
              ><template v-if="participant.splitAmount"
                > {{ participant.splitLabel }} <strong>{{ participant.splitAmount }}</strong></template
              >
            </span>
          </li>
        </ul>
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
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 0;
  overflow: hidden;
  overflow-wrap: anywhere;
  padding: 0;
  opacity: 0;
  transition:
    opacity 0.3s ease,
    padding 0.3s ease;
}

.expense-detail-panel.is-open .expense-detail-inner {
  padding: 0.75rem 1rem;
  opacity: 1;
  transition:
    opacity 0.25s ease 0.05s,
    padding 0.3s ease;
}

.expense-detail-category {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.expense-participants {
  display: flex;
  flex-direction: column;
  width: 100%;
  padding: 0;
  border-radius: 0.375rem;
  background-color: var(--bs-body-bg);
  border: 1px solid var(--bs-border-color-translucent);
  overflow: hidden;
}

.expense-participant {
  padding: 0.35rem 0.5rem;
  font-size: 0.85rem;
  line-height: 1.35;
}

.expense-participant + .expense-participant {
  border-top: 1px solid var(--bs-border-color-translucent);
}

.expense-participant-text {
  display: block;
  min-width: 0;
  overflow-wrap: anywhere;
}
</style>
