<script setup lang="ts">
import { watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import ExpenseForm from '@/modules/splitExpense/components/ExpenseForm.vue'
import { useExpenseForm } from '@/modules/splitExpense/composables/useExpenseForm.ts'
import type { ApiSeExpense } from '@/modules/splitExpense/types'

const open = defineModel<boolean>('open', { required: true })

const props = defineProps<{
  expense: ApiSeExpense | null
}>()

const emit = defineEmits<{ updated: [expense: ApiSeExpense] }>()

const form = useExpenseForm()

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
  if (isOpen && props.expense) {
    form.loadForEdit(props.expense)
  }
})
</script>

<template>
  <AppModal v-model:open="open" title="Edit Expense">
    <div class="modal-body">
      <form id="editExpenseForm" @submit.prevent="submit">
        <ExpenseForm :form="form" id-prefix="edit-expense" />
      </form>
    </div>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
      <button
        type="submit"
        form="editExpenseForm"
        class="btn btn-primary"
        :disabled="form.isLoading.value"
      >
        Save changes
      </button>
    </template>
  </AppModal>
</template>
