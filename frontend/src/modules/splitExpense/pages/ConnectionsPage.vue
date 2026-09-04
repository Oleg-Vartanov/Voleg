<script setup lang="ts">
import { ref } from 'vue'
import PagePagination from '@/modules/core/components/pagination/PagePagination.vue'
import { usePagePagination } from '@/modules/core/components/pagination/usePagePagination'
import AddConnectionModal from '@/modules/splitExpense/components/AddConnectionModal.vue'
import { useConnectionRequests } from '@/modules/splitExpense/composables/useConnectionRequests'
import { useConnections } from '@/modules/splitExpense/composables/useConnections'
import { getConnectionPartnerName } from '@/modules/splitExpense/utils/connections'
import type { ApiSeConnection } from '@/modules/splitExpense/types'
import type { ApiUser } from '@/modules/core/apiType'
import { useAuth } from '@/modules/user/stores/useAuth'

const auth = useAuth()
const requests = useConnectionRequests()
const connections = useConnections()
const { pageIndex, pageSize, totalPages, offset, limit, setPageIndex, setPageSize, setTotalItems } =
  usePagePagination(5)
const items = ref<ApiSeConnection[]>([])
const addConnectionOpen = ref(false)

const requestTabs = [
  { id: 'incoming' as const, label: 'Incoming' },
  { id: 'outgoing' as const, label: 'Outgoing' },
  { id: 'rejected' as const, label: 'Rejected' }
]

requests.loadTab('incoming')

function partnerName(connection: ApiSeConnection) {
  if (auth.user.id === null) return ''
  return getConnectionPartnerName(connection, auth.user.id)
}

async function sendConnectionRequest(user: ApiUser) {
  const result = await connections.sendRequest(user)
  if (result.ok) {
    if (requests.activeTab.value === 'outgoing') {
      await requests.loadTab('outgoing', true)
    } else {
      requests.invalidateTab('outgoing')
    }
  }
  return result
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
    <div class="container d-flex flex-column align-items-center gap-3">
      <div class="se-panel">
        <button
          type="button"
          class="btn btn-outline-primary w-100"
          @click="addConnectionOpen = true"
        >
          <i class="bi bi-person-plus" aria-hidden="true"></i>
          Add connection
        </button>
      </div>

      <div class="se-panel se-panel-block">
        <h2 class="se-panel__title">Requests</h2>

        <ul class="nav nav-tabs justify-content-center" role="tablist">
          <li v-for="tab in requestTabs" :key="tab.id" class="nav-item" role="presentation">
            <button
              type="button"
              class="nav-link"
              :class="{ active: requests.activeTab.value === tab.id }"
              role="tab"
              :aria-selected="requests.activeTab.value === tab.id"
              @click="requests.selectTab(tab.id)"
            >
              {{ tab.label }}
            </button>
          </li>
        </ul>

        <div v-if="requests.isListLoading.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading requests…</span>
          </div>
        </div>

        <template v-else>
          <ul class="list-group list-group-flush">
            <li
              v-if="requests.items.value.length === 0"
              class="list-group-item text-muted text-center"
            >
              <template v-if="requests.activeTab.value === 'incoming'"
                >No incoming requests.</template
              >
              <template v-else-if="requests.activeTab.value === 'outgoing'"
                >No outgoing requests.</template
              >
              <template v-else>No rejected requests.</template>
            </li>
            <template v-else>
              <li
                v-for="connection in requests.items.value"
                :key="connection.id"
                class="list-group-item d-flex justify-content-between align-items-center gap-2"
              >
                <span class="text-truncate">{{ partnerName(connection) }}</span>

                <div
                  v-if="requests.activeTab.value === 'incoming'"
                  class="d-flex gap-2 flex-shrink-0"
                >
                  <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    :disabled="requests.isLoading.value"
                    @click="requests.acceptRequest(connection)"
                  >
                    Accept
                  </button>
                  <button
                    type="button"
                    class="btn btn-outline-danger btn-sm"
                    :disabled="requests.isLoading.value"
                    @click="requests.rejectRequest(connection)"
                  >
                    Reject
                  </button>
                </div>

                <button
                  v-else-if="requests.activeTab.value === 'outgoing'"
                  type="button"
                  class="btn btn-outline-secondary btn-sm flex-shrink-0 se-action-btn"
                  :disabled="requests.isLoading.value"
                  @click="requests.cancelRequest(connection)"
                >
                  Cancel
                </button>

                <button
                  v-else-if="connection.requestedBy.id !== auth.user.id"
                  type="button"
                  class="btn btn-outline-secondary btn-sm flex-shrink-0 se-action-btn"
                  :disabled="requests.isLoading.value"
                  @click="requests.removeConnection(connection)"
                >
                  Remove
                </button>
              </li>
            </template>
          </ul>

          <PagePagination
            :page-index="requests.pageIndex.value"
            :page-size="requests.pageSize.value"
            :page-size-options="[5, 10, 25, 50]"
            :total-pages="requests.totalPages.value"
            aria-label="Requests pagination"
            @update:page-index="requests.setPage"
            @update:page-size="requests.setPageSize"
          />
        </template>
      </div>

      <div class="se-panel se-panel-block">
        <h2 class="se-panel__title">Connections</h2>
        <div v-if="connections.isListLoading.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading connections…</span>
          </div>
        </div>

        <div v-else>
          <ul class="list-group list-group-flush">
            <li v-if="items.length === 0" class="list-group-item text-muted text-center">
              No connections yet.
            </li>
            <template v-else>
              <li
                v-for="connection in items"
                :key="connection.id"
                class="list-group-item d-flex justify-content-between align-items-center gap-2"
              >
                <span class="text-truncate">{{ partnerName(connection) }}</span>
                <button
                  type="button"
                  class="btn btn-outline-danger btn-sm flex-shrink-0 se-action-btn"
                  :disabled="connections.isLoading.value"
                  @click="removeConnection(connection)"
                >
                  Remove
                </button>
              </li>
            </template>
          </ul>

          <PagePagination
            :page-index="pageIndex"
            :page-size="pageSize"
            :page-size-options="[5, 10, 25, 50]"
            :total-pages="totalPages"
            aria-label="Connections pagination"
            @update:page-index="loadPage"
            @update:page-size="onPageSizeChange"
          />
        </div>
      </div>

      <AddConnectionModal v-model:open="addConnectionOpen" :send="sendConnectionRequest" />
    </div>
  </div>
</template>
