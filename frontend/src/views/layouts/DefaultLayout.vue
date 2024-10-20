<script setup lang="ts">
import NavBar from '../../components/NavBar.vue';
</script>

<template>
  <NavBar></NavBar>
  <div class="main-container">
    <router-view></router-view>
  </div>
</template>

<style>
body {
  background-color: #b7ccb7;
}
</style>
