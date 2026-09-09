<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue'
import { ref, watch } from 'vue'
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

type MenuChild = { name: string; title: string; disabled?: boolean }

type MenuItem = { name: string; title: string; roles?: string[]; children?: MenuChild[] }

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
      children: [
        { name: 'seExpenses', title: 'Expenses' },
        { name: 'seAdjustments', title: 'Adjustments' },
        { name: 'seBalance', title: 'Balance' },
        { name: 'seConnections', title: 'Connections' }
      ]
    },
    { name: 'footballPredictions', title: 'Football Predictions' }
  ]
]

const menuItems = menuGroups.flat()

function onNavigate() {
  emit('navigate')
}

function isActive(menuItem: MenuItem) {
  if (menuItem.children?.length) {
    return menuItem.children.some((child) => route.name === child.name)
  }

  return route.matched.some((record) => record.name === menuItem.name)
}

function isChildActive(child: MenuChild) {
  return route.name === child.name
}

const expandedMenus = ref<Record<string, boolean>>({})

function isMenuExpanded(name: string) {
  return expandedMenus.value[name] ?? false
}

function toggleMenu(name: string) {
  expandedMenus.value = { ...expandedMenus.value, [name]: !isMenuExpanded(name) }
}

watch(
  () => route.matched,
  () => {
    for (const item of menuItems) {
      if (item.children && isActive(item)) {
        expandedMenus.value = { ...expandedMenus.value, [item.name]: true }
      }
    }
  },
  { immediate: true, deep: true }
)
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
            <template v-if="!menuItem.roles || auth.hasRole(menuItem.roles)">
              <div v-if="menuItem.children?.length" class="side-nav__dropdown">
                <button
                  type="button"
                  class="side-nav__link side-nav__link--toggle"
                  :class="{
                    'side-nav__link--active': isActive(menuItem),
                    'side-nav__link--expanded': isMenuExpanded(menuItem.name)
                  }"
                  :aria-expanded="isMenuExpanded(menuItem.name)"
                  @click="toggleMenu(menuItem.name)"
                >
                  <span class="side-nav__link-label">{{ menuItem.title }}</span>
                  <i
                    class="bi side-nav__chevron"
                    :class="isMenuExpanded(menuItem.name) ? 'bi-chevron-down' : 'bi-chevron-left'"
                    aria-hidden="true"
                  />
                </button>
                <ul v-show="isMenuExpanded(menuItem.name)" class="side-nav__sublist">
                  <li v-for="child in menuItem.children" :key="child.name" class="side-nav__item">
                    <router-link
                      v-if="!child.disabled"
                      class="side-nav__link side-nav__link--sub"
                      :class="{ 'side-nav__link--active': isChildActive(child) }"
                      :to="{ name: child.name }"
                      @click="onNavigate"
                    >
                      {{ child.title }}
                    </router-link>
                    <span
                      v-else
                      class="side-nav__link side-nav__link--sub side-nav__link--disabled"
                    >
                      {{ child.title }}
                    </span>
                  </li>
                </ul>
              </div>
              <router-link
                v-else
                class="side-nav__link"
                :class="{ 'side-nav__link--active': isActive(menuItem) }"
                :to="{ name: menuItem.name }"
                @click="onNavigate"
              >
                {{ menuItem.title }}
              </router-link>
            </template>
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
