<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client';
import ArrayHelper from "@/helpers/array-helper";
import { useTopAlerts } from "@/modules/top-alerts";
import { useAuth } from "@/modules/auth";
import { Alert } from "@/models/alert";
import TeamLogo from "@/components/games/fooball-predictions/TeamLogo.vue";

const topAlerts = useTopAlerts();
const auth = useAuth();

const start = ref(null);
const end = ref(null);

const fixtures = ref(null);
const leaderboard = ref(null);

const searchUsers = ref([]);
const h2hUsers = ref([]);
const h2hInput = ref({ value: '', error: '' });

const isLoading = ref({ fixtures: false, leaderboard: false, predictions: false, headToHead: false });

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
  let userIds = h2hUsers.value.map(object => object.id);

  Client.showFixtures(start.value, end.value, userIds)
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

function searchUser() {
  isLoading.value.headToHead = true;

  Client.listUsers(h2hInput.value.value)
    .then((response) => {
      h2hInput.value.error = '';
      searchUsers.value = response.data;
    })
    .catch((axiosError) => {
      h2hInput.value.error = 'Unexpected error :(';
    })
    .finally(() => {
      isLoading.value.headToHead = false;
    })
}

function addH2hUser(user: object) {
  if (!h2hUsers.value.includes(user)) {
    h2hUsers.value.push(user);
    updateLoadedTables();
  }
}
function removeH2hUser(user: object) {
  ArrayHelper.removeItem(h2hUsers.value, user);
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

      const parsedValue = parseInt(element.value, 10);
      const score = isNaN(parsedValue) ? null : parsedValue;

      if (fixtureId === null || side === null) {
        continue;
      }

      if (!predictions[fixtureId]) {
        predictions[fixtureId] = {
          fixtureId: parseInt(fixtureId, 10),
          homeScore: null,
          awayScore: null,
        };
      }

      if (side === 'home') {
        predictions[fixtureId].homeScore = score;
      }
      if (side === 'away') {
        predictions[fixtureId].awayScore = score;
      }
    }
  }

  // Exclude not filled fixtures.
  Object.entries(predictions).forEach(([index, prediction]) => {
    if (prediction.homeScore === null || prediction.awayScore === null) {
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

function getPrediction(fixture, user = null) {
  const userId = user === null ? auth.user.id : user.id;

  return fixture?.fixturePredictions.find(prediction => prediction.user.id === userId);
}

function predictionHomeScore(prediction) {
  return prediction?.homeScore == null ? '-' : prediction.homeScore;
}
function predictionAwayScore(prediction) {
  return prediction?.awayScore == null ? '-' : prediction.awayScore;
}

function colorClass(prediction) {
  switch (prediction?.points) {
    case 3:
      return 'text-success';
    case 1:
      return 'text-warning';
    case 0:
      return 'text-danger';
    default:
      return '';
  }
}

function fixtureDate(fixture) {
  const date = new Date(fixture.startAt);

  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const hour = String(date.getHours()).padStart(2, '0');
  const minute = String(date.getMinutes()).padStart(2, '0');

  return { date: `${day}/${month}`, time: `${hour}:${minute}` };
}
</script>

<template>
  <div class="ov-center">
    <div class="container">

      <div class="d-flex justify-content-center">
        <div class="row">
          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">Start</span>
              <input id="start" class="form-control filter-date-input" type="date" v-model="start"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">End</span>
              <input id="end" class="form-control filter-date-input" type="date" v-model="end"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <button @click="updateLoadedTables"
                    class="btn btn-outline-primary"
                    type="button"
                    :disabled="isLoading.fixtures || isLoading.leaderboard"
            ><i class="bi bi-funnel"></i> Filter</button>
          </div>

          <div class="col-auto mb-2 p-1">
            <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#go"
            ><i class="bi bi-people-fill"></i> H2H</button>
          </div>

          <div class="col-auto mb-2 p-1">
            <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#predictionsModal"
                    :disabled="isLoading.fixtures || isLoading.predictions"
            ><i class="bi bi-magic"></i> Predict</button>
          </div>
        </div>

      </div>

      <!-- Nav -->
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

      <!-- Nav Tabs -->
      <div class="tab-content">

        <div v-if="isLoading.fixtures || isLoading.leaderboard" class="spinner-border text-primary mt-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>

        <!-- Matches -->
        <div class="tab-pane fade show active" id="nav-matches" role="tabpanel" aria-labelledby="nav-matches-tab" tabindex="0">
          <table v-if="!isLoading.fixtures" class="table table-sm">
            <thead>
            <tr>
              <th scope="col">Match</th>
              <th scope="col">Score</th>
              <th scope="col">{{ h2hUsers.length === 0 ? 'Prediction' : auth.user.displayName }}</th>
              <th scope="col" v-if="h2hUsers.length === 0">Points</th>
              <th scope="col" v-for="h2hUser in h2hUsers">{{ h2hUser.displayName }}</th>
              <th scope="col">Start</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="fixture in fixtures">
              <template v-for="prediction of [getPrediction(fixture)]">
                <td class="text-start">
                  <span>
                    <TeamLogo :teamName="fixture.homeTeam.name"></TeamLogo>
                    {{ fixture.homeTeam.name }}
                  </span><br>
                  <span>
                    <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
                    {{ fixture.awayTeam.name }}
                  </span>
                </td>
                <td>
                  {{ fixture.homeScore === null ? '-' : fixture.homeScore }}<br>
                  {{ fixture.awayScore === null ? '-' : fixture.awayScore }}
                </td>
                <td :class="colorClass(prediction)">
                  {{ predictionHomeScore(prediction) }}<br>
                  {{ predictionAwayScore(prediction) }}
                </td>
                <template v-for="h2hUser in h2hUsers">
                  <template v-for="h2hUserPrediction of [getPrediction(fixture, h2hUser)]">
                    <td :class="colorClass(h2hUserPrediction)">
                      {{ predictionHomeScore(h2hUserPrediction) }}<br>
                      {{ predictionAwayScore(h2hUserPrediction) }}
                    </td>
                  </template>
                </template>
                <td v-if="h2hUsers.length === 0">
                  {{ prediction?.points ?? '-' }}
                </td>
                <td v-for="{date, time} of [fixtureDate(fixture)]">
                  {{ time }}<br>{{ date }}
                </td>
              </template>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Leaderboard -->
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
      data-bs-backdrop="static"
      aria-labelledby="predictionsModalLabel"
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
                <template v-for="prediction of [getPrediction(fixture)]">
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
                             :value="predictionHomeScore(prediction)">
                    </td>
                    <td>
                      <input class="form-control"
                             type="number"
                             min="0" max="99"
                             :name="'away-fixture-prediction-'+fixture.id"
                             :data-id="fixture.id"
                             data-side="away"
                             :value="predictionAwayScore(prediction)">
                    </td>
                  </tr>
                </template>
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

    <!-- H2H Modal -->
    <div
      class="modal fade"
      id="go"
      tabindex="-1"
      aria-labelledby="h2hModalLabel"
      data-bs-backdrop="static"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="h2hModalLabel">Head To Head</h1>
          </div>
          <div class="modal-body">

            <ul class="list-group list-group-flush mb-3">
              <li v-for="user in h2hUsers" class="h2h-user list-group-item list-group-item-action" @click="removeH2hUser(user)">
                {{ user.displayName }} <i class="bi bi-x-lg text-danger" style="font-size: 20px;"></i>
              </li>
            </ul>

            <div class="input-group mb-3 has-validation">
              <span class="input-group-text rounded-0" id="addon-wrapping">Name</span>
              <input v-model="h2hInput.value"
                     type="text"
                     :class="[h2hInput.error === '' ? '' : 'is-invalid']"
                     class="form-control"
                     aria-describedby="go-h2h validation-go-h2h"
                     @keyup.enter="h2hInput.value === '' || isLoading.headToHead ? '' : searchUser()">
              <button @click="searchUser"
                      :disabled="h2hInput.value === '' || isLoading.headToHead"
                      class="btn btn btn-outline-primary rounded-0"
                      type="button"
                      id="go-h2h">Search</button>
              <div id="validation-go-h2h" class="invalid-feedback">{{ h2hInput.error }}</div>
            </div>

            <div v-if="isLoading.headToHead" class="spinner-border text-primary mt-3" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>

            <ul class="list-group list-group-flush">
              <li v-for="user in searchUsers" class="h2h-user list-group-item list-group-item-action" @click="addH2hUser(user)">
                {{ user.displayName }} <i class="bi bi-person-plus text-primary" style="font-size: 20px;"></i>
              </li>
            </ul>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closeModal" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.h2h-user {
  cursor: pointer;
}

.filter-date-input {
  width: 125px;
  padding-left: 6px;
}
.filter-date-text {
  padding: 0 5px 0 5px;
  min-width: 45px;
  display: flex; justify-content: center;
}
</style>