<script setup lang="ts">
import type { ApiSeBalanceAmount } from '@/modules/splitExpense/types'
import { balanceColor, formatBalance } from '@/modules/splitExpense/utils/balances'

defineProps<{
  amounts: ApiSeBalanceAmount[]
  /** Compact single-line layout used on the feed summary. */
  compact?: boolean
  /** Optional help text shown under the amounts (balance page). */
  hint?: string
}>()
</script>

<template>
  <section class="balance-total" :class="{ 'balance-total--compact': compact }">
    <h2 class="balance-total__title">Total balance</h2>

    <ul
      class="balance-total__list"
      :class="{ 'balance-total__list--single': amounts.length === 1 }"
    >
      <li v-for="total in amounts" :key="total.currency.id" class="balance-total__item">
        <span class="balance-total__amount" :class="balanceColor(total)">
          {{ formatBalance(total) }}
        </span>
      </li>
    </ul>

    <p v-if="hint" class="balance-total__hint form-text">{{ hint }}</p>
  </section>
</template>

<style scoped>
.text-orange {
  color: var(--bs-orange);
}

.balance-total {
  padding: 0.6rem 0.5rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.balance-total__title {
  margin: 0 0 0.25rem;
  font-size: var(--ov-font-size-sm);
  font-weight: 600;
  text-transform: uppercase;
  text-align: center;
  color: var(--bs-secondary-color);
}

.balance-total__list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: 0.75rem;
  row-gap: 0.15rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.balance-total__list--single {
  grid-template-columns: 1fr;
}

.balance-total__item {
  min-width: 0;
  text-align: center;
}

.balance-total__amount {
  font-size: var(--ov-font-size-lg);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.balance-total__hint {
  margin: 0.35rem 0 0;
  text-align: center;
}

.balance-total--compact {
  flex: 1 1 auto;
  min-width: 0;
  padding: 0;
  border-bottom: none;
}

.balance-total--compact .balance-total__title {
  margin-bottom: 0.1rem;
}

.balance-total--compact .balance-total__list {
  row-gap: 0;
}

.balance-total--compact .balance-total__amount {
  font-size: inherit;
  font-weight: inherit;
}
</style>
