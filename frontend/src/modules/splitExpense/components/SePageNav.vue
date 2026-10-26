<script setup lang="ts">
const items = [
  { name: 'seFeed', title: 'Feed' },
  { name: 'seBalance', title: 'Balance' },
  { name: 'seInsights', title: 'Insights' },
  { name: 'seConnections', title: 'Connections' }
] as const
</script>

<template>
  <nav class="se-page-nav" aria-label="Split Expense">
    <ul class="nav nav-tabs justify-content-center">
      <li v-for="item in items" :key="item.name" class="nav-item">
        <router-link class="nav-link" :to="{ name: item.name }" active-class="active">
          {{ item.title }}
        </router-link>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.se-page-nav {
  --bs-nav-link-font-size: var(--ov-font-size-md);
  --bs-nav-link-padding-x: 0.75rem;
  width: 100%;
}

@media (max-width: 399.98px) {
  .se-page-nav {
    --bs-nav-link-font-size: var(--ov-font-size-sm);
    --bs-nav-link-padding-x: 0.4rem;
  }
}
</style>
