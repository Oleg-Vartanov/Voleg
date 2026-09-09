<script setup lang="ts">
import AppModal from '@/modules/core/components/AppModal.vue'
import { useAdjustmentDisplay } from '@/modules/splitExpense/composables/useAdjustmentDisplay'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'

const open = defineModel<boolean>('open', { required: true })

defineProps<{
  adjustment: ApiSeAdjustment | null
  deleting?: boolean
}>()

const emit = defineEmits<{
  confirm: []
}>()

const display = useAdjustmentDisplay()
</script>

<template>
  <AppModal v-model:open="open" title="Delete adjustment">
    <div class="modal-body">
      <p class="mb-0">
        Delete
        <strong v-if="adjustment">{{ display.title(adjustment) }}</strong>
        <template v-else>this adjustment</template>? This cannot be undone.
      </p>
    </div>

    <template #footer="{ close }">
      <button type="button" class="btn btn-secondary" :disabled="deleting" @click="close">
        Cancel
      </button>
      <button
        type="button"
        class="btn btn-danger"
        :disabled="deleting || !adjustment"
        @click="emit('confirm')"
      >
        <span v-if="deleting" class="spinner-border spinner-border-sm me-2" role="status"></span>
        Delete
      </button>
    </template>
  </AppModal>
</template>
