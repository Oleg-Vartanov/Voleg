<script setup lang="ts">
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import  { useCreateExpense } from '@/modules/splitExpense/composables/useCreateExpense'
import type { Expenses } from '@/modules/splitExpense/composables/useExpenses.ts';
import { onMounted } from 'vue';

const { expenses } = defineProps<{
  expenses: Expenses
}>()

const form = useCreateExpense()

function submit() {
  form.submit().then(() => {
    expenses.load()
  })
}

onMounted(() => {
  form.loadOptions()
})
</script>

<template>
  <div id="addExpenseModal" class="modal fade" tabindex="-1" aria-labelledby="addExpenseModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h1 id="addExpenseModalLabel" class="modal-title fs-5">Add Expense</h1>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body">
          <form id="addExpenseForm" @submit.prevent="submit">
            <div v-if="form.ui.isOptionsLoading" class="d-flex justify-content-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading form…</span>
              </div>
            </div>

            <template v-else>
              <FormInput
                id="expense-title"
                v-model="form.fields.title"
                label="Title"
                v-bind="form.fieldAttrs('title')"
              />

              <FormSelect
                id="expense-currency"
                v-model="form.fields.currencyId"
                label="Currency"
                :options="form.currencyOptions"
                v-bind="form.fieldAttrs('currencyId')"
              />

              <FormInputAmount
                id="expense-amount"
                v-model="form.fields.amount"
                label="Amount"
                :decimal-places="form.amountDecimalPlaces"
                v-bind="form.fieldAttrs('amount')"
              />

              <FormInput
                id="expense-date"
                v-model="form.fields.expenseDate"
                type="date"
                label="Date"
                v-bind="form.fieldAttrs('expenseDate')"
              />

              <FormSelect
                id="expense-category"
                v-model="form.fields.categoryId"
                label="Category"
                :options="form.categoryOptions"
                v-bind="form.fieldAttrs('categoryId')"
              />

              <FormSelect
                id="expense-paid-by"
                v-model="form.fields.paidByUserId"
                label="Paid by"
                :options="form.payerSelectOptions"
                v-bind="form.fieldAttrs('paidByUserId')"
              />

              <FormTextarea
                id="expense-description"
                v-model="form.fields.description"
                label="Description"
                :required="false"
                v-bind="form.fieldAttrs('description')"
              />

              <fieldset class="mb-0">
                <legend class="form-label fs-6 mb-2">Split equally with</legend>
                <div class="form-check">
                  <input
                    id="expense-split-self"
                    class="form-check-input"
                    type="checkbox"
                    checked
                    disabled
                  />
                  <label class="form-check-label" for="expense-split-self">You</label>
                </div>
                <div
                  v-for="partner in form.connectionPartners"
                  :key="partner.id"
                  class="form-check"
                >
                  <input
                    :id="`expense-split-${partner.id}`"
                    class="form-check-input"
                    type="checkbox"
                    :checked="form.ui.splitWithUserIds.includes(partner.id)"
                    @change="
                      form.toggleSplitWith(partner.id, ($event.target as HTMLInputElement).checked)
                    "
                  />
                  <label class="form-check-label" :for="`expense-split-${partner.id}`">
                    {{ partner.displayName }}
                  </label>
                </div>
                <p v-if="form.connectionPartners.length === 0" class="form-text mb-0">
                  Add contacts to split expenses with others.
                </p>
                <div
                  v-if="form.validation.isValid('splits') === false"
                  class="invalid-feedback d-block"
                >
                  {{ form.validation.getError('splits') }}
                </div>
              </fieldset>
            </template>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button
            type="submit"
            form="addExpenseForm"
            class="btn btn-primary"
            :disabled="form.ui.isOptionsLoading || form.ui.isSubmitting"
          >
            <span
              v-if="form.ui.isSubmitting"
              class="spinner-border spinner-border-sm me-1"
              role="status"
              aria-hidden="true"
            ></span>
            Create expense
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
