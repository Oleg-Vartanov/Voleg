<script setup lang="ts">
import HeaderNavigation from '@/modules/splitExpense/components/HeaderNavigation.vue'
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column gap-2">
      <HeaderNavigation />
      <router-view />
    </div>
  </div>
</template>
