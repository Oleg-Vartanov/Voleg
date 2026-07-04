<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import FormUserSelect from '@/modules/core/components/form/FormUserSelect.vue'
import UserSearch from '@/modules/core/components/UserSearch.vue'
import type { ApiUser } from '@/modules/core/apiType'
import { useCreateExpense } from '@/modules/splitExpense/composables/useCreateExpense'
import type { Expenses } from '@/modules/splitExpense/composables/useExpenses.ts'

const { expenses } = defineProps<{
  expenses: Expenses
}>()

const form = useCreateExpense()
const isAddParticipantOpen = ref(false)
const splitsIsInvalid = computed(() => form.validation.isValid('splits') === false)

function addParticipant(user: ApiUser) {
  form.addSplitWith(user)
}

function toggleAddParticipant() {
  isAddParticipantOpen.value = !isAddParticipantOpen.value
}

async function submit() {
  if (await form.submit()) {
    expenses.load()
  }
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

              <FormUserSelect
                id="expense-paid-by"
                v-model="form.fields.paidByUserId"
                label="Paid by"
                :users="form.payerOptions"
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

                <div
                  class="split-with-block border rounded overflow-hidden bg-body-tertiary mb-1"
                  :class="{
                    'split-with-block--add-open': isAddParticipantOpen,
                    'split-with-block--invalid': splitsIsInvalid
                  }"
                >
                  <ul class="list-group list-group-flush mb-0">
                    <li
                      v-if="form.selectedPayer"
                      class="list-group-item d-flex justify-content-between align-items-center gap-2"
                    >
                      <span class="text-truncate">
                        {{ form.selectedPayer.displayName }} (@{{ form.selectedPayer.tag }})
                      </span>
                      <span class="badge text-bg-secondary flex-shrink-0">
                        {{ form.selectedPayer.id === form.currentUser?.id ? 'You' : 'Paid by' }}
                      </span>
                    </li>
                    <li
                      v-if="
                        form.currentUser &&
                        form.currentUser.id !== form.selectedPayer?.id
                      "
                      class="list-group-item d-flex justify-content-between align-items-center gap-2"
                    >
                      <span class="text-truncate">
                        {{ form.currentUser.displayName }} (@{{ form.currentUser.tag }})
                      </span>
                      <span class="badge text-bg-secondary flex-shrink-0">You</span>
                    </li>
                    <li
                      v-for="user in form.ui.splitWithUsers"
                      :key="user.id"
                      class="list-group-item d-flex justify-content-between align-items-center gap-2"
                    >
                      <span class="text-truncate">{{ user.displayName }} (@{{ user.tag }})</span>
                      <button
                        type="button"
                        class="btn btn-outline-danger btn-sm flex-shrink-0"
                        @click="form.removeSplitWith(user.id)"
                      >
                        Remove
                      </button>
                    </li>
                  </ul>

                  <div class="split-with-add">
                    <button
                      type="button"
                      class="split-with-add-trigger btn btn-link text-decoration-none w-100 text-start d-flex align-items-center justify-content-between gap-2"
                      :aria-expanded="isAddParticipantOpen"
                      :disabled="form.splitPartnerOptions.length === 0"
                      aria-controls="expense-add-participant"
                      @click="toggleAddParticipant"
                    >
                      <span>Add participant</span>
                      <i
                        class="bi flex-shrink-0"
                        :class="isAddParticipantOpen ? 'bi-chevron-up' : 'bi-chevron-down'"
                        aria-hidden="true"
                      ></i>
                    </button>

                    <div
                      v-if="isAddParticipantOpen"
                      id="expense-add-participant"
                      class="split-with-add-panel"
                    >
                      <UserSearch
                        action-label="Add"
                        :users="form.splitPartnerOptions"
                        :exclude-user-ids="form.splitExcludeUserIds"
                        embedded
                        validation-id="expense-split-validation"
                        @action="addParticipant"
                      />
                    </div>
                  </div>
                </div>

                <p v-if="form.splitPartnerOptions.length === 0" class="form-text mb-0">
                  Add connections to split expenses with others.
                </p>
                <div
                  v-if="splitsIsInvalid"
                  id="expense-split-validation"
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

<style scoped>
.split-with-block--invalid {
  border-color: var(--bs-form-invalid-border-color);
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-danger-rgb), 0.25);
}

.split-with-add-trigger {
  padding: 0.75rem 1rem;
  border-top: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: 0;
  color: var(--bs-body-color);
}

.split-with-add-trigger:hover,
.split-with-add-trigger:focus {
  color: var(--bs-primary);
  background-color: var(--bs-body-bg);
}

.split-with-block--add-open .split-with-add-trigger {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  background-color: var(--bs-body-bg);
  color: var(--bs-primary);
}

.split-with-add-panel {
  padding: 0.75rem;
  background-color: var(--bs-body-bg);
  border-top: var(--bs-border-width) solid var(--bs-border-color);
}
</style>
