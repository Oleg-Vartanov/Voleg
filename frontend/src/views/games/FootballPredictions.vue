<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";
import TeamLogo from "@/components/games/fooball-predictions/TeamLogo.vue";

const topAlerts = useTopAlerts();

const start = ref(null);
const end = ref(null);
const fixtures = ref(null);
const leaderboard = ref(null);
const isLoading = ref({
  fixtures: false,
  leaderboard: false,
  predictions: false,
});

updateFixturesTable();

function initTable(tab: string) {
  if (tab === 'matches' && fixtures.value === null) {
    updateFixturesTable();
  }
  if (tab === 'leaderboard' && leaderboard.value === null) {
    updateLeaderboardTable();
  }
}

function updateLoadedTables() {
  if (fixtures.value !== null) {
    updateFixturesTable();
  }
  if (leaderboard.value !== null) {
    updateLeaderboardTable();
  }
}

function updateFixturesTable() {
  isLoading.value.fixtures = true;

  Client.showFixtures(start.value, end.value)
    .then((response) => {
      fixtures.value = response.data.fixtures;
      start.value = response.data.filters.start;
      end.value = response.data.filters.end;
    })
    .catch((axiosError) => {
      topAlerts.add(new Alert('Error during obtaining data.', 'danger', 10));
    })
    .finally(() => {
      isLoading.value.fixtures = false;
    })
}

function updateLeaderboardTable() {
  isLoading.value.leaderboard = true;

  Client.leaderboard(start.value, end.value)
    .then((response) => {
      leaderboard.value = response.data.users;
      start.value = response.data.filters.start;
      end.value = response.data.filters.end;
    })
    .catch((axiosError) => {
      topAlerts.add(new Alert('Error during obtaining data.', 'danger', 10));
    })
    .finally(() => {
      isLoading.value.leaderboard = false;
    })
}

function makePredictions(event: SubmitEvent) {
  isLoading.value.predictions = true;
  const form = event.target as HTMLFormElement;
  const elements = form.elements;

  const predictions = {};
  for (let element of elements) {
    if (element instanceof HTMLInputElement) {
      const fixtureId = element.dataset.id || null;
      const side = element.dataset.side || null;
      const value = element.value;

      if (fixtureId === null || side === null) {
        continue;
      }

      if (!predictions[fixtureId]) {
        predictions[fixtureId] = {
          fixtureId: fixtureId,
          homeScore: null,
          awayScore: null,
        };
      }

      if (side === 'home') {
        predictions[fixtureId].homeScore = value;
      }
      if (side === 'away') {
        predictions[fixtureId].awayScore = value;
      }
    }
  }

  // Exclude not filled fixtures.
  Object.entries(predictions).forEach(([index, prediction]) => {
    if (prediction.homeScore === '' || prediction.awayScore === '') {
      delete predictions[index]
    }
  });

  Client.makePredictions(Object.values(predictions))
    .then((response) => {
      updateLoadedTables();
      topAlerts.add(new Alert('Updated.', 'success', 10));
    })
    .catch((axiosError) => {
      switch (axiosError.response.status) {
        case 409:
          topAlerts.add(new Alert('Some fixtures has already started. Try to reload a page.', 'danger', 10));
          break;
        default:
          topAlerts.add(new Alert('Error. Try again later. Or contact support.', 'danger', 10));
      }
    })
    .finally(() => {
      isLoading.value.predictions = false;
      closeModal();
    })
}

function closeModal() {
  document.getElementById('closeModal').click();
}

function predictionHomeScore(fixture) {
  return fixture?.fixturePredictions[0]?.homeScore == null ? '-' : fixture.fixturePredictions[0].homeScore;
}
function predictionAwayScore(fixture) {
  return fixture?.fixturePredictions[0]?.awayScore == null ? '-' : fixture.fixturePredictions[0].awayScore;
}
</script>

<template>
  <div class="ov-center">
    <div class="container">

      <div class="d-flex justify-content-center">
        <div class="row">

          <div class="col-auto mb-2 p-1">
            <button v-if="!isLoading.fixtures"
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#predictionsModal"
            >Make Predictions</button>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text">Start</span>
              <input id="start" class="form-control" type="date" v-model="start"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text">End</span>
              <input id="end" class="form-control" type="date" v-model="end"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <button @click="updateLoadedTables" class="btn btn-outline-primary" type="button">Filter</button>
          </div>
        </div>

      </div>

      <div v-if="isLoading.fixtures || isLoading.leaderboard" class="spinner-border text-primary mt-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>

      <nav>
        <div class="nav nav-tabs justify-content-center" id="nav-tab" role="tablist">
          <button @click="initTable('matches')" class="nav-link active" id="nav-matches-tab" type="button" role="tab"
            data-bs-toggle="tab" data-bs-target="#nav-matches" aria-controls="nav-matches" aria-selected="true">Matches
          </button>
          <button @click="initTable('leaderboard')" class="nav-link" id="nav-leaderboard-tab" role="tab" type="button"
            data-bs-toggle="tab" data-bs-target="#nav-leaderboard" aria-controls="nav-leaderboard" aria-selected="false">Leaderboard
          </button>
        </div>
      </nav>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="nav-matches" role="tabpanel" aria-labelledby="nav-matches-tab" tabindex="0">
          <table v-if="!isLoading.fixtures" class="table table-sm">
            <thead>
            <tr>
              <th scope="col">Match</th>
              <th scope="col">Prediction</th>
              <th scope="col">Score</th>
              <th scope="col">Points</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="fixture in fixtures">
              <td class="text-start">
                <span>
                  <TeamLogo :teamName="fixture.homeTeam.name"></TeamLogo>
                  {{ fixture.homeTeam.name }}
                </span>
                <br>
                <span>
                  <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
                  {{ fixture.awayTeam.name }}
                </span>
              </td>
              <td>
                {{ predictionHomeScore(fixture) }}
                <br>
                {{ predictionAwayScore(fixture) }}
              </td>
              <td>
                {{ fixture.homeScore === null ? '-' : fixture.homeScore }}
                <br>
                {{ fixture.awayScore === null ? '-' : fixture.awayScore }}
              </td>
              <td>
                {{ fixture?.fixturePredictions[0]?.points ?? '-' }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="tab-pane fade" id="nav-leaderboard" role="tabpanel" aria-labelledby="nav-leaderboard-tab" tabindex="0">
          <table v-if="!isLoading.leaderboard" class="table table-sm">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Period Points</th>
              <th scope="col">Total Points</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(user, index) in leaderboard">
              <th scope="row">{{ index + 1 }}</th>
              <td>{{ user[0].displayName }}</td>
              <td>{{ user.periodPoints ?? '-' }}</td>
              <td>{{ user.totalPoints ?? '-' }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Predictions Modal -->
    <div
      class="modal fade"
      id="predictionsModal"
      tabindex="-1"
      data-bs-backdrop="static" data-bs-keyboard="false"
      aria-labelledby="predictionsModalLabel"
      aria-hidden="true"
    >
      <form @submit.prevent="makePredictions">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="predictionsModalLabel">Predictions</h1>
          </div>
          <div class="modal-body">
              <table v-if="!isLoading.fixtures" class="table">
                <thead>
                <tr>
                  <th scope="col">Match</th>
                  <th scope="col" style="width: 20%; min-width: 80px;">Home</th>
                  <th scope="col" style="width: 20%; min-width: 80px;">Away</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="fixture in fixtures">
                  <tr v-if="new Date(fixture.startAt) > new Date()">
                    <td class="text-start">
                      <span>
                        <TeamLogo :teamName="fixture.homeTeam.name"></TeamLogo>
                      {{ fixture.homeTeam.name }}
                      </span>
                      <br>
                      <span>
                        <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
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
                             :value="predictionHomeScore(fixture)">
                    </td>
                    <td>
                      <input class="form-control"
                             type="number"
                             min="0" max="99"
                             :name="'away-fixture-prediction-'+fixture.id"
                             :data-id="fixture.id"
                             data-side="away"
                             :value="predictionAwayScore(fixture)">
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closeModal" data-bs-dismiss="modal">Close</button>
            <button :disabled="isLoading.predictions" type="submit" class="btn btn-primary">Save</button>
            <div v-if="isLoading.predictions" class="spinner-border text-primary mt-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
      </form>
    </div>
  </div>
</template>

<style scoped></style>