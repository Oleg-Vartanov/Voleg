<script setup lang="ts">
import NavBar from '@/modules/core/components/NavBar.vue';
import TopAlerts from '@/modules/core/components/TopAlerts.vue';
</script>

<template>
  <div class="main-container">
    <NavBar></NavBar>
    <TopAlerts></TopAlerts>
    <router-view></router-view>
  </div>
</template>

<!--<style>-->
<!--body {-->
<!--  background-color: #b7ccb7;-->
<!--}-->
<!--</style>-->
