<script setup lang="ts">
import NavBar from '../../components/NavBar.vue';
</script>

<template>
  <div class="main-container">
    <NavBar></NavBar>
    <router-view></router-view>
  </div>
</template>

<style>
body {
  background-color: #b7ccb7;
}
</style>
