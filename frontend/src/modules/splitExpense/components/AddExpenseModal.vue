<script setup lang="ts">
import { watch } from 'vue'
import AppModal from '@/modules/core/components/AppModal.vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import FormInputAmount from '@/modules/core/components/form/FormInputAmount.vue'
import FormSelect from '@/modules/core/components/form/FormSelect.vue'
import FormTextarea from '@/modules/core/components/form/FormTextarea.vue'
import FormUserSelect from '@/modules/core/components/form/FormUserSelect.vue'
import UserSearch from '@/modules/core/components/UserSearch.vue'
import { useAddExpenseForm } from '@/modules/splitExpense/composables/useAddExpenseForm.ts'
import { useAuth } from '@/modules/user/stores/useAuth.ts';

const open = defineModel<boolean>('open', { required: true })

const emit = defineEmits<{ created: [] }>()

const auth = useAuth()
const {
  fields,
  amountDecimalPlaces,
  validation,
  isLoading,
  load,
  splitUsers,
  splitUsersIsOpen,
  splitUsersIsInvalid,
  splitUsersAdd,
  splitUsersRemove,
  submit: submitExpense,
  currencyOptions,
  categoryOptions,
  payerOptions,
  splitUserOptions,
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

              <FormUserSelect
                id="expense-paid-by"
                v-model="fields.paidByUser"
                label="Paid by"
                :users="payerOptions"
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

              <fieldset class="mb-0">
                <legend class="form-label fs-6 mb-2">Split equally with</legend>
                <div
                  class="split-with-block border rounded overflow-hidden bg-body-tertiary mb-1"
                  :class="{
                    'split-with-block--add-open': splitUsersIsOpen,
                    'split-with-block--invalid': splitUsersIsInvalid
                  }"
                >
                  <ul class="list-group list-group-flush mb-0">
                    <li
                      v-for="user in splitUsers"
                      :key="user.id"
                      class="list-group-item d-flex justify-content-between align-items-center gap-2"
                    >
                      <span class="text-truncate">
                        {{ user.displayName }} (@{{ user.tag }})
                      </span>
                      <span
                        v-if="user.id === fields.paidByUser?.id || user.id === auth.user.id"
                        class="badge text-bg-secondary flex-shrink-0"
                      >
                        {{ user.id === auth.user.id ? 'You' : 'Paid by' }}
                      </span>
                      <button
                        v-else
                        type="button"
                        class="btn btn-outline-danger btn-sm flex-shrink-0"
                        @click="splitUsersRemove(user.id)"
                      >
                        Remove
                      </button>
                    </li>
                  </ul>

                  <div class="split-with-add">
                    <button
                      type="button"
                      class="split-with-add-trigger btn btn-link text-decoration-none w-100 text-start d-flex align-items-center justify-content-between gap-2"
                      :aria-expanded="splitUsersIsOpen"
                      aria-controls="expense-add-split-user"
                      @click="splitUsersIsOpen = !splitUsersIsOpen"
                    >
                      <span>Add users</span>
                      <i
                        class="bi flex-shrink-0"
                        :class="splitUsersIsOpen ? 'bi-chevron-up' : 'bi-chevron-down'"
                        aria-hidden="true"
                      ></i>
                    </button>

                    <div
                      v-if="splitUsersIsOpen"
                      id="expense-add-split-user"
                      class="split-with-add-panel"
                    >
                      <UserSearch
                        action-label="Add"
                        :users="splitUserOptions"
                        :exclude-user-ids="() => splitUsers.map((user) => user.id)"
                        embedded
                        validation-id="expense-split-validation"
                        @action="splitUsersAdd"
                      />
                    </div>
                  </div>
                </div>
                <div
                  v-if="splitUsersIsInvalid"
                  id="expense-split-validation"
                  class="invalid-feedback d-block"
                >
                  {{ validation.getError('splits') }}
                </div>
              </fieldset>
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

<style scoped>
@import '@/modules/splitExpense/styles/expense-form.scss';
</style>
