<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";
import ArrayHelper from "@/helpers/array-helper";
import DateHelper from '@/helpers/date-helper';

const topAlerts = useTopAlerts();
const isLoading = ref(false);

const today = (new Date());
today.setHours(0, 0, 0, 0);
const dayStart = DateHelper.format(today);
today.setHours(23, 59, 59, 999);
const dayEnd = DateHelper.format(today);

const competition = ref('PL');
const season = ref(2025);
const start = ref(dayStart);
const end = ref(dayEnd);

const sync = () => {
  isLoading.value = true;

  Client.syncFixtures(competition.value, season.value , start.value, end.value)
    .then(() => {
      topAlerts.add(new Alert('Fixtures were synced.', 'success', 10));
    })
    .catch((axiosError) => {
      switch (axiosError.response.status) {
        case 403:
          topAlerts.add(new Alert('Forbidden action.', 'danger', 10));
          break;
        case 401:
          topAlerts.add(new Alert('Unauthorized.', 'danger', 10));
          break;
        default:
          topAlerts.add(new Alert('Unable to sync. Try again later. Or contact support.', 'danger', 10));
      }
    })
    .finally(() => {
      isLoading.value = false;
    })
}
</script>

<template>
  <div class="ov-center">

  </div>

  <div class="ov-center">
    <div class="form w-100 m-auto">

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Competition</span>
        <select class="form-select" aria-label="Default select example" v-model="competition">
          <option value="PL">English Premier League</option>
        </select>
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Season</span>
        <select class="form-select" v-model="season">
          <option v-for="year in ArrayHelper.range(2023, 2100)" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Start</span>
        <input id="start" class="form-control filter-date-input" type="datetime-local" v-model="start"/>
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">End</span>
        <input id="end" class="form-control filter-date-input" type="datetime-local" v-model="end"/>
      </div>

      <button :disabled="isLoading" v-on:click="sync" type="button" class="btn btn-outline-primary py-2 mb-3 w-100">Sync Matches</button>
      <br>
      <div v-if="isLoading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>

      <router-view></router-view>

    </div>
  </div>
</template>

<style>
html, body {
  height: 100%;
}

.form {
  max-width: 330px;
  padding: 1rem;
}
</style>