<script setup lang="ts">
let routes: { name: string, title: string }[] = [
  {name: 'home', title: 'Home'},
  {name: 'about', title: 'About'},
  {name: 'signIn', title: 'Sign In'}
];
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto d-flex">
          <li v-for="route in routes" class="nav-item">
            <router-link
              class="nav-link"
              :to="{ name: route.name }"
              :class="this.$route.name === route.name ? 'text-white' : 'link-success'"
            >
              {{ route.title }}
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>