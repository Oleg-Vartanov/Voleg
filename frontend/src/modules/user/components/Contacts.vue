<script setup lang="ts">
import AddContactModal from '@/modules/user/components/AddContactModal.vue'
import { useContacts } from '@/modules/user/composables/useContacts'

const contacts = useContacts();

contacts.loadContacts();
</script>

<template>
  <h1 class="h4 mb-4 fw-normal">Contacts</h1>

  <button
    type="button"
    class="btn btn-outline-primary w-100 mb-3"
    data-bs-toggle="modal"
    data-bs-target="#addContactModal"
  >
    Add contact
  </button>

  <p v-if="contacts.isListLoading.value" class="text-muted mb-0">Loading contacts…</p>
  <p v-else-if="contacts.users.value.length === 0" class="text-muted mb-0">No contacts yet.</p>

  <ul v-else class="list-group list-group-flush">
    <li
      v-for="contact in contacts.users.value"
      :key="contact.id"
      class="list-group-item d-flex justify-content-between align-items-center gap-2"
    >
      <span class="text-truncate">{{ contact.displayName }} (@{{ contact.tag }})</span>
      <button
        type="button"
        class="btn btn-outline-danger btn-sm flex-shrink-0"
        :disabled="contacts.isLoading.value"
        :aria-label="`Remove ${contact.displayName}`"
        @click="contacts.removeContact(contact)"
      >
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </li>
  </ul>

  <AddContactModal
    v-model:search-tag="contacts.searchTag.value"
    :search-users="contacts.searchUsers.value"
    :search-error="contacts.searchError.value"
    :is-loading="contacts.isLoading.value"
    @search="contacts.searchUser"
    @add="contacts.addContact"
  />
</template>
