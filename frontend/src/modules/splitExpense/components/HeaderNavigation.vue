<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { type SplitExpenseTab } from '@/modules/splitExpense/types'
import { useRouter } from 'vue-router'

const tabs: SplitExpenseTab[] = [
  { tag: 'expenses', title: 'Expenses', route: 'seExpenses', icon: 'bi-plus-slash-minus' },
  { tag: 'connections', title: 'Connections', route: 'seConnections', icon: 'bi-people-fill' },
  { tag: 'charts', title: 'Charts', route: 'seCharts', icon: 'bi-clipboard2-data', disabled: true }
]

const router = useRouter()
const root = ref<HTMLElement | null>(null)
const isOpen = ref(false)
const activeTab = ref(getActiveTab())

function getActiveTab() {
  const route = router.currentRoute.value.name
  return tabs.find((t) => t.route === route)
}

function toggle() {
  isOpen.value = !isOpen.value
}

function selectTab(tab: SplitExpenseTab) {
  activeTab.value = tab
  router.push({ name: tab.route })
}

function onOutsideClick(e: MouseEvent) {
  if (root.value && !root.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', onOutsideClick))
</script>

<template>
  <nav ref="root" class="se-nav navbar" aria-label="Split expense sections" @click="toggle">
    <div class="container-fluid justify-content-center py-0">
      <div class="dropdown">
        <button type="button" class="se-nav-btn nav-link dropdown-toggle">
          <i class="bi" :class="activeTab.icon" aria-hidden="true"></i>
          {{ activeTab.title }}
        </button>
        <ul class="dropdown-menu dropdown-menu-center" :class="{ show: isOpen }">
          <li v-for="tab in tabs" :key="tab.tag">
            <button
              type="button"
              class="dropdown-item"
              :class="{ active: activeTab.tag === tab.tag, disabled: tab.disabled }"
              @click="selectTab(tab)"
            >
              <i class="bi" :class="tab.icon" aria-hidden="true"></i>
              {{ tab.title }}
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.se-nav {
  padding: 0;
  background-color: var(--bs-secondary-bg);
  cursor: pointer;
}

.se-nav:hover .se-nav-btn {
  color: var(--bs-nav-link-hover-color);
}

.se-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  min-height: 2.75rem;
  margin: 0;
  padding: 0.5rem 0.75rem;
  background: transparent;
  border: 0;
  font-size: 1.05rem;
  font-weight: 500;
  cursor: pointer;
}

.se-nav-btn::after {
  margin-left: 0.15rem;
}

.dropdown-item .bi {
  margin-right: 0.35rem;
}
</style>
