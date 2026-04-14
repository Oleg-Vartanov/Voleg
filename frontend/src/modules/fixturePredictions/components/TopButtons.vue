<script setup lang="ts">
defineProps<{
  disablePredictions: boolean
}>()
</script>

<template>
  <div class="d-flex justify-content-center mb-3">
    <div class="btn-group me-2" role="group" aria-label="Top buttons">
      <button
        class="btn btn-outline-primary"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasFilters"
        aria-controls="offcanvasFilters"
      >
        <i class="bi bi-funnel"></i>
        Filters
      </button>

      <button
        class="btn btn-outline-primary"
        data-bs-toggle="modal"
        data-bs-target="#predictionsModal"
        :disabled="disablePredictions"
      >
        <i class="bi bi-magic"></i>
        Predict
      </button>
    </div>
    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#aboutModal">
      <i class="bi bi-question-lg"></i>
    </button>
  </div>
</template>
