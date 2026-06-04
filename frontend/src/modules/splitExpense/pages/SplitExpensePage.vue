<script setup lang="ts">
import HeaderNavigation from '@/modules/splitExpense/components/HeaderNavigation.vue'
</script>

<template>
  <HeaderNavigation />

  <div class="ov-center">
    <div class="container d-flex flex-column gap-3">
      <router-view />
    </div>
  </div>
</template>
