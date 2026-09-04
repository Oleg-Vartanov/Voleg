import { computed, reactive, ref, watch } from 'vue'
import client from '@/modules/core/apiClient'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation'
import { useCurrency } from '@/modules/core/composables/useCurrency'
import { useCategories } from '@/modules/splitExpense/composables/useCategories'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import dateUtils from '@/modules/core/utils/dateUtils'
import type { ApiUser } from '@/modules/core/apiType'
import type { FormSelectOption } from '@/modules/core/components/form/types'
import type { ApiSeExpense, ApiSeExpensePayload } from '@/modules/splitExpense/types'
import { buildEqualSplits } from '@/modules/splitExpense/utils/splitAmounts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'
import arrayUtils from '@/modules/core/utils/arrayUtils.ts'

export interface ExpenseFormFields {
  title: string
  amount: number | string
  expenseDate: string
  categoryId: number | null
  currencyId: number | null
  paidByUser: ApiUser | null
  description: string
}

export function defaultFields(): ExpenseFormFields {
  return {
    title: '',
    amount: '',
    expenseDate: dateUtils.todayIsoDate(),
    categoryId: null,
    currencyId: null,
    paidByUser: null,
    description: ''
  }
}

export function useExpenseForm() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()
  const validation = useApiValidation<ApiSeExpensePayload>()

  const currency = useCurrency()
  const categories = useCategories()

  const fields = reactive<ExpenseFormFields>(defaultFields())
  const isLoaded = ref(false)
  const isLoading = ref(false)
  const editingId = ref<number | null>(null)

  const splitUsersSelected = ref<ApiUser[]>([])
  const splitUsersLocked = computed(() => [auth.user])
  const splitUsers = computed(() =>
    arrayUtils.uniqueBy<ApiUser>(
      [...splitUsersLocked.value, ...splitUsersSelected.value],
      (user) => user.id
    )
  )

  const paidBySelectOptions = computed<FormSelectOption[]>(() =>
    splitUsers.value.map((user) => ({
      value: user.id,
      label: user.id === auth.user.id ? `@${user.username} (You)` : `@${user.username}`
    }))
  )

  const paidByUserId = computed({
    get: () => fields.paidByUser?.id ?? null,
    set: (id: string | number | null) => {
      const user = splitUsers.value.find((entry) => entry.id === Number(id))
      fields.paidByUser = user ?? auth.user
    }
  })

  watch(
    splitUsers,
    (users) => {
      if (fields.paidByUser !== null && users.some((user) => user.id === fields.paidByUser?.id)) {
        return
      }
      fields.paidByUser = auth.user
    },
    { immediate: true }
  )

  const amountDecimalPlaces = computed(() => currency.decimalPlacesById(fields.currencyId))

  async function loadReferenceData(): Promise<boolean> {
    return Promise.all([categories.load(), currency.load()])
      .then(() => true)
      .catch(() => {
        topAlerts.add('Failed to load expense form data.', 'danger', 5)
        return false
      })
  }

  async function load() {
    if (isLoading.value || isLoaded.value) return
    isLoading.value = true

    await loadReferenceData()
      .then((loaded) => {
        if (!loaded) return
        reset()
        isLoaded.value = true
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  async function loadForEdit(expense: ApiSeExpense) {
    if (isLoading.value) return
    isLoading.value = true

    await loadReferenceData()
      .then((loaded) => {
        if (!loaded) return
        fill(expense)
        isLoaded.value = true
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  function reset() {
    Object.assign(fields, defaultFields())
    validation.reset()
    fields.currencyId = currency.defaultCurrencyId()
    fields.categoryId = categories.defaultCategoryId()
    fields.paidByUser = auth.user
    splitUsersSelected.value = []
    editingId.value = null
  }

  function fill(expense: ApiSeExpense) {
    validation.reset()
    editingId.value = expense.id
    fields.title = expense.title
    fields.amount = moneyUtils.fromMinorUnits(
      Number(expense.amount),
      expense.currency.decimalPlaces
    )
    fields.expenseDate = expense.expenseDate.slice(0, 10)
    fields.currencyId = expense.currency.id
    fields.categoryId = expense.category?.id ?? categories.defaultCategoryId()
    fields.description = expense.description ?? ''
    splitUsersSelected.value = expense.splits
      .map((split) => split.user)
      .filter((user) => user.id !== auth.user.id)
    fields.paidByUser = expense.paidByUser
  }

  async function submit(): Promise<ApiSeExpense | null> {
    if (isLoading.value) return null

    const amountMinor = moneyUtils.toMinorUnits(
      fields.amount,
      currency.decimalPlacesById(fields.currencyId)
    )
    const payload: ApiSeExpensePayload = {
      title: fields.title.trim(),
      amount: amountMinor,
      currencyId: fields.currencyId,
      expenseDate: fields.expenseDate,
      categoryId: fields.categoryId,
      paidByUserId: fields.paidByUser.id,
      description: fields.description.trim() || null,
      splits: buildEqualSplits(
        amountMinor,
        splitUsers.value.map((user) => user.id)
      )
    }
    const id = editingId.value
    isLoading.value = true
    validation.reset()

    const request =
      id === null ? client.createSplitExpense(payload) : client.updateSplitExpense(id, payload)

    return request
      .then((response) => {
        if (id === null) {
          topAlerts.add('Expense created.', 'success', 3)
          reset()
        } else {
          topAlerts.add('Expense updated.', 'success', 3)
        }
        return response.data as ApiSeExpense
      })
      .catch((axiosError) => {
        if (axiosError.response?.status === 422) {
          validation.applyErrors(axiosError.response.data.violations)
          return null
        }
        const fallback = id === null ? 'Failed to create expense.' : 'Failed to update expense.'
        topAlerts.add(axiosError.response?.data?.message ?? fallback, 'danger', 5)
        return null
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  return {
    fields,
    amountDecimalPlaces,
    validation,
    isLoading,
    load,
    loadForEdit,
    reset,
    splitUsers,
    splitUsersSelected,
    splitUsersLocked,
    paidByUserId,
    paidBySelectOptions,
    submit,
    currencyOptions: currency.currencyOptions,
    categoryOptions: categories.categoryOptions
  }
}

export type ExpenseFormState = ReturnType<typeof useExpenseForm>
