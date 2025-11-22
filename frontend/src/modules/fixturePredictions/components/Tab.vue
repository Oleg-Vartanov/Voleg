<script setup lang="ts">
import type { TablesEnum } from '@/modules/fixturePredictions/composables/useTables';

const props = withDefaults(defineProps<{
  table: TablesEnum;
  active?: boolean;
}>(), {
  active: false,
});
</script>

<template>
  <div class="tab-pane fade"
       :class="{'show active': active}"
       :id="'nav-'+table"
       role="tabpanel"
       :aria-labelledby="'nav-'+table+'-tab'"
       tabindex="0"
  >
    <slot/>
  </div>
</template>
