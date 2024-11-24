<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";
import TeamLogo from "@/components/games/fooball-predictions/TeamLogo.vue";

const topAlerts = useTopAlerts();

const start = ref(null);
const end = ref(null);

const fixtures = ref({});
const leaderboard = ref({});
const isLoadingFixtures = ref(true);
const isLoadingLeaderboard = ref(true);

function updateFixturesTable() {
  isLoadingFixtures.value = true;
  isLoadingLeaderboard.value = true;

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
      isLoadingFixtures.value = false;
    })

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
      isLoadingLeaderboard.value = false;
    })
}
updateFixturesTable();

function predictionHomeScore(fixture) {
  return fixture?.fixturePredictions?.homeScore == null ? '-' : fixture.fixturePredictions.homeScore;
}
function predictionAwayScore(fixture) {
  return fixture?.fixturePredictions?.awayScore == null ? '-' : fixture.fixturePredictions.awayScore;
}
</script>

<template>
  <div class="ov-center">
    <div class="container">

      <div class="d-flex justify-content-center">
        <div class="row">

          <div class="col-auto mb-2 p-1">
            <button v-if="!isLoadingFixtures"
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
            <button @click="updateFixturesTable" class="btn btn-outline-primary" type="button">Filter</button>
          </div>
        </div>

      </div>

      <div v-if="isLoadingFixtures || isLoadingLeaderboard" class="spinner-border text-primary mt-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>

      <div class="row">
        <div class="col">
          <table v-if="!isLoadingFixtures" class="table">
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
                {{ fixture?.fixturePredictions?.points ?? '-' }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div class="col">
          <table v-if="!isLoadingLeaderboard" class="table">
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
              <td>{{ user.totalPoints ?? '-' }}</td>
              <td>{{ user.totalPoints ?? '-' }}</td>
            </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Modal -->
    <div
      class="modal fade"
      id="predictionsModal"
      tabindex="-1"
      data-bs-backdrop="static" data-bs-keyboard="false"
      aria-labelledby="predictionsModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="predictionsModalLabel">Predictions</h1>
          </div>
          <div class="modal-body">

            <table v-if="!isLoadingFixtures" class="table">
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
                    {{ fixture.status }}
                    </span>
                    <br>
                    <span>
                      <TeamLogo :teamName="fixture.awayTeam.name"></TeamLogo>
                    {{ fixture.awayTeam.name }}
                    </span>
                  </td>
                  <td>
                    <input type="number"
                           min="0" max="99"
                           class="form-control"
                           :value="predictionHomeScore(fixture)">
                  </td>
                  <td>
                    <input type="number"
                           min="0" max="99"
                           class="form-control"
                           :value="predictionAwayScore(fixture)">
                  </td>
                </tr>
              </template>
              </tbody>
            </table>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped></style>