<script setup lang="ts">
import { ref } from 'vue'
import { useConnections } from '@/modules/splitExpense/composables/useConnections'
import { getConnectionPartnerName } from '@/modules/splitExpense/utils/connections'
import { useAuth } from '@/modules/user/stores/useAuth'

const auth = useAuth()
const connections = useConnections()
const showRejected = ref(false)

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
        <div v-if="connections.isListLoading.value" class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading requests…</span>
          </div>
        </div>

        <div v-else>
          <section>
            <h2 class="h6 text-muted mb-2">Incoming</h2>
            <p v-if="connections.incomingRequests.value.length === 0" class="text-muted mb-0">
              No incoming requests.
            </p>
            <ul v-else class="list-group list-group-flush">
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

          <hr class="my-4" />

          <section>
            <h2 class="h6 text-muted mb-2">Outgoing</h2>
            <p v-if="connections.outgoingRequests.value.length === 0" class="text-muted mb-0">
              No outgoing requests.
            </p>
            <ul v-else class="list-group list-group-flush">
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

          <hr class="my-4" />

          <section>
            <h2 class="h6 text-muted mb-2">Rejected</h2>
            <button
              v-if="!showRejected"
              type="button"
              class="btn btn-link btn-sm p-0 mb-2 text-decoration-none"
              @click="showRejected = true"
            >
              Show rejected
            </button>
            <template v-else>
              <button
                type="button"
                class="btn btn-link btn-sm p-0 mb-2 text-decoration-none"
                @click="showRejected = false"
              >
                Hide rejected
              </button>
              <p v-if="connections.rejectedConnections.value.length === 0" class="text-muted mb-0">
                No rejected requests.
              </p>
              <ul v-else class="list-group list-group-flush">
                <li
                  v-for="connection in connections.rejectedConnections.value"
                  :key="connection.id"
                  class="list-group-item d-flex justify-content-between align-items-center gap-2"
                >
                  <span class="text-truncate">{{ partnerName(connection) }}</span>
                  <button
                    v-if="connection.requestedBy.id !== auth.user.id"
                    type="button"
                    class="btn btn-outline-secondary btn-sm flex-shrink-0"
                    :disabled="connections.isLoading.value"
                    @click="connections.removeConnection(connection)"
                  >
                    Cancel
                  </button>
                </li>
              </ul>
            </template>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>
