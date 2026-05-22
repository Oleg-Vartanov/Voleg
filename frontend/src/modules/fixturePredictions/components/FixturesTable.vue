<script setup lang="ts">
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { type Predictions } from '@/modules/fixturePredictions/composables/usePredictions'
import { inject } from 'vue'

const tables: Tables = inject('tables')
const vs: Versus = inject('vs')
const predictions: Predictions = inject('predictions')
const auth = useAuth()
</script>

<template>
  <table v-if="!tables.isLoading.value.leaderboard" class="table table-sm">
    <thead>
      <tr>
        <th scope="col">Match</th>
        <th scope="col">Score</th>
        <th scope="col">
          {{ vs.users.value.length === 0 ? 'Prediction' : auth.user.displayName }}
        </th>
        <th v-if="vs.users.value.length === 0" scope="col">Points</th>
        <th v-for="vsUser in vs.users.value" :key="vsUser.id" scope="col">
          {{ vsUser.displayName }}
        </th>
        <th scope="col">Start</th>
      </tr>
    </thead>

    <tbody>
      <!-- No fixtures -->
      <tr v-if="tables.fixtures.value?.length === 0">
        <td colspan="6" class="text-center py-3 text-muted">No fixtures found</td>
      </tr>

      <!-- Fixtures -->
      <tr v-for="fixture in tables.fixtures.value" :key="fixture.id">
        <!-- Teams -->
        <td class="text-start">
          <span>
            <TeamLogo :team-name="fixture.homeTeam.name" />
            {{ fixture.homeTeam.name }}
          </span>
          <br />
          <span>
            <TeamLogo :team-name="fixture.awayTeam.name" />
            {{ fixture.awayTeam.name }}
          </span>
        </td>

        <!-- Score -->
        <td>
          {{ fixture.homeScore ?? '-' }}<br />
          {{ fixture.awayScore ?? '-' }}
        </td>

        <!-- Current user prediction -->
        <td
          :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, auth.user.id))"
        >
          {{ predictions.getPrediction(fixture.id, auth.user.id)?.homeScore ?? '-' }}<br />
          {{ predictions.getPrediction(fixture.id, auth.user.id)?.awayScore ?? '-' }}
        </td>

        <!-- Points -->
        <td v-if="vs.users.value.length === 0">
          {{ predictions.getPrediction(fixture.id, auth.user.id)?.points ?? '-' }}
        </td>

        <!-- Versus users predictions -->
        <td
          v-for="vsUser in vs.users.value"
          :key="vsUser.id"
          :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, vsUser.id))"
        >
          {{ predictions.getPrediction(fixture.id, vsUser.id)?.homeScore ?? '-' }}<br />
          {{ predictions.getPrediction(fixture.id, vsUser.id)?.awayScore ?? '-' }}
        </td>

        <!-- Fixture Start Date -->
        <td v-for="{ id, date, time } of [predictions.fixtureDate(fixture)]" :key="id">
          {{ time }}<br />{{ date }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
.table-sm {
  margin-bottom: 0;
}

.text-muted {
  opacity: 0.9;
}
</style>
