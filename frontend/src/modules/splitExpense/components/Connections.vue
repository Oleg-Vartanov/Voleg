<script setup lang="ts">
import { computed, ref } from 'vue'
import ResponsiveTabs from '@/modules/core/components/ResponsiveTabs.vue'
import AddConnectionModal from '@/modules/splitExpense/components/AddConnectionModal.vue'
import { useConnections } from '@/modules/splitExpense/composables/useConnections'
import { getConnectionPartner } from '@/modules/splitExpense/utils/connections'
import { useAuth } from '@/modules/user/stores/useAuth'

type ConnectionsTab = 'list' | 'requests'

const auth = useAuth()
const connections = useConnections()
const activeTab = ref<ConnectionsTab>('list')

const connectionTabs = computed(() => [
  { value: 'list', label: 'Connections' },
  {
    value: 'requests',
    label: 'Requests',
    badge:
      connections.incomingRequests.value.length > 0
        ? connections.incomingRequests.value.length
        : undefined
  }
])

connections.loadConnections()

function partnerName(connection: Parameters<typeof getConnectionPartner>[0]) {
  if (auth.user.id === null) return ''
  const partner = getConnectionPartner(connection, auth.user.id)
  return `${partner.displayName} (@${partner.tag})`
}
</script>

<template>
  <ResponsiveTabs
    v-model="activeTab"
    :tabs="connectionTabs"
    aria-label="Connections sections"
  />

  <button
    type="button"
    class="btn btn-outline-primary w-100"
    data-bs-toggle="modal"
    data-bs-target="#addConnectionModal"
  >
    <i class="bi bi-person-plus" aria-hidden="true"></i>
    Add connection
  </button>

  <div v-if="connections.isListLoading.value" class="text-center py-3 mt-3">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading connections…</span>
    </div>
  </div>

  <div v-else class="tab-content mt-3">
    <div v-if="activeTab === 'list'" role="tabpanel">
      <p v-if="connections.acceptedConnections.value.length === 0" class="text-muted mb-0">
        No connections yet.
      </p>

      <ul v-else class="list-group list-group-flush">
        <li
          v-for="connection in connections.acceptedConnections.value"
          :key="connection.id"
          class="list-group-item d-flex justify-content-between align-items-center gap-2"
        >
          <span class="text-truncate">{{ partnerName(connection) }}</span>
          <button
            type="button"
            class="btn btn-outline-danger btn-sm flex-shrink-0"
            :disabled="connections.isLoading.value"
            @click="connections.removeConnection(connection)"
          >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
          </button>
        </li>
      </ul>
    </div>

    <div v-else role="tabpanel">
      <section v-if="connections.incomingRequests.value.length > 0" class="mb-4">
        <h2 class="h6 text-muted mb-2">Incoming</h2>
        <ul class="list-group list-group-flush">
          <li
            v-for="connection in connections.incomingRequests.value"
            :key="connection.id"
            class="list-group-item d-flex justify-content-between align-items-center gap-2"
          >
            <span class="text-truncate">{{ partnerName(connection) }}</span>
            <div class="d-flex gap-2 flex-shrink-0">
              <button
                type="button"
                class="btn btn-outline-primary btn-sm"
                :disabled="connections.isLoading.value"
                @click="connections.acceptRequest(connection)"
              >
                Accept
              </button>
              <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                :disabled="connections.isLoading.value"
                @click="connections.rejectRequest(connection)"
              >
                Reject
              </button>
            </div>
          </li>
        </ul>
      </section>

      <section v-if="connections.outgoingRequests.value.length > 0">
        <h2 class="h6 text-muted mb-2">Outgoing</h2>
        <ul class="list-group list-group-flush">
          <li
            v-for="connection in connections.outgoingRequests.value"
            :key="connection.id"
            class="list-group-item d-flex justify-content-between align-items-center gap-2"
          >
            <span class="text-truncate">{{ partnerName(connection) }}</span>
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm flex-shrink-0"
              :disabled="connections.isLoading.value"
              @click="connections.cancelRequest(connection)"
            >
              Cancel
            </button>
          </li>
        </ul>
      </section>

      <p
        v-if="
          connections.incomingRequests.value.length === 0 &&
          connections.outgoingRequests.value.length === 0
        "
        class="text-muted mb-0"
      >
        No pending requests.
      </p>
    </div>
  </div>

  <AddConnectionModal
    :exclude-user-ids="
      () =>
        auth.user.id === null
          ? []
          : connections.connections.value.map((connection) =>
              getConnectionPartner(connection, auth.user.id!).id
            )
    "
    @send="connections.sendRequest"
  />
</template>
