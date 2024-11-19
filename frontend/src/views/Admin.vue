<script setup lang="ts">
import { ref } from 'vue';
import Client from '@/modules/api-client.ts';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";

const topAlerts = useTopAlerts();
const isLoading = ref(false);

const sync = () => {
  isLoading.value = true;

  Client.syncFixtures('PL', 2024)
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
    <button :disabled="isLoading" v-on:click="sync" type="button" class="btn btn-primary py-2 mb-3">Sync Matches</button>
    <br>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
</template>