<script setup lang="ts">
import { ref } from 'vue'
import UserSearch from '@/modules/core/components/UserSearch.vue'
import type { ApiUser } from '@/modules/core/apiType'

const emit = defineEmits<{
  send: [user: ApiUser]
}>()

const selectedUsers = ref<ApiUser[]>([])

function sendSelected() {
  const user = selectedUsers.value[0]
  if (!user) return

  emit('send', user)
  selectedUsers.value = []
}
</script>

<template>
  <div id="addConnectionModal" class="modal fade" tabindex="-1" aria-labelledby="addConnectionModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="addConnectionModalLabel" class="modal-title fs-5">Add connection</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <UserSearch
            v-model:selected-users="selectedUsers"
            :exclude-self="false"
            single-select
            validation-id="connections-search-validation"
          />
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-primary"
            :disabled="selectedUsers.length === 0"
            @click="sendSelected"
          >
            Request
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
