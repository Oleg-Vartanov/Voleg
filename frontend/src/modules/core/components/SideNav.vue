<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/modules/user/stores/useAuth'

defineProps<{
  open: boolean
  collapsed: boolean
}>()

const emit = defineEmits<{
  navigate: []
}>()

const route = useRoute()
const auth = useAuth()

type MenuItem = { name: string; title: string; roles?: string[] }

const menuItems: MenuItem[] = [
  { name: 'about', title: 'About' },
  { name: 'splitExpense', title: 'Split Expense' },
  { name: 'footballPredictions', title: 'Football Predictions' },
  { name: 'pricing', title: 'Pricing' },
  { name: 'admin', title: 'Admin', roles: ['ROLE_ADMIN'] }
]

function onNavigate() {
  emit('navigate')
}

function onSignOut() {
  auth.signOut()
  onNavigate()
}

function isActive(menuItem: MenuItem) {
  return route.matched.some(record => record.name === menuItem.name);
}
</script>

<template>
  <aside
    class="side-nav"
    :class="{ 'side-nav--open': open, 'side-nav--collapsed': collapsed }"
    aria-label="Sidebar navigation"
  >
    <nav class="side-nav__content">
      <ul class="side-nav__list">
        <li v-for="menuItem in menuItems" :key="menuItem.name" class="side-nav__item">
          <router-link
            v-if="!menuItem.roles || auth.hasRole(menuItem.roles)"
            class="side-nav__link"
            :class="{ 'side-nav__link--active': isActive(menuItem) }"
            :to="{ name: menuItem.name }"
            @click="onNavigate"
          >
            {{ menuItem.title }}
          </router-link>
        </li>
      </ul>

      <hr class="side-nav__divider" />

      <ul class="side-nav__list">
        <li v-if="!auth.user.isSignedIn" class="side-nav__item">
          <router-link
            class="side-nav__link"
            :class="{ 'side-nav__link--active': route.name === 'signIn' }"
            :to="{ name: 'signIn' }"
            @click="onNavigate"
          >
            Sign In
          </router-link>
        </li>

        <template v-else>
          <li class="side-nav__item">
            <span class="side-nav__link side-nav__link--user">
              {{ auth.user.displayName ?? 'User' }}
            </span>
          </li>
          <li class="side-nav__item">
            <router-link
              class="side-nav__link"
              :class="{ 'side-nav__link--active': route.name === 'profileInfo' }"
              :to="{ name: 'profileInfo' }"
              @click="onNavigate"
            >
              Profile
            </router-link>
          </li>
          <li class="side-nav__item">
            <router-link
              class="side-nav__link"
              :class="{ 'side-nav__link--active': route.name === 'contacts' }"
              :to="{ name: 'contacts' }"
              @click="onNavigate"
            >
              Contacts
            </router-link>
          </li>
          <li class="side-nav__item">
            <button type="button" class="side-nav__link" @click="onSignOut">Sign Out</button>
          </li>
        </template>
      </ul>

      <hr class="side-nav__divider" />

      <ul class="side-nav__list">
        <li class="side-nav__item">
          <ColorThemeToggle class="side-nav__theme" />
        </li>
      </ul>
    </nav>
  </aside>
</template>
