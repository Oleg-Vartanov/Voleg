<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue';
import {useRoute} from 'vue-router'
import {useAuth} from "@/modules/auth";

let centerRoutes: { name: string, title: string }[] = [
  {name: 'about', title: 'About'},
  {name: 'games', title: 'Games'},
  {name: 'pricing', title: 'Pricing'},
  {name: 'admin', title: 'Admin', roles: ['ROLE_ADMIN']},
];

const route = useRoute();
const auth = useAuth();

function routerLinkClass(routeName: string) {
  return routeName === route.name ? 'active text-white' : '';
}
</script>

<template>
  <nav class="navbar navbar-expand-md bg-body-tertiary" aria-label="Navbar">
    <div class="container-fluid">

      <div class="navbar-brand col-3">
        <img src="/logo-voleg.svg" width="100" height="40" alt="logo">
      </div>

      <button class="navbar-toggler"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbars"
              aria-controls="navbars"
              aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse col-9" id="navbars">

        <ul class="navbar-nav col-8 justify-content-center nav-pills">
          <li v-for="route in centerRoutes" class="nav-item">
            <router-link
              v-if="!route.hasOwnProperty('roles') || auth.hasRole(route.roles)"
              class="nav-link"
              :to="{ name: route.name }"
              :class="routerLinkClass(route.name)"
            >
              {{ route.title }}
            </router-link>
          </li>
        </ul>

        <div class="d-md-flex d-lg-flex col-4 justify-content-end">
          <ul class="navbar-nav nav-pills me-2">
            <li class="nav-item nav-link">
              <ColorThemeToggle></ColorThemeToggle>
            </li>
            <li v-if="!auth.user.isSignedIn" class="nav-item">
              <router-link
                class="nav-link"
                :to="{ name: 'signIn' }"
                :class="routerLinkClass('signIn')"
              >
                Sign In
              </router-link>
            </li>
            <li v-if="auth.user.isSignedIn" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">{{ auth.user.displayName ?? 'User' }}</a>
              <ul class="dropdown-menu dropdown-menu-end">
                <router-link class="dropdown-item" :to="{ name: 'profile' }">Profile</router-link>
                <li><a @click="auth.signOut()" class="dropdown-item" href="#">Sign Out</a></li>
              </ul>
            </li>
          </ul>
        </div>

      </div>

    </div>
  </nav>
</template>