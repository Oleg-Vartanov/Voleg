<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ResponsiveTabs from '@/modules/core/components/ResponsiveTabs.vue'
import { type SplitExpenseTab, type SplitExpenseTabTag } from '@/modules/splitExpense/types'

const tabs: SplitExpenseTab[] = [
  { tag: 'expenses', title: 'Expenses', route: 'seExpenses', icon: 'bi-plus-slash-minus' },
  { tag: 'users', title: 'Users', route: 'seUsers', icon: 'bi-people-fill' },
  { tag: 'charts', title: 'Charts', route: 'seCharts', icon: 'bi-clipboard2-data', disabled: true }
]

const responsiveTabs = tabs.map(({ tag, title, icon, disabled }) => ({
  value: tag,
  label: title,
  icon,
  disabled
}))

const route = useRoute()
const router = useRouter()

const activeTab = ref<SplitExpenseTabTag>(
  tabs.find((t) => t.route === route.name)?.tag ?? 'expenses'
)

watch(
  () => route.name,
  (name) => {
    const tab = tabs.find((t) => t.route === name)
    if (tab) activeTab.value = tab.tag
  }
)

watch(activeTab, (tag) => {
  const tab = tabs.find((t) => t.tag === tag)
  if (tab && tab.route !== route.name) {
    router.push({ name: tab.route })
  }
})
</script>

<template>
  <ResponsiveTabs
    v-model="activeTab"
    :tabs="responsiveTabs"
    aria-label="Split expense sections"
  />
</template>
