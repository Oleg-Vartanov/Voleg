<script setup lang="ts">
import { onMounted } from 'vue'
import BalanceTotal from '@/modules/splitExpense/components/BalanceTotal.vue'
import SePageNav from '@/modules/splitExpense/components/SePageNav.vue'
import { useBalances } from '@/modules/splitExpense/composables/useBalances'
import { balanceColor, formatBalance } from '@/modules/splitExpense/utils/balances'

const balances = useBalances()

onMounted(() => {
  balances.load()
})
</script>

<template>
  <div class="ov-center">
    <div class="container se-page d-flex flex-column align-items-center gap-2">
      <SePageNav class="se-page-inset" />

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
          <BalanceTotal
            :amounts="balances.totalAmounts.value"
            hint="Positive: you are owed. Negative: you owe."
          />

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
                  :class="balanceColor(amount)"
                >
                  {{ formatBalance(amount) }}
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

.balance-message {
  margin: 0;
  padding: 1rem;
  text-align: center;
  color: var(--bs-secondary-color);
}

.balance-message--lg {
  font-size: var(--ov-font-size-lg);
}

.balance-user {
  display: flex;
  align-items: center;
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
  white-space: nowrap;
}
</style>
