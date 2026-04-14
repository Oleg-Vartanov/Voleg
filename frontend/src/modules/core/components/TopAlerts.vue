<script setup lang="ts">
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'

const module = useTopAlerts()
</script>

<template>
  <TransitionGroup name="list" tag="div">
    <div v-for="alert in module.alerts" :key="alert.id">
      <div
        :class="['alert-' + alert.type]"
        class="alert alert-dismissible d-flex justify-content-between align-items-center mt-1 mb-0"
        role="alert"
      >
        {{ alert.text }}

        <div class="d-flex">
          <div v-if="alert.countdown > 0" class="d-flex align-items-center">
            <span class="me-2">{{ alert.countdown }}</span>
            <div
              :class="['text-' + alert.type]"
              class="spinner-border spinner-border-sm"
              role="status"
            />
          </div>

          <button
            type="button"
            class="btn-close"
            aria-label="Close"
            @click="module.remove(alert)"
          />
        </div>
      </div>
    </div>
  </TransitionGroup>
</template>

<style>
.alert {
  padding: 5px;
}

.alert-dismissible .btn-close {
  padding: 10px;
  position: relative;
}
</style>
