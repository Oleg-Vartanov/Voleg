<script setup lang="ts">
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue'
import { useAuth } from '@/modules/user/stores/useAuth'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { type Predictions } from '@/modules/fixturePredictions/composables/usePredictions'
import { inject } from 'vue'

const tables: Tables = inject('tables')
const predictions: Predictions = inject('predictions')
const auth = useAuth()
</script>

<template>
  <div
    id="predictionsModal"
    class="modal fade"
    tabindex="-1"
    data-bs-backdrop="static"
    aria-labelledby="predictionsModalLabel"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="predictionsModalLabel" class="modal-title fs-5">Predictions</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <form id="makePredictionsForm" @submit.prevent="predictions.makePredictions">
            <table v-if="!tables.isLoading.value.fixtures" class="table predictions-table">
              <thead>
                <tr>
                  <th scope="col">Match</th>
                  <th scope="col" class="predictions-col-score">Home</th>
                  <th scope="col" class="predictions-col-score">Away</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="fixture in tables.fixtures.value" :key="fixture.id">
                  <tr v-if="new Date(fixture.startAt) > new Date()">
                    <td class="text-start predictions-match">
                      <span class="predictions-team">
                        <TeamLogo :team-name="fixture.homeTeam.name" />
                        {{ fixture.homeTeam.name }}
                      </span>
                      <br />
                      <span class="predictions-team">
                        <TeamLogo :team-name="fixture.awayTeam.name" />
                        {{ fixture.awayTeam.name }}
                      </span>
                    </td>
                    <td class="predictions-score-cell">
                      <input
                        class="form-control"
                        type="number"
                        min="0"
                        max="99"
                        :name="'home-fixture-prediction-' + fixture.id"
                        :data-id="fixture.id"
                        data-side="home"
                        :value="
                          predictions.getHomeScore(
                            predictions.getPrediction(fixture.id, auth.user.id),
                            ''
                          )
                        "
                      />
                    </td>
                    <td class="predictions-score-cell">
                      <input
                        class="form-control"
                        type="number"
                        min="0"
                        max="99"
                        :name="'away-fixture-prediction-' + fixture.id"
                        :data-id="fixture.id"
                        data-side="away"
                        :value="
                          predictions.getAwayScore(
                            predictions.getPrediction(fixture.id, auth.user.id),
                            ''
                          )
                        "
                      />
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
          <button
            id="closePredictionsModal"
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Close
          </button>
          <button
            :disabled="predictions.isLoading.value"
            type="submit"
            form="makePredictionsForm"
            class="btn btn-primary"
          >
            Save
          </button>
          <div
            v-if="predictions.isLoading.value"
            class="spinner-border text-primary mt-3"
            role="status"
          >
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.predictions-table {
  font-size: var(--ov-font-size-sm);
}

.predictions-table thead th {
  font-weight: 600;
  color: var(--bs-secondary-color);
  text-align: center;
  vertical-align: bottom;
  border-bottom-color: var(--bs-primary);
}

.predictions-col-score {
  width: 20%;
  min-width: 80px;
}

.predictions-score-cell {
  vertical-align: middle;
  text-align: center;
}

.predictions-score-cell .form-control {
  display: inline-block;
  width: 3.25rem;
  margin-inline: auto;
  text-align: center;
  padding-inline: 0.35rem;
}

.predictions-match {
  font-size: var(--ov-font-size-sm);
}

.predictions-team {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  white-space: nowrap;
}
</style>
