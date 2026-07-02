<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useUserSearch } from '@/modules/core/composables/useUserSearch'
import type { ApiUser } from '@/modules/core/apiType'

const PAGE_SIZE = 5

const props = withDefaults(
  defineProps<{
    excludeUserIds?: () => number[]
    validationId?: string
    singleSelect?: boolean
    excludeSelf?: boolean
  }>(),
  {
    excludeUserIds: () => [],
    validationId: 'user-search-validation',
    singleSelect: false,
    excludeSelf: true,
  }
)

const selectedUsers = defineModel<ApiUser[]>('selectedUsers', { default: () => [] })

const { users, searchTag, searchError, isLoading, searchUser } = useUserSearch(
  () => props.excludeUserIds(),
  props.excludeSelf,
)

const currentPage = ref(1)

const totalPages = computed(() => Math.max(1, Math.ceil(users.value.length / PAGE_SIZE)))

const pagedUsers = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return users.value.slice(start, start + PAGE_SIZE)
})

function isSelected(user: ApiUser) {
  return selectedUsers.value.some((selected) => selected.id === user.id)
}

function toggleUser(user: ApiUser) {
  if (isSelected(user)) {
    selectedUsers.value = selectedUsers.value.filter((selected) => selected.id !== user.id)
    return
  }

  if (props.singleSelect) {
    selectedUsers.value = [user]
    return
  }

  selectedUsers.value = [...selectedUsers.value, user]
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

watch(
  () => users.value.map((user) => user.id).join(','),
  () => {
    currentPage.value = 1
  }
)

watch(totalPages, (pages) => {
  if (currentPage.value > pages) {
    currentPage.value = pages
  }
})

watch(
  () => props.excludeUserIds().join(','),
  (joined) => {
    const excluded = new Set(joined === '' ? [] : joined.split(',').map(Number))
    selectedUsers.value = selectedUsers.value.filter((user) => !excluded.has(user.id))
  }
)
</script>

<template>
  <div class="input-group mb-3 has-validation">
    <span class="input-group-text p-2">User Tag</span>
    <input
      v-model="searchTag"
      type="text"
      class="form-control"
      :class="{ 'is-invalid': searchError !== '' }"
      placeholder="Search by tag"
      :aria-describedby="validationId"
      @keyup.enter="searchTag !== '' && !isLoading && searchUser()"
    />
    <button
      class="btn btn-outline-primary p-2"
      type="button"
      :disabled="searchTag === '' || isLoading"
      @click="searchUser()"
    >
      Search
    </button>
    <div :id="validationId" class="invalid-feedback">{{ searchError }}</div>
  </div>

  <div v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status">
    <span class="visually-hidden">Loading…</span>
  </div>

  <p v-else-if="users.length === 0" class="text-muted mb-0">No users found.</p>

  <template v-else>
    <ul class="list-group list-group-flush">
      <li v-for="user in pagedUsers" :key="user.id" class="list-group-item p-0">
        <label class="d-flex align-items-center gap-2 user-search-row mb-0 px-3 py-2 w-100">
          <input
            class="form-check-input flex-shrink-0 mt-0"
            :type="singleSelect ? 'radio' : 'checkbox'"
            :name="singleSelect ? `${validationId}-user` : undefined"
            :checked="isSelected(user)"
            @change="toggleUser(user)"
          />
          <span class="text-truncate">{{ user.displayName }} (@{{ user.tag }})</span>
        </label>
      </li>
    </ul>

    <nav v-if="totalPages > 1" class="mt-2" :aria-label="`${validationId}-pagination`">
      <ul class="pagination pagination-sm justify-content-center mb-0">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button
            type="button"
            class="page-link"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            Previous
          </button>
        </li>
        <li
          v-for="page in totalPages"
          :key="page"
          class="page-item"
          :class="{ active: currentPage === page }"
        >
          <button type="button" class="page-link" @click="goToPage(page)">
            {{ page }}
          </button>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button
            type="button"
            class="page-link"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            Next
          </button>
        </li>
      </ul>
    </nav>
  </template>
</template>

<style scoped>
.user-search-row {
  cursor: pointer;
}

.user-search-row:hover {
  background-color: var(--bs-list-group-action-hover-bg);
  color: var(--bs-list-group-action-hover-color);
}
</style>
