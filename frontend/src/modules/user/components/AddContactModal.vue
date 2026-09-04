<script setup lang="ts">
import SelectUsersAction from '@/modules/core/components/SelectUsersAction.vue'
import type { ApiUser } from '@/modules/core/apiType'

const emit = defineEmits<{
  add: [user: ApiUser]
}>()
</script>

<template>
  <div id="addContactModal" class="modal fade" tabindex="-1" aria-labelledby="addContactModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="addContactModalLabel" class="modal-title fs-5">Add contact</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <SelectUsersAction action-label="Add" @action="emit('add', $event)" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
