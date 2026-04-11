<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/modules/user/stores/useAuth';
import { computed } from 'vue';

const route = useRoute();
const auth = useAuth();

type NavRoute = { name: string, title: string, roles?: string[] };

const navRoutes: NavRoute[] = [
  { name: 'about', title: 'About' },
  { name: 'football-predictions', title: 'Football Predictions' },
  { name: 'pricing', title: 'Pricing' },
  { name: 'admin', title: 'Admin', roles: ['ROLE_ADMIN'] },
];

const currentNavRoute = computed((): NavRoute => {
  return navRoutes.find(nav => nav.name === route.name) ?? { name: 'pages', title: 'Pages' };
});

function routerLinkClass(routeName: string) {
  return routeName === route.name ? 'active text-white' : '';
}
</script>

<template>
  <nav class="navbar navbar-expand-md bg-body-tertiary" aria-label="Navbar">
    <div class="container-fluid">

      <div class="d-flex d-md-none w-100 justify-content-between">
        <div class="navbar-brand p-0 pe-2 m-0">
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
      </div>

      <div class="collapse navbar-collapse justify-content-center w-100" id="navbars">

        <div class="navbar-brand p-0 pe-2 m-0 d-none d-md-block">
          <img src="/logo-voleg.svg" width="100" height="40" alt="logo">
        </div>

        <ul class="navbar-nav">

          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ currentNavRoute.title }}
            </a>
            <ul class="dropdown-menu">
              <li v-for="navRoute in navRoutes" :key="route.name">
                <router-link
                  v-if="!navRoute.hasOwnProperty('roles') || auth.hasRole(navRoute.roles)"
                  class="dropdown-item"
                  :to="{ name: navRoute.name }"
                  :class="routerLinkClass(navRoute.name)"
                >
                  {{ navRoute.title }}
                </router-link>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <ul class="navbar-nav nav-pills me-2">
              <li v-if="!auth.user.isSignedIn" class="nav-item">
                <router-link
                  class="nav-link"
                  :to="{ name: 'signIn' }"
                >
                  Sign In
                </router-link>
              </li>
              <li v-if="auth.user.isSignedIn" class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                   aria-expanded="false">{{ auth.user.displayName ?? 'User' }}</a>
                <ul class="dropdown-menu">
                  <router-link class="dropdown-item" :to="{ name: 'profile' }">Profile</router-link>
                  <li><a @click="auth.signOut()" class="dropdown-item" href="#">Sign Out</a></li>
                </ul>
              </li>
              <li class="nav-item nav-link">
                <ColorThemeToggle></ColorThemeToggle>
              </li>
            </ul>
          </li>

        </ul>

      </div>
    </div>
  </nav>
</template>