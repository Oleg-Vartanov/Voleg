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

type MenuItem = {
  name: string
  title: string
  roles?: string[]
  /** Route to navigate to (defaults to name). */
  to?: string
  /** Extra route names that count as active for this item. */
  activeWhen?: string[]
}

const menuGroups: MenuItem[][] = [
  [
    { name: 'about', title: 'About' },
    { name: 'pricing', title: 'Pricing' },
    { name: 'admin', title: 'Admin', roles: ['ROLE_ADMIN'] }
  ],
  [
    {
      name: 'splitExpense',
      title: 'Split Expense',
      to: 'seFeed',
      activeWhen: ['seFeed', 'seBalance', 'seInsights', 'seConnections']
    },
    { name: 'footballPredictions', title: 'Football Predictions' }
  ]
]

function onNavigate() {
  emit('navigate')
}

function menuTo(menuItem: MenuItem) {
  return { name: menuItem.to ?? menuItem.name }
}

function isActive(menuItem: MenuItem) {
  if (menuItem.activeWhen?.some((name) => route.name === name)) return true
  return route.matched.some((record) => record.name === menuItem.name)
}
</script>

<template>
  <aside
    class="side-nav"
    :class="{ 'side-nav--open': open, 'side-nav--collapsed': collapsed }"
    aria-label="Sidebar navigation"
  >
    <nav class="side-nav__content">
      <template v-for="(group, groupIndex) in menuGroups" :key="groupIndex">
        <hr v-if="groupIndex > 0" class="side-nav__divider" />

        <ul class="side-nav__list">
          <li v-for="menuItem in group" :key="menuItem.name" class="side-nav__item">
            <router-link
              v-if="!menuItem.roles || auth.hasRole(menuItem.roles)"
              class="side-nav__link"
              :class="{ 'side-nav__link--active': isActive(menuItem) }"
              :to="menuTo(menuItem)"
              @click="onNavigate"
            >
              {{ menuItem.title }}
            </router-link>
          </li>
        </ul>
      </template>

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
