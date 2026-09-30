<script setup lang="ts">
import ColorThemeToggle from './ColorThemeToggle.vue'
import DesignToggle from './DesignToggle.vue'
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/modules/user/stores/useAuth'

defineProps<{
  open: boolean
  collapsed: boolean
}>()

const emit = defineEmits<{
  navigate: []
}>()

const route = useRoute()
const router = useRouter()
const auth = useAuth()

type MenuChild = { name: string; title: string; disabled?: boolean }

type MenuItem = {
  name: string
  title: string
  roles?: string[]
  children?: MenuChild[]
  /** When set, clicking the parent expands the submenu and navigates here. */
  defaultChild?: string
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
      defaultChild: 'seFeed',
      children: [
        { name: 'seFeed', title: 'Feed' },
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
    if (menuItem.name === 'splitExpense' && route.name === 'seBalance') return true
    return menuItem.children.some((child) => route.name === child.name)
  }

  return route.matched.some((record) => record.name === menuItem.name)
}

function isChildActive(child: MenuChild) {
  // Balance is reached from Feed, so keep Feed highlighted there.
  if (child.name === 'seFeed' && route.name === 'seBalance') return true
  return route.name === child.name
}

const expandedMenus = ref<Record<string, boolean>>({})

function isMenuExpanded(name: string) {
  return expandedMenus.value[name] ?? false
}

function toggleMenu(name: string) {
  expandedMenus.value = { ...expandedMenus.value, [name]: !isMenuExpanded(name) }
}

function expandOnly(name: string) {
  const next: Record<string, boolean> = {}
  for (const item of menuItems) {
    if (item.children?.length) {
      next[item.name] = item.name === name
    }
  }
  expandedMenus.value = next
}

async function onParentClick(menuItem: MenuItem) {
  if (!menuItem.defaultChild) {
    toggleMenu(menuItem.name)
    return
  }

  expandOnly(menuItem.name)
  if (route.name !== menuItem.defaultChild) {
    await router.push({ name: menuItem.defaultChild })
  }
  onNavigate()
}

watch(
  () => route.name,
  () => {
    const next: Record<string, boolean> = {}
    for (const item of menuItems) {
      if (item.children?.length) {
        next[item.name] = isActive(item)
      }
    }
    expandedMenus.value = next
  },
  { immediate: true }
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
                  @click="onParentClick(menuItem)"
                >
                  <span class="side-nav__link-label">{{ menuItem.title }}</span>
                  <i
                    v-if="!menuItem.defaultChild"
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
        <li class="side-nav__item">
          <DesignToggle class="side-nav__theme" />
        </li>
      </ul>
    </nav>
  </aside>
</template>
