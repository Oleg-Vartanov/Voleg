<script setup lang="ts">
import { type TablesEnum, useTables } from '@/modules/fixturePredictions/composables/useTables';

const props = defineProps<{
  table: TablesEnum;
  text: string;
  active: boolean;
}>();

const tables = useTables();
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
