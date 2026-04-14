<script setup lang="ts">
import arrayUtils from '@/modules/core/utils/arrayUtils';
import { inject } from 'vue';
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters';
import { type Tables } from '@/modules/fixturePredictions/composables/useTables';
import { CompetitionCode, CompetitionNames } from '@/modules/fixturePredictions/enum';

const tables: Tables = inject('tables');
const filters: FixtureFilters = inject('filters');
</script>

<template>
  <div
    id="offcanvasFilters"
    class="offcanvas offcanvas-start"
    tabindex="-1"
    aria-labelledby="offcanvas-filters-label"
  >
    <div class="offcanvas-header">
      <h5 id="offcanvas-filters-label" class="offcanvas-title">Filters</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

      <div class="d-flex justify-content-center">
        <div class="row">
          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">Start</span>
              <input id="start" v-model="filters.start.value" class="form-control filter-date-input" type="date"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">End</span>
              <input id="end" v-model="filters.end.value" class="form-control filter-date-input" type="date"/>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">Competition</span>
              <select v-model="filters.competition.value" class="form-select" aria-label="Default select example">
                <option :value="CompetitionCode.PL">{{ CompetitionNames[CompetitionCode.PL] }}</option>
              </select>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <div class="input-group">
              <span class="input-group-text filter-date-text">Season</span>
              <select v-model="filters.season.value" class="form-select">
                <option v-for="year in arrayUtils.range(2023, 2100)" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>

          <div class="col-auto mb-2 p-1">
            <button
              class="btn btn-primary"
              data-bs-toggle="modal"
              data-bs-target="#go"
            >
              <i class="bi bi-people-fill"></i>
              H2H
            </button>
          </div>

          <div class="col-auto mb-2 p-1">
            <button
              class="btn btn-outline-primary"
              type="button"
              :disabled="tables.isLoading.value.fixtures || tables.isLoading.value.leaderboard"
              data-bs-dismiss="offcanvas"
              @click="tables.updateLoadedTables"
            >
              <i class="bi bi-funnel"></i>
              Filter
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.filter-date-input {
  width: 125px;
  padding-left: 6px;
}

.filter-date-text {
  padding: 0 5px 0 5px;
  min-width: 45px;
  display: flex;
  justify-content: center;
}
</style>