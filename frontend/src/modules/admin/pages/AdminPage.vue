<script setup lang="ts">
import { ref } from 'vue'
import Client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import arrayUtils from '@/modules/core/utils/arrayUtils'
import dateUtils from '@/modules/core/utils/dateUtils'
import { CompetitionCode, CompetitionNames } from '@/modules/fixturePredictions/enum'

const topAlerts = useTopAlerts()
const isLoading = ref(false)

const today = new Date()
today.setHours(0, 0, 0, 0)
const dayStart = dateUtils.format(today)
today.setHours(23, 59, 59, 999)
const dayEnd = dateUtils.format(today)
const timezone = dateUtils.getTimezone(today)

const competition = ref(CompetitionCode.PL)
const season = ref(new Date().getFullYear())
const start = ref(dayStart)
const end = ref(dayEnd)

const sync = () => {
  isLoading.value = true

  Client.syncFixtures(
    competition.value,
    season.value,
    start.value + ' ' + timezone,
    end.value + ' ' + timezone
  )
    .then(() => {
      topAlerts.add('Fixtures were synced.', 'success')
    })
    .catch((axiosError) => {
      switch (axiosError.response.status) {
        case 403:
          topAlerts.add('Forbidden action.', 'danger')
          break
        case 401:
          topAlerts.add('Unauthorized.', 'danger')
          break
        default:
          topAlerts.add('Unable to sync. Try again later. Or contact support.', 'danger')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <div class="ov-center" />

  <div class="ov-center">
    <div class="form w-100 m-auto">
      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Competition</span>
        <select v-model="competition" class="form-select" aria-label="Default select example">
          <option :value="CompetitionCode.PL">
            {{ CompetitionNames[CompetitionCode.PL] }}
          </option>
        </select>
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Season</span>
        <select v-model="season" class="form-select">
          <option v-for="year in arrayUtils.range(2023, 2100)" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">Start</span>
        <input
          id="start"
          v-model="start"
          class="form-control filter-date-input"
          type="datetime-local"
        />
      </div>

      <div class="input-group mb-2 w-100">
        <span class="input-group-text filter-date-text">End</span>
        <input
          id="end"
          v-model="end"
          class="form-control filter-date-input"
          type="datetime-local"
        />
      </div>

      <button
        :disabled="isLoading"
        type="button"
        class="btn btn-outline-primary py-2 mb-2 w-100"
        @click="sync"
      >
        Sync Matches
      </button>
      <br />
      <div v-if="isLoading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <i class="text-secondary">
        * The current fixtures provider converts datetime to UTC and then uses only the date,
        ignoring the time.
      </i>
      &#128533;<br />
      <i class="text-secondary">
        * The end date fixtures are not included in the result, so it's "until then".
      </i>
      &#128534;

      <router-view />
    </div>
  </div>
</template>

<style>
html,
body {
  height: 100%;
}

.form {
  max-width: 330px;
  padding: 1rem;
}
</style>
