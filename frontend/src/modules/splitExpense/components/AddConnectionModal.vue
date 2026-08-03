<script setup lang="ts">
import { ref, watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import SelectUsersAction from '@/modules/core/components/SelectUsersAction.vue'
import type { ApiUser } from '@/modules/core/apiType'

const open = defineModel<boolean>('open', { required: true })

const props = defineProps<{
  send: (user: ApiUser) => Promise<{ ok: true } | { ok: false; message: string | null }>
}>()

const error = ref<string | null>(null)

watch(open, (isOpen) => {
  if (isOpen) {
    error.value = null
  }
})

async function onSend(user: ApiUser) {
  error.value = null
  const result = await props.send(user)
  if (result.ok) {
    open.value = false
    return
  }
  if (result.message) {
    error.value = result.message
  }
}
</script>

<template>
  <AppModal v-model:open="open" title="Add connection" :error="error">
    <div class="modal-body">
      <SelectUsersAction
        action-label="Request"
        @action="onSend"
      />
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" @click="close">Close</button>
    </template>
  </AppModal>
</template>
