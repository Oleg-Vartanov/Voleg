<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue';
import {useRoute} from 'vue-router'
import {useAuth} from "@/modules/auth";

let centerRoutes: { name: string, title: string }[] = [
  {name: 'home', title: 'Home'},
  {name: 'about', title: 'About'},
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

        <router-link class="navbar-brand col-lg-3 me-0" :to="{ name: 'home' }">
          <img src="/logo-voleg.svg" width="100" height="40" alt="logo">
        </router-link>

        <ul class="navbar-nav col-lg-6 justify-content-lg-center nav-pills">
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

        <div class="d-lg-flex col-lg-3 justify-content-lg-end">
          <ul class="navbar-nav nav-pills">
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