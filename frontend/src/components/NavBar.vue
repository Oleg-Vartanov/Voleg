<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue';
import {useRoute} from 'vue-router'

let centerRoutes: { name: string, title: string }[] = [
  {name: 'home', title: 'Home'},
  {name: 'about', title: 'About'},
  {name: 'pricing', title: 'Pricing'},
];

const route = useRoute();

function routerLinkClass(routeName: string) {
  return routeName === route.name ? 'active text-white' : '';
}
</script>

<template>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">

      <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse d-lg-flex" id="navbarNav">

        <a class="navbar-brand col-lg-3 me-0" href="#">
          <img src="/vue-logo.svg" width="35" height="25" alt="logo">
        </a>

        <ul class="navbar-nav col-lg-6 justify-content-lg-center nav-pills">
          <li v-for="route in centerRoutes" class="nav-item">
            <router-link
                class="nav-link"
                :to="{ name: route.name }"
                :class="routerLinkClass(route.name)"
            >
              {{ route.title }}
            </router-link>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Action</a></li>
              <li><a class="dropdown-item" href="#">Another action</a></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
          </li>
        </ul>

        <div class="d-lg-flex col-lg-3 justify-content-lg-end">
          <ul class="navbar-nav nav-pills">
            <li class="nav-item nav-link">
              <ColorThemeToggle></ColorThemeToggle>
            </li>
            <li class="nav-item">
              <router-link
                  class="nav-link"
                  :to="{ name: 'signIn' }"
                  :class="routerLinkClass('signIn')"
              >
                Sign In
              </router-link>
            </li>
          </ul>
<!--          <button class="btn btn-primary">Button</button>-->
        </div>

      </div>
    </div>
  </nav>
</template>