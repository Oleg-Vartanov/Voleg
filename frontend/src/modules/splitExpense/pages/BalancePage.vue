<script setup lang="ts">
import { onMounted } from 'vue'
import { useBalances } from '@/modules/splitExpense/composables/useBalances'
import {
  balanceColor,
  balanceLabel,
  formatBalance,
  totalLabel
} from '@/modules/splitExpense/utils/balances'

const balances = useBalances()

onMounted(() => {
  balances.load()
})
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column align-items-center gap-2">
      <div class="se-panel">
        <div v-if="!balances.isLoaded.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading balances…</span>
          </div>
        </div>

        <div v-else-if="balances.hasError.value" class="balance-message">
          <p class="mb-2">Balances could not be loaded.</p>
          <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            :disabled="balances.isLoading.value"
            @click="balances.load()"
          >
            Try again
          </button>
        </div>

        <p v-else-if="balances.isSettled.value" class="balance-message balance-message--lg">
          All settled up
        </p>

        <template v-else>
          <section class="balance-total">
            <h2 class="balance-total__title">Total balance</h2>

            <ul class="balance-total__list">
              <li
                v-for="total in balances.totalAmounts.value"
                :key="total.currency.id"
                class="balance-total__item"
              >
                <span class="balance-total__amount" :class="balanceColor(total)">
                  {{ formatBalance(total) }}
                </span>
                <span class="balance-total__label">{{ totalLabel(total) }}</span>
              </li>
            </ul>
          </section>

          <ul class="list-group list-group-flush">
            <li
              v-for="userBalance in balances.byUserAmounts.value"
              :key="userBalance.user.id"
              class="list-group-item balance-user"
            >
              <span class="balance-user__name text-truncate">@{{ userBalance.user.username }}</span>

              <span class="balance-user__amounts">
                <span
                  v-for="amount in userBalance.amounts"
                  :key="amount.currency.id"
                  class="balance-user__amount"
                >
                  <span :class="balanceColor(amount)">{{ formatBalance(amount) }}</span>
                  <span class="balance-user__label">{{ balanceLabel(amount) }}</span>
                </span>
              </span>
            </li>
          </ul>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.balance-total {
  padding: 0.75rem 0.5rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.balance-total__title {
  margin: 0 0 0.35rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--bs-secondary-color);
}

.balance-total__list {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.balance-total__item {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
}

.balance-total__amount {
  font-size: 1.5rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.balance-total__label,
.balance-user__label {
  font-size: 0.75rem;
  color: var(--bs-secondary-color);
}

.balance-message {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
}

.balance-message--lg {
  font-size: 1.25rem;
}

.balance-user {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}

.balance-user__name {
  min-width: 0;
}

.balance-user__amounts {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.15rem;
  flex-shrink: 0;
  font-variant-numeric: tabular-nums;
}

.balance-user__amount {
  display: flex;
  align-items: baseline;
  gap: 0.35rem;
  white-space: nowrap;
}
</style>
