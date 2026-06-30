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
    <div class="app-navbar__row">
      <div
        class="app-navbar__sidebar-col"
        :class="{ 'app-navbar__sidebar-col--narrow': sidebarCollapsed }"
      >
        <button
          type="button"
          class="app-navbar__toggle side-nav__link"
          :aria-label="collapsed ? 'Show sidebar' : 'Hide sidebar'"
          @click="emit('toggle')"
        >
          <i
            class="bi"
            :class="collapsed ? 'bi-chevron-right' : 'bi-chevron-left'"
            aria-hidden="true"
          ></i>
        </button>
      </div>
      <LogoVoleg class="app-navbar__brand" />
    </div>
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
