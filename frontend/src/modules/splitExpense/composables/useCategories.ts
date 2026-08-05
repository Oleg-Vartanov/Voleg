import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { FormSelectOption } from '@/modules/core/components/form/types'
import type { ApiSeCategory } from '@/modules/splitExpense/types'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { categories as categoryIcons, type CategoryKey } from '@/modules/splitExpense/categories'

const categories = ref<ApiSeCategory[] | null>(null)
let loadPromise: Promise<void> | null = null

export function useCategories() {
  const topAlerts = useTopAlerts()

  const categoryOptions = computed((): FormSelectOption[] =>
    (categories.value ?? []).map((category) => ({
      value: category.id,
      label: category.title,
      icon:
        category.tag in categoryIcons
          ? categoryIcons[category.tag as CategoryKey]
          : categoryIcons.other
    }))
  )

  async function load(): Promise<void> {
    if (loadPromise) return loadPromise

    loadPromise = client
      .listSplitExpenseCategories()
      .then((response) => {
        categories.value = response.data
      })
      .catch(() => {
        categories.value = []
        topAlerts.add('Failed to load categories.', 'danger', 5)
      })

    return loadPromise
  }

  function findById(id: number | null): ApiSeCategory | null {
    if (id === null || categories.value === null) return null

    return categories.value.find((category) => category.id === id) ?? null
  }

  function defaultCategoryId(): number | null {
    if (!categories.value?.length) return null
    return categories.value[0].id
  }

  return {
    categories,
    categoryOptions,
    load,
    findById,
    defaultCategoryId
  }
}
