<script setup lang="ts">
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { searchConnectedUsers } from '@/modules/splitExpense/api/searchConnectedUsers.ts'
import type { ExpenseFormState } from '@/modules/splitExpense/composables/useExpenseForm.ts'

const props = defineProps<{
  form: ExpenseFormState
  idPrefix: string
}>()

const {
  fields,
  amountDecimalPlaces,
  validation,
  isLoading,
  splitUsersSelected,
  splitUsersLocked,
  paidByUserId,
  paidBySelectOptions,
  currencyOptions,
  categoryOptions
} = props.form

function fieldId(name: string): string {
  return `${props.idPrefix}-${name}`
}
</script>

<template>
  <div v-if="isLoading" class="d-flex justify-content-center py-4">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading form…</span>
    </div>
  </div>

  <template v-else>
    <FormInput
      :id="fieldId('title')"
      v-model="fields.title"
      label="Title"
      :is-valid="validation.isValid('title')"
      :error-text="validation.getError('title')"
    />

    <FormSelect
      :id="fieldId('currency')"
      v-model="fields.currencyId"
      label="Currency"
      :options="currencyOptions"
      :is-valid="validation.isValid('currencyId')"
      :error-text="validation.getError('currencyId')"
    />

    <FormInputAmount
      :id="fieldId('amount')"
      v-model="fields.amount"
      label="Amount"
      :decimal-places="amountDecimalPlaces"
      :is-valid="validation.isValid('amount')"
      :error-text="validation.getError('amount')"
    />

    <FormInput
      :id="fieldId('date')"
      v-model="fields.expenseDate"
      type="date"
      label="Date"
      :is-valid="validation.isValid('expenseDate')"
      :error-text="validation.getError('expenseDate')"
    />

    <FormSelect
      :id="fieldId('category')"
      v-model="fields.categoryId"
      label="Category"
      :options="categoryOptions"
      :is-valid="validation.isValid('categoryId')"
      :error-text="validation.getError('categoryId')"
    />

    <FormSelectUsers
      :id="fieldId('split-with')"
      v-model="splitUsersSelected"
      multiple
      label="Split equally with"
      :search="searchConnectedUsers"
      :locked-users="splitUsersLocked"
      :is-valid="validation.isValid('splits')"
      :error-text="validation.getError('splits')"
    />

    <FormSelect
      :id="fieldId('paid-by')"
      v-model="paidByUserId"
      label="Paid by"
      :options="paidBySelectOptions"
      :is-valid="validation.isValid('paidByUserId')"
      :error-text="validation.getError('paidByUserId')"
    />

    <FormTextarea
      :id="fieldId('description')"
      v-model="fields.description"
      label="Description"
      :required="false"
      :is-valid="validation.isValid('description')"
      :error-text="validation.getError('description')"
    />
  </template>
</template>
