<script setup lang="ts">
import type { ApiContact } from '@/modules/core/apiType'

defineProps<{
  searchTag: string
  searchUsers: ApiContact[]
  searchError: string
  isLoading: boolean
}>()

const emit = defineEmits<{
  'update:searchTag': [value: string]
  search: []
  add: [user: ApiContact]
}>()
</script>

<template>
  <div
    id="addContactModal"
    class="modal fade"
    tabindex="-1"
    aria-labelledby="addContactModalLabel"
  >
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
          <div class="input-group mb-3 has-validation">
            <span class="input-group-text rounded-0">User Tag</span>
            <input
              :value="searchTag"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': searchError !== '' }"
              placeholder="Search by tag"
              aria-describedby="contacts-search-validation"
              @input="emit('update:searchTag', ($event.target as HTMLInputElement).value)"
              @keyup.enter="searchTag !== '' && !isLoading && emit('search')"
            />
            <button
              class="btn btn-outline-primary rounded-0"
              type="button"
              :disabled="searchTag === '' || isLoading"
              @click="emit('search')"
            >
              Search
            </button>
            <div id="contacts-search-validation" class="invalid-feedback">{{ searchError }}</div>
          </div>

          <div v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading…</span>
          </div>

          <ul v-else-if="searchUsers.length > 0" class="list-group list-group-flush">
            <li
              v-for="user in searchUsers"
              :key="user.id"
              class="list-group-item d-flex justify-content-between align-items-center gap-2"
            >
              <span class="text-truncate">{{ user.displayName }} (@{{ user.tag }})</span>
              <button
                type="button"
                class="btn btn-outline-primary btn-sm flex-shrink-0"
                :disabled="isLoading"
                @click="emit('add', user)"
              >
                Add
              </button>
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
