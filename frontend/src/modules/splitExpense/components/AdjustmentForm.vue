<script setup lang="ts">
import { computed } from 'vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import FormSelectUsers from '@/modules/core/components/form/FormSelectUsers.vue'
import { searchConnectedUsers } from '@/modules/splitExpense/api/searchConnectedUsers.ts'
import type { AdjustmentFormState } from '@/modules/splitExpense/composables/useAdjustmentForm.ts'

const props = defineProps<{
  form: AdjustmentFormState
  idPrefix: string
}>()

const {
  fields,
  amountDecimalPlaces,
  validation,
  isLoading,
  isEditing,
  otherUserError,
  otherUserIsValid,
  currencyOptions
} = props.form

const otherUserLabel = computed(() => (fields.otherUser ? `@${fields.otherUser.username}` : ''))

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
      v-if="isEditing"
      :id="fieldId('with')"
      :model-value="otherUserLabel"
      label="With"
      disabled
      :required="false"
    />
    <FormSelectUsers
      v-else
      :id="fieldId('with')"
      v-model="fields.otherUser"
      label="With"
      :search="searchConnectedUsers"
      :is-valid="otherUserIsValid"
      :error-text="otherUserError ?? ''"
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
      signed
      help-text="Positive: adds to your balance. Negative: subtracts from it."
      :decimal-places="amountDecimalPlaces"
      :is-valid="validation.isValid('amount')"
      :error-text="validation.getError('amount')"
    />

    <FormInput
      :id="fieldId('date')"
      v-model="fields.adjustmentDate"
      type="date"
      label="Date"
      :is-valid="validation.isValid('adjustmentDate')"
      :error-text="validation.getError('adjustmentDate')"
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
