<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useUserSearch } from '@/modules/core/composables/useUserSearch'
import type { ApiUser } from '@/modules/core/apiType'
import { useAuth } from '@/modules/user/stores/useAuth'

const PAGE_SIZE = 5

const props = withDefaults(
  defineProps<{
    excludeUserIds?: () => number[]
    validationId?: string
    excludeSelf?: boolean
    users?: ApiUser[]
    embedded?: boolean
    actionLabel?: string
  }>(),
  {
    excludeUserIds: () => [],
    validationId: 'user-search-validation',
    excludeSelf: true,
    embedded: false,
    actionLabel: 'Select',
  }
)

const emit = defineEmits<{
  action: [user: ApiUser]
}>()

const isLocalMode = computed(() => props.users !== undefined)
const auth = useAuth()
const localSearchUsername = ref('')

const {
  users: apiUsers,
  searchUsername: apiSearchUsername,
  searchError: apiSearchError,
  isLoading: apiIsLoading,
} = useUserSearch(
  () => props.excludeUserIds(),
  props.excludeSelf,
  !isLocalMode.value
)

const searchQuery = computed({
  get() {
    return isLocalMode.value ? localSearchUsername.value : apiSearchUsername.value
  },
  set(value: string) {
    if (isLocalMode.value) {
      localSearchUsername.value = value
      return
    }

    apiSearchUsername.value = value
  },
})

function excludedUserIds(): Set<number> {
  return new Set(
    [
      ...(props.excludeSelf && auth.user.id !== null ? [auth.user.id] : []),
      ...props.excludeUserIds(),
    ].filter((id): id is number => id !== null)
  )
}

const localFilteredUsers = computed(() => {
  const excluded = excludedUserIds()
  let filtered = (props.users ?? []).filter((user) => !excluded.has(user.id))

  const query = localSearchUsername.value.trim().toLowerCase()
  if (query !== '') {
    filtered = filtered.filter((user) =>
      user.username.toLowerCase().includes(query)
    )
  }

  return filtered
})

const displayUsers = computed(() =>
  isLocalMode.value ? localFilteredUsers.value : apiUsers.value
)

const isLoading = computed(() => (isLocalMode.value ? false : apiIsLoading.value))

const searchError = computed(() => (isLocalMode.value ? '' : apiSearchError.value))

const currentPage = ref(1)

const totalPages = computed(() => Math.max(1, Math.ceil(displayUsers.value.length / PAGE_SIZE)))

const pagedUsers = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return displayUsers.value.slice(start, start + PAGE_SIZE)
})

function onAction(user: ApiUser) {
  emit('action', user)
}

function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

watch(
  () => displayUsers.value.map((user) => user.id).join(','),
  () => {
    currentPage.value = 1
  }
)

watch(totalPages, (pages) => {
  if (currentPage.value > pages) {
    currentPage.value = pages
  }
})
</script>

<template>
  <div class="has-validation" :class="{ 'mb-3': !embedded }">
    <input
      v-model="searchQuery"
      type="text"
      class="form-control"
      :class="{ 'is-invalid': !isLocalMode && searchError !== '' }"
      placeholder="Search by username"
      :aria-describedby="validationId"
    />
    <div :id="validationId" class="invalid-feedback">{{ searchError }}</div>
  </div>

  <div v-if="isLoading" class="spinner-border spinner-border-sm text-primary" role="status">
    <span class="visually-hidden">Loading…</span>
  </div>

  <p v-else-if="displayUsers.length === 0" class="text-muted mb-0">No users found.</p>

  <template v-else>
    <ul class="list-group list-group-flush">
      <li
        v-for="user in pagedUsers"
        :key="user.id"
        class="list-group-item d-flex justify-content-between align-items-center gap-2"
      >
        <span class="text-truncate">@{{ user.username }}</span>
        <button
          type="button"
          class="btn btn-outline-primary btn-sm flex-shrink-0"
          :aria-label="`${actionLabel} ${user.username}`"
          @click="onAction(user)"
        >
          {{ actionLabel }}
        </button>
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
