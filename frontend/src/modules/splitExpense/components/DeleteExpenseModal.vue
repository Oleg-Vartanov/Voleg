<script setup lang="ts">
import AppModal from '@/modules/core/components/AppModal.vue'
import type { ApiSeExpense } from '@/modules/splitExpense/types'

const open = defineModel<boolean>('open', { required: true })

defineProps<{
  expense: ApiSeExpense | null
  deleting?: boolean
}>()

const emit = defineEmits<{
  confirm: []
}>()
</script>

<template>
  <AppModal v-model:open="open" title="Delete expense">
    <div class="modal-body">
      <p class="mb-0">
        Delete
        <strong v-if="expense">{{ expense.title }}</strong>
        <template v-else>this expense</template>? This cannot be undone.
      </p>
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" :disabled="deleting" @click="close">
        Cancel
      </button>
      <button
        type="button"
        class="btn btn-danger"
        :disabled="deleting || !expense"
        @click="emit('confirm')"
      >
        <span v-if="deleting" class="spinner-border spinner-border-sm me-2" role="status"></span>
        Delete
      </button>
    </template>
  </AppModal>
</template>
