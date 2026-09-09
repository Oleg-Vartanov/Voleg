<script setup lang="ts">
import { watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import AdjustmentForm from '@/modules/splitExpense/components/AdjustmentForm.vue'
import { useAdjustmentForm } from '@/modules/splitExpense/composables/useAdjustmentForm.ts'

const open = defineModel<boolean>('open', { required: true })

const emit = defineEmits<{ created: [] }>()

const form = useAdjustmentForm()

function close() {
  open.value = false
}

async function submit() {
  if (await form.submit()) {
    emit('created')
    close()
  }
}

watch(open, (isOpen) => {
  if (isOpen) {
    form.load()
  }
})
</script>

<template>
  <AppModal v-model:open="open" title="Add Adjustment">
    <div class="modal-body">
      <form id="addAdjustmentForm" @submit.prevent="submit">
        <AdjustmentForm :form="form" id-prefix="adjustment" />
      </form>
    </div>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
      <button
        type="submit"
        form="addAdjustmentForm"
        class="btn btn-primary"
        :disabled="form.isLoading.value"
      >
        Add adjustment
      </button>
    </template>
  </AppModal>
</template>
