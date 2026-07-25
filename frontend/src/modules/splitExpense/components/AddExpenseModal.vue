<script setup lang="ts">
import { watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { useAddExpenseForm } from '@/modules/splitExpense/composables/useAddExpenseForm.ts'
import { searchConnectedUsers } from '@/modules/splitExpense/api/searchConnectedUsers.ts'

const open = defineModel<boolean>('open', { required: true })

const emit = defineEmits<{ created: [] }>()

const {
  fields,
  amountDecimalPlaces,
  validation,
  isLoading,
  load,
  splitUsersSelected,
  splitUsersLocked,
  paidByUserId,
  paidBySelectOptions,
  submit: submitExpense,
  currencyOptions,
  categoryOptions,
} = useAddExpenseForm()

function close() {
  open.value = false
}

async function submit() {
  if (await submitExpense()) {
    emit('created')
    close()
  }
}

watch(open, (isOpen) => {
  if (isOpen) {
    load()
  }
})
</script>

<template>
  <AppModal v-model:open="open" title="Add Expense">
    <div class="modal-body">
      <form id="addExpenseForm" @submit.prevent="submit">
        <div v-if="isLoading" class="d-flex justify-content-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading form…</span>
          </div>
        </div>

        <template v-else>
          <FormInput
            id="expense-title"
            v-model="fields.title"
            label="Title"
            :is-valid="validation.isValid('title')"
            :error-text="validation.getError('title')"
          />

          <FormSelect
            id="expense-currency"
            v-model="fields.currencyId"
            label="Currency"
            :options="currencyOptions"
            :is-valid="validation.isValid('currencyId')"
            :error-text="validation.getError('currencyId')"
          />

          <FormInputAmount
            id="expense-amount"
            v-model="fields.amount"
            label="Amount"
            :decimal-places="amountDecimalPlaces"
            :is-valid="validation.isValid('amount')"
            :error-text="validation.getError('amount')"
          />

          <FormInput
            id="expense-date"
            v-model="fields.expenseDate"
            type="date"
            label="Date"
            :is-valid="validation.isValid('expenseDate')"
            :error-text="validation.getError('expenseDate')"
          />

          <FormSelect
            id="expense-category"
            v-model="fields.categoryId"
            label="Category"
            :options="categoryOptions"
            :is-valid="validation.isValid('categoryId')"
            :error-text="validation.getError('categoryId')"
          />

          <FormSelectUsers
            id="expense-split-with"
            v-model="splitUsersSelected"
            multiple
            label="Split equally with"
            :search="searchConnectedUsers"
            :locked-users="splitUsersLocked"
            :is-valid="validation.isValid('splits')"
            :error-text="validation.getError('splits')"
          />

          <FormSelect
            id="expense-paid-by"
            v-model="paidByUserId"
            label="Paid by"
            :options="paidBySelectOptions"
            :is-valid="validation.isValid('paidByUserId')"
            :error-text="validation.getError('paidByUserId')"
          />

          <FormTextarea
            id="expense-description"
            v-model="fields.description"
            label="Description"
            :required="false"
            :is-valid="validation.isValid('description')"
            :error-text="validation.getError('description')"
          />
        </template>
      </form>
    </div>

    <template #footer>
      <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
      <button
        type="submit"
        form="addExpenseForm"
        class="btn btn-primary"
        :disabled="isLoading"
      >
        Add expense
      </button>
    </template>
  </AppModal>
</template>
