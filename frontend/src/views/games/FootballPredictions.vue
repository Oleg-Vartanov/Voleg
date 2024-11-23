<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";

const topAlerts = useTopAlerts();
const isLoading = ref(true);
const fixtures = ref({});
const start = ref(null);
const end = ref(null);

function updateFixturesTable() {
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
      isLoading.value = false;
    })
}
updateFixturesTable();

function predictionHomeScore(fixture: object|null) {
  return fixture?.fixturePredictions?.homeScore == null ? '-' : fixture.fixturePredictions.homeScore;
}
function predictionAwayScore(fixture: object|null) {
  return fixture?.fixturePredictions?.awayScore == null ? '-' : fixture.fixturePredictions.awayScore;
}
function getHomeScore(fixture: object|null) {
  return fixture.homeScore === null ? '-' : fixture.homeScore;
}
function getAwayScore(fixture: object|null) {
  return fixture?.fixturePredictions?.awayScore == null ? '-' : fixture.fixturePredictions.awayScore;
}

// TODO: Move this calculation to a backend fixtures endpoint.
function calcPoints(fixture: object|null) {
  const pHomeScore = predictionHomeScore(fixture);
  const homeScore = getHomeScore(fixture);
  const pAwayScore = predictionAwayScore(fixture);
  const awayScore = getAwayScore(fixture);

  if (pHomeScore === '-') {
    return '-';
  }
  if (pHomeScore === homeScore(fixture) && pAwayScore === awayScore(fixture)) {
    return 3;
  }
  if (
    (pHomeScore > pAwayScore && homeScore > awayScore)
    || (pHomeScore < pAwayScore && homeScore < awayScore)
    || (pHomeScore === pAwayScore && homeScore === awayScore)
  ) {
    return 1;
  } else {
    return 0;
  }
}

</script>

<template>
  <div class="ov-center">
    <div class="container text-center">

      <div v-if="isLoading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>

      <div v-if="!isLoading" class="row align-items-start">

        <div class="d-flex justify-content-center">
          <div class="row align-items-center">
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

        <div class="col">
          <table class="table">
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
              <td>{{ fixture.homeTeam.name }}<br>{{ fixture.awayTeam.name }}</td>
              <td>{{ predictionHomeScore(fixture) }}<br>{{ predictionAwayScore(fixture) }}</td>
              <td>{{ getHomeScore(fixture) }}<br>{{ getAwayScore(fixture) }}</td>
              <td>{{ calcPoints(fixture) }}</td>
            </tr>
            </tbody>
          </table>
        </div>

        <div class="col">
          <table class="table">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Name</th>
              <th scope="col">Round Points</th>
              <th scope="col">Total Points</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <th scope="row">1</th>
              <td>Ole</td>
              <td>5</td>
              <td>13</td>
            </tr>
            <tr>
              <td>2</td>
              <td>Arti</td>
              <td>2</td>
              <td>25</td>
            </tr>
            </tbody>
          </table>
        </div>
        
      </div>
      
    </div>
  </div>
</template>