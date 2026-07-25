<script setup lang="ts">
import { ref } from 'vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { searchUsers } from '@/modules/core/api/searchUsers'
import type { UserSearchFn } from '@/modules/core/components/form/types'
import type { ApiUser } from '@/modules/core/apiType'

const props = withDefaults(
  defineProps<{
    id?: string
    label?: string
    actionLabel?: string
    search?: UserSearchFn
    embedded?: boolean
  }>(),
  {
    id: 'user-search',
    label: 'Search users',
    actionLabel: 'Select',
    embedded: false,
  }
)

const emit = defineEmits<{
  action: [user: ApiUser]
}>()

const selectedUser = ref<ApiUser | null>(null)

function onAction(user: ApiUser) {
  emit('action', user)
}
</script>

<template>
  <div :class="{ 'mb-3': !embedded }">
    <FormSelectUsers
      :id="id"
      v-model="selectedUser"
      :label="label"
      :search="props.search ?? searchUsers"
      :show-tags="false"
    />

    <ul v-if="selectedUser" class="list-group list-group-flush">
      <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
        <span class="text-truncate">@{{ selectedUser.username }}</span>
        <button
          type="button"
          class="btn btn-outline-primary btn-sm flex-shrink-0"
          :aria-label="`${actionLabel} ${selectedUser.username}`"
          @click="onAction(selectedUser)"
        >
          {{ actionLabel }}
        </button>
      </li>
    </ul>
  </div>
</template>
