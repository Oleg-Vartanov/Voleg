<script setup lang="ts">
import { computed, inject } from 'vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { searchUsers } from '@/modules/core/api/searchUsers'
import type { ApiUser } from '@/modules/core/apiType'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'

const tables = inject<Tables>('tables')!
const vs = inject<Versus>('vs')!

const versusUsers = computed({
  get: () => vs.users.value,
  set: (value: ApiUser[] | ApiUser | null) => {
    vs.setUsers(Array.isArray(value) ? value : [])
    tables.updateLoadedTables()
  },
})
</script>

<template>
  <div id="go" class="modal fade" tabindex="-1" aria-labelledby="vsModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="vsModalLabel" class="modal-title fs-5">Versus</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <FormSelectUsers
            id="versus-users"
            v-model="versusUsers"
            multiple
            label="Compare with"
            :search="searchUsers"
          />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
