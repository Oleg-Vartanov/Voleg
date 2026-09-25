<script setup lang="ts">
import { inject, watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import PagePagination from '@/modules/core/components/pagination/PagePagination.vue'
import TeamLogo from '@/modules/fixturePredictions/components/TeamLogo.vue'
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { usePredictionsForm } from '@/modules/fixturePredictions/composables/usePredictionsForm'

const open = defineModel<boolean>('open', { required: true })

const filters = inject<FixtureFilters>('filters')!
const tables = inject<Tables>('tables')!
const form = usePredictionsForm(filters, tables)

const pageSizeOptions = [10, 25, 50]

watch(open, (isOpen) => {
  if (isOpen) form.init()
})

async function save() {
  await form.save()
  open.value = false
}
</script>

<template>
  <AppModal v-model:open="open" title="Predictions" static-backdrop>
    <div class="modal-body">
      <div v-if="form.isLoading.value" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <p v-else-if="form.fixtures.value.length === 0" class="text-center text-body-secondary mb-0">
        No upcoming fixtures in the selected period.
      </p>
      <form v-else id="makePredictionsForm" @submit.prevent="save">
        <table class="table predictions-table">
          <thead>
            <tr>
              <th scope="col">Match</th>
              <th scope="col" class="predictions-col-score">Home</th>
              <th scope="col" class="predictions-col-score">Away</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="fixture in form.fixtures.value" :key="fixture.id">
              <td class="text-start predictions-match">
                <span class="predictions-team">
                  <TeamLogo :team-name="fixture.homeTeam.name" />
                  {{ fixture.homeTeam.name }}
                </span>
                <br />
                <span class="predictions-team">
                  <TeamLogo :team-name="fixture.awayTeam.name" />
                  {{ fixture.awayTeam.name }}
                </span>
              </td>
              <td class="predictions-score-cell">
                <input
                  v-model="form.drafts.value[fixture.id].homeScore"
                  class="form-control"
                  type="number"
                  min="0"
                  max="99"
                  :name="'home-fixture-prediction-' + fixture.id"
                />
              </td>
              <td class="predictions-score-cell">
                <input
                  v-model="form.drafts.value[fixture.id].awayScore"
                  class="form-control"
                  type="number"
                  min="0"
                  max="99"
                  :name="'away-fixture-prediction-' + fixture.id"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </form>

      <PagePagination
        v-if="!form.isLoading.value && form.pagination.totalItems.value > pageSizeOptions[0]"
        :page-index="form.pagination.pageIndex.value"
        :page-size="form.pagination.pageSize.value"
        :page-size-options="pageSizeOptions"
        :total-pages="form.pagination.totalPages.value"
        aria-label="Predictions pagination"
        @update:page-index="form.setPage"
        @update:page-size="form.setPageSize"
      />
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" @click="close">Close</button>
      <button
        :disabled="form.isSaving.value || form.isLoading.value || form.fixtures.value.length === 0"
        type="submit"
        form="makePredictionsForm"
        class="btn btn-primary"
      >
        Save
      </button>
      <div v-if="form.isSaving.value" class="spinner-border text-primary mt-3" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </template>
  </AppModal>
</template>

<style scoped>
.predictions-table {
  font-size: var(--ov-font-size-sm);
}

.predictions-table thead th {
  font-weight: 600;
  color: var(--bs-secondary-color);
  text-align: center;
  vertical-align: bottom;
  border-bottom-color: var(--bs-primary);
}

.predictions-col-score {
  width: 20%;
  min-width: 80px;
}

.predictions-score-cell {
  vertical-align: middle;
  text-align: center;
}

.predictions-score-cell .form-control {
  display: inline-block;
  width: 3.25rem;
  margin-inline: auto;
  text-align: center;
  padding-inline: 0.35rem;
}

.predictions-match {
  font-size: var(--ov-font-size-sm);
}

.predictions-team {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  white-space: nowrap;
}
</style>
