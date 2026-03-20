<script setup lang="ts">
import { type HeadToHead } from '@/modules/fixturePredictions/composables/useHeadToHead';
import { type Tables } from '@/modules/fixturePredictions/composables/useTables';
import { useAuth } from '@/modules/user/stores/useAuth';
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts';

const { h2h, tables } = defineProps<{
  tables: Tables;
  h2h: HeadToHead;
}>();

const auth = useAuth();
const topAlerts = useTopAlerts();

function addUser(user) {
  if (auth.user.id === user.id) {
    topAlerts.add("It's you :)", 'info');
    return;
  }
  h2h.addUser(user);
  tables.updateLoadedTables();
}
</script>

<template>
  <div
    class="modal fade"
    id="go"
    tabindex="-1"
    aria-labelledby="h2hModalLabel"
    data-bs-backdrop="static"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="h2hModalLabel">Head To Head</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <ul class="list-group list-group-flush mb-3">
            <li
              v-for="user in h2h.users.value"
              class="h2h-user list-group-item list-group-item-action"
              @click="h2h.removeUser(user)"
            >
              {{ user.displayName }} (@{{ user.tag }}) <i class="bi bi-x-lg text-danger" style="font-size: 20px;"></i>
            </li>
          </ul>

          <div class="input-group mb-3 has-validation">
            <span class="input-group-text rounded-0" id="addon-wrapping">User Tag</span>
            <input v-model="h2h.input.value.value"
                   type="text"
                   :class="[h2h.input.value.error === '' ? '' : 'is-invalid']"
                   class="form-control"
                   aria-describedby="go-h2h validation-go-h2h"
                   @keyup.enter="h2h.input.value.value === '' || h2h.isLoading.value ? '' : h2h.searchUser()">
            <button @click="h2h.searchUser()"
                    :disabled="h2h.input.value.value === '' || h2h.isLoading.value"
                    class="btn btn btn-outline-primary rounded-0"
                    type="button"
                    id="go-h2h">Search
            </button>
            <div id="validation-go-h2h" class="invalid-feedback">{{ h2h.input.value.error }}</div>
          </div>

          <div v-if="h2h.isLoading.value" class="spinner-border text-primary mt-3" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>

          <ul class="list-group list-group-flush">
            <li
              v-for="user in h2h.searchUsers.value"
              class="h2h-user list-group-item list-group-item-action"
              @click="addUser(user);"
            >
              {{ user.displayName }} (@{{ user.tag }})
              <i class="bi bi-person-plus text-primary" style="font-size: 20px;"></i>
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
.h2h-user {
  cursor: pointer;
}
</style>