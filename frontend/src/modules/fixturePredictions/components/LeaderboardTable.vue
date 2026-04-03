<script setup lang="ts">
import { type Tables } from '@/modules/fixturePredictions/composables/useTables';
import { inject } from 'vue';

const tables: Tables = inject('tables');
</script>

<template>
  <table v-if="!tables.isLoading.value.leaderboard" class="table table-sm">
    <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Period Points</th>
      <th scope="col">Total Points</th>
    </tr>
    </thead>
    <tbody>

    <!-- No results -->
    <tr v-if="tables.leaderboard.value?.length === 0">
      <td colspan="6" class="text-center py-3 text-muted">
        No results found
      </td>
    </tr>

    <!-- Leaderboard -->
    <tr v-for="(user, index) in tables.leaderboard.value" :key="user.user.id">
      <th scope="row">{{ index + 1 }}</th>
      <td>{{ user.user.displayName }}</td>
      <td>{{ user.periodPoints ?? '-' }}</td>
      <td>{{ user.totalPoints ?? '-' }}</td>
    </tr>
    </tbody>
  </table>
</template>

<style scoped>
.table-sm {
  margin-bottom: 0;
}

.text-muted {
  opacity: 0.9;
}
</style>