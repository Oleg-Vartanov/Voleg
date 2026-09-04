<script setup lang="ts">
import { computed, inject } from 'vue'
import arrayUtils from '@/modules/core/utils/arrayUtils'
import AppModal from '@/modules/core/components/AppModal.vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import type { FormSelectOption } from '@/modules/core/components/form/types'
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { CompetitionCode, CompetitionNames } from '@/modules/fixturePredictions/enum'

const open = defineModel<boolean>('open', { required: true })

const tables = inject<Tables>('tables')!
const filters = inject<FixtureFilters>('filters')!

const start = computed({
  get: () => filters.start.value ?? '',
  set: (value: string) => {
    filters.start.value = value || null
  }
})

const end = computed({
  get: () => filters.end.value ?? '',
  set: (value: string) => {
    filters.end.value = value || null
  }
})

const competitionOptions: FormSelectOption[] = Object.values(CompetitionCode).map((code) => ({
  value: code,
  label: CompetitionNames[code]
}))

const seasonOptions = computed<FormSelectOption[]>(() =>
  arrayUtils.range(2023, 2100).map((year) => ({
    value: year,
    label: String(year)
  }))
)

function applyFilters(close: () => void) {
  tables.updateLoadedTables()
  close()
}
</script>

<template>
  <AppModal v-model:open="open" title="Filters">
    <div class="modal-body">
      <FormInput id="filter-start" v-model="start" type="date" label="Start" :required="false" />
      <FormInput id="filter-end" v-model="end" type="date" label="End" :required="false" />
      <FormSelect
        id="filter-competition"
        v-model="filters.competition.value"
        label="Competition"
        :options="competitionOptions"
      />
      <FormSelect
        id="filter-season"
        v-model="filters.season.value"
        label="Season"
        :options="seasonOptions"
        :required="false"
      />
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" @click="close">Close</button>
      <button
        class="btn btn-primary"
        type="button"
        :disabled="tables.isLoading.value.fixtures || tables.isLoading.value.leaderboard"
        @click="applyFilters(close)"
      >
        <i class="bi bi-funnel"></i>
        Filter
      </button>
    </template>
  </AppModal>
</template>
