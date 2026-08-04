<script setup lang="ts">
import { ref } from 'vue'
import PagePagination from '@/modules/core/components/pagination/PagePagination.vue'
import { usePagePagination } from '@/modules/core/components/pagination/usePagePagination'
import AddConnectionModal from '@/modules/splitExpense/components/AddConnectionModal.vue'
import { useConnections } from '@/modules/splitExpense/composables/useConnections'
import { getConnectionPartnerName } from '@/modules/splitExpense/utils/connections'
import type { ApiSeConnection } from '@/modules/splitExpense/types'
import { useAuth } from '@/modules/user/stores/useAuth'

const auth = useAuth()
const connections = useConnections()
const { pageIndex, pageSize, totalPages, offset, limit, setPageIndex, setPageSize, setTotalItems } =
  usePagePagination(10)
const items = ref<ApiSeConnection[]>([])
const addConnectionOpen = ref(false)

function partnerName(connection: ApiSeConnection) {
  if (auth.user.id === null) return ''
  return getConnectionPartnerName(connection, auth.user.id)
}

async function loadPage(nextPage = pageIndex.value) {
  setPageIndex(nextPage)

  await connections.loadConnections(offset.value, limit.value, 'accepted')
  setTotalItems(connections.totalCount.value)
  items.value = connections.acceptedConnections.value

  if (pageIndex.value > totalPages.value) {
    await loadPage(totalPages.value)
  }
}

async function removeConnection(connection: ApiSeConnection) {
  if (!(await connections.removeConnection(connection))) return
  await loadPage()
}

async function onPageSizeChange(size: number) {
  setPageSize(size)
  await loadPage(1)
}

loadPage(1)
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column align-items-center gap-2">
      <div class="se-panel">
        <button
          type="button"
          class="btn btn-outline-primary w-100"
          @click="addConnectionOpen = true"
        >
          <i class="bi bi-person-plus" aria-hidden="true"></i>
          Add connection
        </button>

        <div v-if="connections.isListLoading.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading connections…</span>
          </div>
        </div>

        <div v-else>
          <p v-if="items.length === 0" class="text-muted mb-0">
            No connections yet.
          </p>

          <ul v-else class="list-group list-group-flush">
            <li
              v-for="connection in items"
              :key="connection.id"
              class="list-group-item d-flex justify-content-between align-items-center gap-2"
            >
              <span class="text-truncate">{{ partnerName(connection) }}</span>
              <button
                type="button"
                class="btn btn-outline-danger btn-sm flex-shrink-0"
                :disabled="connections.isLoading.value"
                @click="removeConnection(connection)"
              >
                Remove
              </button>
            </li>
          </ul>

          <PagePagination
            :page-index="pageIndex"
            :page-size="pageSize"
            :page-size-options="[10, 25, 50]"
            :total-pages="totalPages"
            aria-label="Connections pagination"
            @update:page-index="loadPage"
            @update:page-size="onPageSizeChange"
          />
        </div>

        <AddConnectionModal
          v-model:open="addConnectionOpen"
          :send="connections.sendRequest"
        />
      </div>
    </div>
  </div>
</template>
