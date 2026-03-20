<script setup lang="ts">
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue';
import { useAuth } from '@/modules/user/stores/useAuth';
import { type Tables } from '@/modules/fixturePredictions/composables/useTables';
import { type Predictions } from '@/modules/fixturePredictions/composables/usePredictions';

const props = defineProps<{
  tables: Tables;
  predictions: Predictions;
}>();

const auth = useAuth();
</script>

<template>
  <div
    class="modal fade"
    id="predictionsModal"
    tabindex="-1"
    data-bs-backdrop="static"
    aria-labelledby="predictionsModalLabel"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="predictionsModalLabel">Predictions</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="makePredictionsForm" @submit.prevent="predictions.makePredictions">
            <table v-if="!tables.isLoading.value.fixtures" class="table">
              <thead>
              <tr>
                <th scope="col">Match</th>
                <th scope="col" style="width: 20%; min-width: 80px;">Home</th>
                <th scope="col" style="width: 20%; min-width: 80px;">Away</th>
              </tr>
              </thead>
              <tbody>
              <template v-for="fixture in tables.fixtures.value">
                <tr v-if="new Date(fixture.startAt) > new Date()">
                  <td class="text-start">
                        <span>
                          <TeamLogo :teamName="fixture.homeTeam.name"/>
                          {{ fixture.homeTeam.name }}
                        </span>
                    <br>
                    <span>
                          <TeamLogo :teamName="fixture.awayTeam.name"/>
                          {{ fixture.awayTeam.name }}
                        </span>
                  </td>
                  <td>
                    <input class="form-control"
                           type="number"
                           min="0" max="99"
                           :name="'home-fixture-prediction-'+fixture.id"
                           :data-id="fixture.id"
                           data-side="home"
                           :value="predictions.getHomeScore(predictions.getPrediction(fixture.id, auth.user.id), '')">
                  </td>
                  <td>
                    <input class="form-control"
                           type="number"
                           min="0" max="99"
                           :name="'away-fixture-prediction-'+fixture.id"
                           :data-id="fixture.id"
                           data-side="away"
                           :value="predictions.getAwayScore(predictions.getPrediction(fixture.id, auth.user.id), '')">
                  </td>
                </tr>
              </template>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="closePredictionsModal" data-bs-dismiss="modal">Close
          </button>
          <button :disabled="predictions.isLoading.value" type="submit" form="makePredictionsForm"
                  class="btn btn-primary">Save
          </button>
          <div v-if="predictions.isLoading.value" class="spinner-border text-primary mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
