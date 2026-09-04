<script setup lang="ts">
import { computed, inject } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { searchUsers } from '@/modules/core/api/searchUsers'
import type { ApiUser } from '@/modules/core/apiType'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'

const open = defineModel<boolean>('open', { required: true })

const tables = inject<Tables>('tables')!
const vs = inject<Versus>('vs')!

const versusUsers = computed({
  get: () => vs.users.value,
  set: (value: ApiUser[] | ApiUser | null) => {
    vs.setUsers(Array.isArray(value) ? value : [])
    tables.updateLoadedTables()
  }
})
</script>

<template>
  <AppModal v-model:open="open" title="Versus">
    <div class="modal-body">
      <FormSelectUsers
        id="versus-users"
        v-model="versusUsers"
        multiple
        label="Compare with"
        :search="searchUsers"
      />
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" @click="close">Close</button>
    </template>
  </AppModal>
</template>
