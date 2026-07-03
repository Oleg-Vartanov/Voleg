<script setup lang="ts">
import { inject } from 'vue'
import UserSearch from '@/modules/core/components/UserSearch.vue'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import type { ApiUser } from '@/modules/core/apiType'

const tables = inject<Tables>('tables')!
const vs = inject<Versus>('vs')!
const auth = useAuth()
const topAlerts = useTopAlerts()

function addUser(user: ApiUser) {
  if (auth.user.id === user.id) {
    topAlerts.add("It's you :)", 'info')
    return
  }
  vs.addUser(user)
  tables.updateLoadedTables()
}
</script>

<template>
  <div id="go" class="modal fade" tabindex="-1" aria-labelledby="vsModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="vsModalLabel" class="modal-title fs-5">Versus</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <ul v-if="vs.users.value.length > 0" class="list-group list-group-flush mb-3">
            <li
              v-for="user in vs.users.value"
              :key="user.id"
              class="list-group-item d-flex justify-content-between align-items-center gap-2"
            >
              <span class="text-truncate">{{ user.displayName }} (@{{ user.tag }})</span>
              <button
                type="button"
                class="btn btn-outline-danger btn-sm flex-shrink-0"
                :aria-label="`Remove ${user.displayName}`"
                @click="vs.removeUser(user)"
              >
                Remove
              </button>
            </li>
          </ul>

          <UserSearch
            action-label="Add"
            :exclude-user-ids="() => vs.users.value.map((user) => user.id)"
            validation-id="go-vs-validation"
            @action="addUser"
          />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
