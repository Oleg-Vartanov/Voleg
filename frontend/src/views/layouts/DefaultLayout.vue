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
  background-color: #d9d8d8;
}

.main-container {
  margin: 80px 10px 132px;
}
</style>
