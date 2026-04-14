<script setup lang="ts">
import NavBar from '@/modules/core/components/NavBar.vue'
import TopAlerts from '@/modules/core/components/TopAlerts.vue'
</script>

<template>
  <div class="main-container">
    <NavBar />
    <TopAlerts />
    <router-view />
  </div>
</template>
