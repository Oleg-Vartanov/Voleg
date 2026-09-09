<script setup lang="ts">
import { watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import AdjustmentForm from '@/modules/splitExpense/components/AdjustmentForm.vue'
import { useAdjustmentForm } from '@/modules/splitExpense/composables/useAdjustmentForm.ts'
import type { ApiSeAdjustment } from '@/modules/splitExpense/types'

const open = defineModel<boolean>('open', { required: true })

const props = defineProps<{
  adjustment: ApiSeAdjustment | null
}>()

const emit = defineEmits<{ updated: [adjustment: ApiSeAdjustment] }>()

const form = useAdjustmentForm()

function close() {
  open.value = false
}

async function submit() {
  const updated = await form.submit()
  if (updated) {
    emit('updated', updated)
    close()
  }
}

watch(open, (isOpen) => {
  if (isOpen && props.adjustment) {
    form.loadForEdit(props.adjustment)
  }
})
</script>

<template>
  <AppModal v-model:open="open" title="Edit Adjustment">
    <div class="modal-body">
      <form id="editAdjustmentForm" @submit.prevent="submit">
        <AdjustmentForm :form="form" id-prefix="edit-adjustment" />
      </form>
    </div>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
      <button
        type="submit"
        form="editAdjustmentForm"
        class="btn btn-primary"
        :disabled="form.isLoading.value"
      >
        Save changes
      </button>
    </template>
  </AppModal>
</template>
