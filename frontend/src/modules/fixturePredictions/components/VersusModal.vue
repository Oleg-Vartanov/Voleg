<script setup lang="ts">
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { inject } from 'vue'

const tables = inject<Tables>('tables')!
const vs = inject<Versus>('vs')!
const auth = useAuth()
const topAlerts = useTopAlerts()

function addUser(user) {
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
          <ul class="list-group list-group-flush mb-3">
            <li
              v-for="user in vs.users.value"
              :key="user.id"
              class="vs-user list-group-item list-group-item-action"
              @click="vs.removeUser(user)"
            >
              {{ user.displayName }} (@{{ user.tag }})
              <i class="bi bi-x-lg text-danger" style="font-size: 20px"></i>
            </li>
          </ul>

          <div class="input-group mb-3 has-validation">
            <span id="addon-wrapping" class="input-group-text p-2">User Tag</span>
            <input
              v-model="vs.input.value.value"
              type="text"
              :class="[vs.input.value.error === '' ? '' : 'is-invalid']"
              class="form-control"
              aria-describedby="go-vs validation-go-vs"
              @keyup.enter="
                vs.input.value.value === '' || vs.isLoading.value ? '' : vs.searchUser()
              "
            />
            <button
              id="go-vs"
              :disabled="vs.input.value.value === '' || vs.isLoading.value"
              class="btn btn btn-outline-primary rounded-end p-2 me-2"
              type="button"
              @click="vs.searchUser()"
            >
              Search
            </button>
            <div id="validation-go-vs" class="invalid-feedback">{{ vs.input.value.error }}</div>
            <button
              type="button"
              class="btn btn btn-outline-primary rounded p-2"
              :disabled="vs.isLoading.value"
              @click="vs.showContacts()"
            >
              Contacts
            </button>
          </div>

          <div v-if="vs.isLoading.value" class="spinner-border text-primary mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>

          <ul class="list-group list-group-flush">
            <li
              v-for="user in vs.searchUsers.value"
              :key="user.id"
              class="vs-user list-group-item list-group-item-action"
              @click="addUser(user)"
            >
              {{ user.displayName }} (@{{ user.tag }})
              <i class="bi bi-person-plus text-primary" style="font-size: 20px"></i>
            </li>
          </ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.vs-user {
  cursor: pointer;
}
</style>
