<script setup lang="ts">
import { type Tables, type TablesEnum } from '@/modules/fixturePredictions/composables/useTables';

const props = defineProps<{
  tables: Tables;
  table: TablesEnum;
  text: string;
  active: boolean;
}>();
</script>

<template>
  <button @click="tables.initTable(table)"
          class="nav-link"
          :class="{'active': active}"
          :id="'nav-'+table+'-tab'"
          role="tab"
          data-bs-toggle="tab"
          :data-bs-target="'#nav-'+table"
          :aria-controls="'nav-'+table"
          aria-selected="true"
  >
    {{ text }}
  </button>
</template>
