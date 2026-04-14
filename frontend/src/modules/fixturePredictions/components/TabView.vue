<script setup lang="ts">
import { type TablesEnum } from '@/modules/fixturePredictions/composables/useTables'

withDefaults(
  defineProps<{
    table: TablesEnum
    active?: boolean
  }>(),
  {
    active: false
  }
)
</script>

<template>
  <div
    :id="'nav-' + table"
    class="tab-pane fade"
    :class="{ 'show active': active }"
    role="tabpanel"
    :aria-labelledby="'nav-' + table + '-tab'"
    tabindex="0"
  >
    <slot />
  </div>
</template>
