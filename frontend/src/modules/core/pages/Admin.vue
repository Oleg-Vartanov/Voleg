<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/core/apiClient';
import { useTopAlerts } from '@/modules/core/topAlerts';
import { Alert } from '@/models/alert';
import arrayUtils from '@/modules/core/utils/arrayUtils';
import dateUtils from '@/modules/core/utils/dateUtils';

const topAlerts = useTopAlerts();
const isLoading = ref(false);

const today = (new Date());
today.setHours(0, 0, 0, 0);
const dayStart = dateUtils.format(today);
today.setHours(23, 59, 59, 999);
const dayEnd = dateUtils.format(today);
const timezone = dateUtils.getTimezone(today);

const competition = ref('PL');
const season = ref(new Date().getFullYear());
const start = ref(dayStart);
const end = ref(dayEnd);

const sync = () => {
  isLoading.value = true;

  Client.syncFixtures(
    competition.value,
    season.value,
    start.value + ' ' + timezone,
    end.value + ' ' + timezone,
  )
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
    });
};
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
          <option v-for="year in arrayUtils.range(2023, 2100)" :key="year" :value="year">
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

      <button :disabled="isLoading" v-on:click="sync" type="button" class="btn btn-outline-primary py-2 mb-2 w-100">Sync
        Matches
      </button>
      <br>
      <div v-if="isLoading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <i class="text-secondary">* The current fixtures provider converts datetime to UTC and then uses only the date,
        ignoring the time.</i> &#128533;<br>
      <i class="text-secondary">* The end date fixtures are not included in the result, so it's "until then".</i>
      &#128534;

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