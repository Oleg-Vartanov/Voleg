<script setup lang="ts">
import LogoVoleg from './LogoVoleg.vue'

defineProps<{
  collapsed: boolean
  sidebarCollapsed: boolean
}>()

const emit = defineEmits<{
  toggle: []
}>()
</script>

<template>
  <header class="app-navbar navbar">
    <button
      type="button"
      class="app-navbar__row app-navbar__toggle"
      :aria-label="collapsed ? 'Show sidebar' : 'Hide sidebar'"
      @click="emit('toggle')"
    >
      <span
        class="app-navbar__sidebar-col"
        :class="{ 'app-navbar__sidebar-col--narrow': sidebarCollapsed }"
      >
        <span class="app-navbar__chevron side-nav__link">
          <i
            class="bi"
            :class="collapsed ? 'bi-chevron-right' : 'bi-chevron-left'"
            aria-hidden="true"
          ></i>
        </span>
      </span>
      <LogoVoleg class="app-navbar__brand" />
    </button>
  </header>
</template>

<style scoped>
.app-navbar {
  padding: 0;
  min-height: 0;
  background-color: var(--ov-chrome-bg);
  border-bottom: 1px solid var(--ov-color-primary);
}

@media (min-width: 768px) {
  .app-navbar {
    border-bottom: none;
  }
}
</style>
