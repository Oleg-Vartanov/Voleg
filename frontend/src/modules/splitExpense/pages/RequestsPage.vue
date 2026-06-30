<script setup lang="ts">
import { useConnections } from '@/modules/splitExpense/composables/useConnections'
import { getConnectionPartnerName } from '@/modules/splitExpense/utils/connections'
import { useAuth } from '@/modules/user/stores/useAuth'

const auth = useAuth()
const connections = useConnections()

connections.loadConnections()

function partnerName(connection: Parameters<typeof getConnectionPartnerName>[0]) {
  if (auth.user.id === null) return ''
  return getConnectionPartnerName(connection, auth.user.id)
}
</script>

<template>
  <div class="ov-center">
    <div class="container d-flex flex-column gap-2">
      <div v-if="connections.isListLoading.value" class="text-center py-3">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading requests…</span>
    </div>
  </div>

  <div v-else>
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
  </div>
</template>
