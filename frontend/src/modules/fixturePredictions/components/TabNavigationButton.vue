<script setup lang="ts">
import { type Tables, type TablesEnum } from '@/modules/fixturePredictions/composables/useTables'
import { inject } from 'vue'

withDefaults(
  defineProps<{
    table: TablesEnum
    text: string
    active?: boolean
  }>(),
  {
    active: false
  }
)

const tables: Tables = inject('tables')
</script>

<template>
  <button
    :id="'nav-' + table + '-tab'"
    class="nav-link"
    :class="{ active: active }"
    role="tab"
    data-bs-toggle="tab"
    :data-bs-target="'#nav-' + table"
    :aria-controls="'nav-' + table"
    aria-selected="true"
    @click="tables.initTable(table)"
  >
    {{ text }}
  </button>
</template>
