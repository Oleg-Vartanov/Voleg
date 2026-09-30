<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'

export type BreadcrumbItem = {
  label: string
  /** When set, the crumb is a link; otherwise it is the current page. */
  to?: RouteLocationRaw
}

defineProps<{
  items: BreadcrumbItem[]
}>()
</script>

<template>
  <nav class="app-breadcrumb" aria-label="Breadcrumb">
    <ol class="breadcrumb mb-0">
      <li
        v-for="(item, index) in items"
        :key="`${item.label}-${index}`"
        class="breadcrumb-item"
        :class="{ active: !item.to }"
        :aria-current="item.to ? undefined : 'page'"
      >
        <router-link v-if="item.to" :to="item.to">{{ item.label }}</router-link>
        <template v-else>{{ item.label }}</template>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.app-breadcrumb {
  font-size: var(--ov-font-size-label);
}

.app-breadcrumb .breadcrumb {
  --bs-breadcrumb-item-padding-x: 0.65rem;
  align-items: center;
  min-height: 2.75rem;
  margin: 0;
}

.app-breadcrumb .breadcrumb-item a {
  display: inline-flex;
  align-items: center;
  min-height: 2.75rem;
  padding-inline: 0.15rem;
}

.app-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
  content: '\203a';
  padding-inline: 0.65rem;
}
</style>
