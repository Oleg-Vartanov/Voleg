<script setup lang="ts">
import UserSearch from '@/modules/core/components/UserSearch.vue'
import type { ApiUser } from '@/modules/core/apiType'

const emit = defineEmits<{
  send: [user: ApiUser]
}>()
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
            action-label="Request"
            :exclude-self="false"
            validation-id="connections-search-validation"
            @action="emit('send', $event)"
          />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
