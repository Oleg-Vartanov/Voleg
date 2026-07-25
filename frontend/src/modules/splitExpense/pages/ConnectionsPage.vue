<script setup lang="ts">
import AddConnectionModal from '@/modules/splitExpense/components/AddConnectionModal.vue'
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
    <div class="container d-flex flex-column align-items-center gap-2">
      <div class="se-panel">
        <button
          type="button"
          class="btn btn-outline-primary w-100"
          data-bs-toggle="modal"
          data-bs-target="#addConnectionModal"
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
                Remove
              </button>
            </li>
          </ul>
        </div>

        <AddConnectionModal @send="connections.sendRequest" />
      </div>
    </div>
  </div>
</template>
