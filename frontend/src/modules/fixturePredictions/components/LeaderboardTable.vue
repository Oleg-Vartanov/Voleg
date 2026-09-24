<script setup lang="ts">
import PagePagination from '@/modules/core/components/pagination/PagePagination.vue'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { inject } from 'vue'

const tables: Tables = inject('tables')
</script>

<template>
  <div
    v-if="!tables.isLoading.value.leaderboard"
    class="leaderboard"
    role="table"
    aria-label="Leaderboard"
  >
    <div class="leaderboard-row leaderboard-row--head" role="row">
      <div class="leaderboard-rank" role="columnheader">#</div>
      <div class="leaderboard-name" role="columnheader">Name</div>
      <div class="leaderboard-points" role="columnheader">
        <span class="leaderboard-points-label">Period</span>
        <span class="leaderboard-points-label">Points</span>
      </div>
      <div class="leaderboard-points" role="columnheader">
        <span class="leaderboard-points-label">Total</span>
        <span class="leaderboard-points-label">Points</span>
      </div>
    </div>

    <div v-if="tables.leaderboard.value?.length === 0" class="leaderboard-empty" role="row">
      <div class="leaderboard-empty-cell" role="cell">No results found</div>
    </div>

    <div
      v-for="(user, index) in tables.leaderboard.value"
      :key="user.user.id"
      class="leaderboard-row"
      role="row"
    >
      <div class="leaderboard-rank" role="cell">
        {{ tables.leaderboardPagination.offset.value + index + 1 }}.
      </div>
      <div class="leaderboard-name" role="cell">{{ user.user.username }}</div>
      <div class="leaderboard-points" role="cell">{{ user.periodPoints ?? '-' }}</div>
      <div class="leaderboard-points" role="cell">{{ user.totalPoints ?? '-' }}</div>
    </div>
  </div>

  <PagePagination
    v-if="!tables.isLoading.value.leaderboard"
    :page-index="tables.leaderboardPagination.pageIndex.value"
    :page-size="tables.leaderboardPagination.pageSize.value"
    :page-size-options="[10, 20, 50]"
    :total-pages="tables.leaderboardPagination.totalPages.value"
    aria-label="Leaderboard pagination"
    @update:page-index="tables.setLeaderboardPage"
    @update:page-size="tables.setLeaderboardPageSize"
  />
</template>

<style scoped>
.leaderboard {
  display: grid;
  grid-template-columns: auto auto auto auto;
  width: max-content;
  max-width: 100%;
  margin-inline: auto;
  font-size: var(--ov-font-size-sm);
}

.leaderboard-row,
.leaderboard-empty {
  display: contents;
}

.leaderboard-row > *,
.leaderboard-empty-cell {
  padding: 0.35rem 0.4rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.leaderboard-rank,
.leaderboard-points {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.leaderboard-name {
  text-align: start;
  white-space: nowrap;
}

.leaderboard-row--head > * {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  text-align: center;
  font-weight: 600;
  color: var(--bs-secondary-color);
  border-bottom-color: var(--bs-primary);
}

.leaderboard-points-label {
  display: block;
  line-height: 1.15;
  white-space: nowrap;
}

.leaderboard-empty-cell {
  grid-column: 1 / -1;
  padding: 1rem 0;
  text-align: center;
  color: var(--bs-secondary-color);
  opacity: 0.9;
}
</style>
