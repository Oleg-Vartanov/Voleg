<script setup lang="ts">
import { useTopAlerts } from "@/modules/top-alerts";

const module = useTopAlerts();
</script>

<template>
  <!-- TODO: Fix alert properties typehint. -->
  <div v-for="alert in module.alerts" :key="alert.id">
    <div
      :class="['alert-' + alert.type]"
      class="alert alert-dismissible fade show d-flex justify-content-between"
      role="alert"
    >
      {{ alert.text }}

      <div v-if="alert.countdown > 0" class="d-flex align-items-center ms-auto">
        <span class="me-2">
          {{ alert.countdown }}
        </span>
        <div :class="['text-' + alert.type]" class="spinner-border spinner-border-sm me-2" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <button @click="module.remove(alert)" type="button" class="btn-close" aria-label="Close"></button>
    </div>
  </div>
</template>