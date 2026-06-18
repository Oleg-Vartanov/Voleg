<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/modules/core/components/NavBar.vue'
import SideNav from '@/modules/core/components/SideNav.vue'
import TopAlerts from '@/modules/core/components/TopAlerts.vue'

const sidebarCollapsedKey = 'voleg-sidebar-collapsed'
const mobileMediaQuery = '(max-width: 767.98px)'

const route = useRoute()
const isMobile = ref(window.matchMedia(mobileMediaQuery).matches)
const isSidebarOpen = ref(false)
const isSidebarCollapsed = ref(window.localStorage.getItem(sidebarCollapsedKey) === '1')

const isToggleCollapsed = computed(() =>
  isMobile.value ? !isSidebarOpen.value : isSidebarCollapsed.value
)

watch(
  () => route.fullPath,
  () => {
    if (isMobile.value) {
      isSidebarOpen.value = false
    }
  }
)

watch(isSidebarCollapsed, collapsed => {
  window.localStorage.setItem(sidebarCollapsedKey, collapsed ? '1' : '0')
})

function toggleSidebar() {
  if (isMobile.value) {
    isSidebarOpen.value = !isSidebarOpen.value
    return
  }

  isSidebarCollapsed.value = !isSidebarCollapsed.value
}

function onSideNavNavigate() {
  if (isMobile.value) {
    isSidebarOpen.value = false
  }
}

let mediaQueryList: MediaQueryList | null = null

function onViewportChange(event: MediaQueryListEvent) {
  isMobile.value = event.matches
  if (event.matches) {
    isSidebarOpen.value = false
  }
}

onMounted(() => {
  mediaQueryList = window.matchMedia(mobileMediaQuery)
  mediaQueryList.addEventListener('change', onViewportChange)
})

onUnmounted(() => {
  mediaQueryList?.removeEventListener('change', onViewportChange)
})
</script>

<template>
  <div class="app-layout">
    <NavBar
      :collapsed="isToggleCollapsed"
      :sidebar-collapsed="isSidebarCollapsed && !isMobile"
      @toggle="toggleSidebar"
    />

    <div class="app-body">
      <div
        v-if="isMobile && isSidebarOpen"
        class="sidebar-backdrop"
        aria-hidden="true"
        @click="isSidebarOpen = false"
      />

      <SideNav
        :open="isSidebarOpen"
        :collapsed="isSidebarCollapsed"
        @navigate="onSideNavNavigate"
      />

      <main class="app-main">
        <TopAlerts />
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.app-layout {
  --app-sidebar-width: 240px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.app-body {
  display: flex;
  flex: 1;
  min-height: 0;
  position: relative;
}

.app-main {
  flex: 1;
  min-width: 0;
  overflow: auto;
}

@media (min-width: 768px) {
  .app-main {
    border-top: 1px solid var(--ov-color-primary);
  }
}

.sidebar-backdrop {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1040;
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
