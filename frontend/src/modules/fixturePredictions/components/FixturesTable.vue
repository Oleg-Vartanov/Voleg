<script setup lang="ts">
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue'
import PagePagination from '@/modules/core/components/pagination/PagePagination.vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { type Predictions } from '@/modules/fixturePredictions/composables/usePredictions'
import { computed, inject } from 'vue'

const tables: Tables = inject('tables')
const vs: Versus = inject('vs')
const predictions: Predictions = inject('predictions')
const auth = useAuth()

const showPoints = computed(() => vs.users.value.length === 0)

/** Username header wrap width; also drives num-column `ch` size. */
const usernameCharsPerLine = 6

/** Split a username into rows (no ellipsis). */
function usernameLines(name: string): string[] {
  const lines: string[] = []
  for (let i = 0; i < name.length; i += usernameCharsPerLine) {
    lines.push(name.slice(i, i + usernameCharsPerLine))
  }
  return lines.length > 0 ? lines : [name]
}

/** Shared tracks: Start | Match | Score | Prediction | [Points | vs…] */
const tableStyle = computed(() => {
  const vsCount = vs.users.value.length
  const num = `calc(${usernameCharsPerLine}ch + 0.75rem)`
  const columns =
    vsCount === 0
      ? `auto auto ${num} ${num} ${num}`
      : `auto auto ${num} ${num} repeat(${vsCount}, ${num})`

  return { gridTemplateColumns: columns }
})
</script>

<template>
  <div
    v-if="!tables.isLoading.value.leaderboard"
    class="fixtures"
    role="table"
    aria-label="Fixtures"
    :style="tableStyle"
  >
    <div class="fixtures-row fixtures-row--head" role="row">
      <div class="fixtures-start" role="columnheader">Start</div>
      <div class="fixtures-match" role="columnheader">Match</div>
      <div class="fixtures-num" role="columnheader">
        <span class="fixtures-num-label">Score</span>
      </div>
      <div class="fixtures-num" role="columnheader">
        <span v-if="showPoints" class="fixtures-num-label">Pred</span>
        <span v-else class="fixtures-username" :title="auth.user.username">
          <span v-for="(line, i) in usernameLines(auth.user.username)" :key="i">{{ line }}</span>
        </span>
      </div>
      <div v-if="showPoints" class="fixtures-num" role="columnheader">
        <span class="fixtures-num-label">Points</span>
      </div>
      <div
        v-for="vsUser in vs.users.value"
        :key="vsUser.id"
        class="fixtures-num"
        role="columnheader"
        :title="vsUser.username"
      >
        <span class="fixtures-username">
          <span v-for="(line, i) in usernameLines(vsUser.username)" :key="i">{{ line }}</span>
        </span>
      </div>
    </div>

    <div v-if="tables.fixtures.value?.length === 0" class="fixtures-empty" role="row">
      <div class="fixtures-empty-cell" role="cell">No fixtures found</div>
    </div>

    <div v-for="fixture in tables.fixtures.value" :key="fixture.id" class="fixtures-row" role="row">
      <div class="fixtures-start" role="cell">
        <span>{{ predictions.fixtureDate(fixture).time }}</span>
        <span>{{ predictions.fixtureDate(fixture).date }}</span>
      </div>

      <div class="fixtures-match" role="cell">
        <div class="fixtures-team">
          <TeamLogo :team-name="fixture.homeTeam.name" />
          <span>{{ fixture.homeTeam.name }}</span>
        </div>
        <div class="fixtures-team">
          <TeamLogo :team-name="fixture.awayTeam.name" />
          <span>{{ fixture.awayTeam.name }}</span>
        </div>
      </div>

      <div class="fixtures-num" role="cell">
        <span>{{ fixture.homeScore ?? '-' }}</span>
        <span>{{ fixture.awayScore ?? '-' }}</span>
      </div>

      <div
        class="fixtures-num"
        role="cell"
        :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, auth.user.id))"
      >
        <span>{{ predictions.getPrediction(fixture.id, auth.user.id)?.homeScore ?? '-' }}</span>
        <span>{{ predictions.getPrediction(fixture.id, auth.user.id)?.awayScore ?? '-' }}</span>
      </div>

      <div v-if="showPoints" class="fixtures-num" role="cell">
        {{ predictions.getPrediction(fixture.id, auth.user.id)?.points ?? '-' }}
      </div>

      <div
        v-for="vsUser in vs.users.value"
        :key="vsUser.id"
        class="fixtures-num"
        role="cell"
        :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, vsUser.id))"
      >
        <span>{{ predictions.getPrediction(fixture.id, vsUser.id)?.homeScore ?? '-' }}</span>
        <span>{{ predictions.getPrediction(fixture.id, vsUser.id)?.awayScore ?? '-' }}</span>
      </div>
    </div>
  </div>

  <PagePagination
    v-if="!tables.isLoading.value.fixtures"
    :page-index="tables.fixturesPagination.pageIndex.value"
    :page-size="tables.fixturesPagination.pageSize.value"
    :page-size-options="[25, 50, 100]"
    :total-pages="tables.fixturesPagination.totalPages.value"
    aria-label="Fixtures pagination"
    @update:page-index="tables.setFixturesPage"
    @update:page-size="tables.setFixturesPageSize"
  />
</template>

<style scoped>
.fixtures {
  display: grid;
  width: max-content;
  max-width: 100%;
  margin-inline: auto;
  font-size: var(--ov-font-size-sm);
}

/* Flatten rows into the parent grid so column tracks are shared */
.fixtures-row,
.fixtures-empty {
  display: contents;
}

.fixtures-row > *,
.fixtures-empty-cell {
  padding: 0.35rem 0.4rem;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.fixtures-num,
.fixtures-start {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}

.fixtures-row--head > * {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  text-align: center;
  font-weight: 600;
  color: var(--bs-secondary-color);
  border-bottom-color: var(--bs-primary);
}

.fixtures-empty-cell {
  grid-column: 1 / -1;
  padding: 1rem 0;
  text-align: center;
  color: var(--bs-secondary-color);
  opacity: 0.9;
}

.fixtures-match {
  text-align: start;
  white-space: nowrap;
}

.fixtures-team {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  white-space: nowrap;
}

.fixtures-num-label {
  display: block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fixtures-username {
  display: flex;
  flex-direction: column;
  align-items: center;
  line-height: 1.15;
  white-space: normal;
  overflow: visible;
  font-variant-numeric: normal;
}
</style>
