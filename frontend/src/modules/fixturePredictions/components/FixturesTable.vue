<script setup lang="ts">
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue';
import { useAuth } from '@/modules/user/composables/useAuth';
import { useHeadToHead } from '@/modules/fixturePredictions/composables/useHeadToHead';
import { useTables } from '@/modules/fixturePredictions/composables/useTables';
import { usePredictions } from '@/modules/fixturePredictions/composables/usePredictions';

const auth = useAuth();
const h2h = useHeadToHead();
const tables = useTables();
const predictions = usePredictions();
</script>

<template>
  <table v-if="!tables.isLoading.value.leaderboard" class="table table-sm">
    <thead>
    <tr>
      <th scope="col">Match</th>
      <th scope="col">Score</th>
      <th scope="col">
        {{ h2h.users.value.length === 0 ? 'Prediction' : auth.user.displayName }}
      </th>
      <th scope="col" v-if="h2h.users.value.length === 0">Points</th>
      <th scope="col" v-for="h2hUser in h2h.users.value">{{ h2hUser.displayName }}</th>
      <th scope="col">Start</th>
    </tr>
    </thead>

    <tbody>

    <!-- No fixtures -->
    <tr v-if="tables.fixtures.value?.length === 0">
      <td colspan="6" class="text-center py-3 text-muted">
        No fixtures for this period
      </td>
    </tr>

    <!-- Fixtures -->
    <tr v-for="fixture in tables.fixtures.value" :key="fixture.id">

      <!-- Teams -->
      <td class="text-start">
        <span>
          <TeamLogo :teamName="fixture.homeTeam.name"/>
          {{ fixture.homeTeam.name }}
        </span>
        <br/>
        <span>
          <TeamLogo :teamName="fixture.awayTeam.name"/>
          {{ fixture.awayTeam.name }}
        </span>
      </td>

      <!-- Score -->
      <td>
        {{ fixture.homeScore ?? '-' }}<br/>
        {{ fixture.awayScore ?? '-' }}
      </td>

      <!-- Current user prediction -->
      <td :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, auth.user.id))">
        {{ predictions.getPrediction(fixture.id, auth.user.id)?.homeScore ?? '-' }}<br/>
        {{ predictions.getPrediction(fixture.id, auth.user.id)?.awayScore ?? '-' }}
      </td>

      <!-- Points -->
      <td v-if="h2h.users.value.length === 0">
        {{ predictions.getPrediction(fixture.id, auth.user.id)?.points ?? '-' }}
      </td>

      <!-- Head to Head users predictions -->
      <td
        v-for="h2hUser in h2h.users.value"
        :key="h2hUser.id"
        :class="predictions.scoreColorClass(predictions.getPrediction(fixture.id, h2hUser.id))"
      >
        {{ predictions.getPrediction(fixture.id, h2hUser.id)?.homeScore ?? '-' }}<br/>
        {{ predictions.getPrediction(fixture.id, h2hUser.id)?.awayScore ?? '-' }}
      </td>

      <!-- Fixture Start Date -->
      <td v-for="{ date, time } of [predictions.fixtureDate(fixture)]">
        {{ time }}<br/>{{ date }}
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
