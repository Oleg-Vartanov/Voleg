import { computed, ref } from 'vue'

export function usePagePagination(initialPageSize = 10) {
  const pageIndex = ref(1)
  const pageSize = ref(initialPageSize)
  const totalItems = ref(0)

  const offset = computed(() => (pageIndex.value - 1) * pageSize.value)
  const limit = computed(() => pageSize.value)
  const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)))
  const hasNextPage = computed(() => pageIndex.value < totalPages.value)

  function setPageIndex(value: number) {
    pageIndex.value = Math.max(1, value)
  }

  function setPageSize(value: number) {
    pageSize.value = value
    pageIndex.value = 1
  }

  function setTotalItems(value: number) {
    totalItems.value = Math.max(0, value)
  }

  return {
    pageIndex,
    pageSize,
    totalItems,
    totalPages,
    offset,
    limit,
    hasNextPage,
    setPageIndex,
    setPageSize,
    setTotalItems
  }
}
