<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";

const topAlerts = useTopAlerts();

const start = ref(null);
const end = ref(null);

const fixtures = ref({});
const isLoadingFixtures = ref(true);

const leaderboard = ref({});
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

</script>

<template>
  <div class="ov-center">
    <div class="container text-center">

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

      <div v-if="isLoadingFixtures || isLoadingLeaderboard" class="spinner-border text-primary mt-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>

      <div class="row align-items-start">
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
              <td>
                {{ fixture.homeTeam.name }}
                <br>
                {{ fixture.awayTeam.name }}
              </td>
              <td>
                {{ fixture?.fixturePredictions?.homeScore == null ? '-' : fixture.fixturePredictions.homeScore }}
                <br>
                {{ fixture?.fixturePredictions?.awayScore == null ? '-' : fixture.fixturePredictions.awayScore }}
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
  </div>
</template>