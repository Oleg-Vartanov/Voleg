<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import TopAlerts from '@/components/TopAlerts.vue';
import {onMounted} from "vue";
import * as bootstrap from "bootstrap";

onMounted(() => {
  const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
  const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
});
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
