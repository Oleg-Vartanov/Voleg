<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue'
import NavBarDropdown from '@/modules/core/components/NavBarDropdown.vue'
import NavBarDropdownItem from '@/modules/core/components/NavBarDropdownItem.vue'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/modules/user/stores/useAuth'

const route = useRoute()
const auth = useAuth()

type menuItemType = { name: string; title: string; roles?: string[] }

const menuItems: menuItemType[] = [
  { name: 'about', title: 'About' },
  { name: 'footballPredictions', title: 'Football Predictions' },
  { name: 'pricing', title: 'Pricing' },
  { name: 'admin', title: 'Admin', roles: ['ROLE_ADMIN'] }
]

const isMenuDropdownOpen = ref(false)
const isProfileDropdownOpen = ref(false)

const activeMenuItem = computed((): menuItemType | null => {
  return menuItems.find((nav) => nav.name === route.name) ?? null
})
</script>

<template>
  <nav class="navbar navbar-expand-md bg-body-tertiary" aria-label="Navbar">
    <div class="container-fluid">
      <div class="d-flex d-md-none w-100 justify-content-between">
        <div class="navbar-brand p-0 pe-2 m-0">
          <img src="/logo-voleg.svg" width="100" height="40" alt="logo" />
        </div>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbars"
          aria-controls="navbars"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>

      <div id="navbars" class="collapse navbar-collapse justify-content-center w-100">
        <div class="navbar-brand p-0 pe-2 m-0 d-none d-md-block">
          <img src="/logo-voleg.svg" width="100" height="40" alt="logo" />
        </div>

        <ul class="navbar-nav">
          <!-- Menu Navigation-->
          <NavBarDropdown
            v-model:is-open="isMenuDropdownOpen"
            :text="activeMenuItem?.title ?? 'Menu'"
            :active="activeMenuItem !== null"
          >
            <NavBarDropdownItem v-for="menuItem in menuItems" :key="menuItem.name">
              <router-link
                v-if="!menuItem.roles || auth.hasRole(menuItem.roles)"
                class="dropdown-item"
                :class="{ active: menuItem.name === route.name }"
                :to="{ name: menuItem.name }"
              >
                {{ menuItem.title }}
              </router-link>
            </NavBarDropdownItem>
          </NavBarDropdown>

          <!-- Sign In -->
          <li class="nav-item">
            <router-link
              v-if="!auth.user.isSignedIn"
              class="nav-link"
              :to="{ name: 'signIn' }"
              :class="{ active: 'signIn' === route.name }"
            >
              Sign In
            </router-link>
          </li>

          <!-- Profile Navigation-->
          <NavBarDropdown
            v-if="auth.user.isSignedIn"
            v-model:is-open="isProfileDropdownOpen"
            :text="auth.user.displayName ?? 'User'"
            :active="route.name === 'profile'"
          >
            <NavBarDropdownItem>
              <router-link
                class="dropdown-item"
                :class="{ active: route.name === 'profile' }"
                :to="{ name: 'profile' }"
              >
                Profile
              </router-link>
            </NavBarDropdownItem>

            <NavBarDropdownItem>
              <a class="dropdown-item" href="#" @click="auth.signOut()">Sign Out</a>
            </NavBarDropdownItem>
          </NavBarDropdown>

          <li class="nav-item nav-link">
            <ColorThemeToggle></ColorThemeToggle>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
