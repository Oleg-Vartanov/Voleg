import { computed, reactive, ref, watch } from 'vue'
import client from '@/modules/core/apiClient'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation'
import { useCurrency } from '@/modules/core/composables/useCurrency'
import { useCategories } from '@/modules/splitExpense/composables/useCategories'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import dateUtils from '@/modules/core/utils/dateUtils'
import type { ApiUser } from '@/modules/core/apiType'
import type { FormSelectOption } from '@/modules/core/components/form/types'
import type { ApiSeExpenseCreatePayload } from '@/modules/splitExpense/types'
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

export function useAddExpenseForm() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()
  const validation = useApiValidation<ApiSeExpenseCreatePayload>()

  const currency = useCurrency()
  const categories = useCategories()

  const fields = reactive<ExpenseFormFields>(defaultFields())
  const isLoaded = ref(false)
  const isLoading = ref(false)

  const splitUsersSelected = ref<ApiUser[]>([])
  const splitUsersLocked = computed(() => [auth.user])
  const splitUsers = computed(() =>
    arrayUtils.uniqueBy<ApiUser>([...splitUsersLocked.value, ...splitUsersSelected.value], (user) => user.id)
  )

  const paidBySelectOptions = computed<FormSelectOption[]>(() =>
    splitUsers.value.map((user) => ({
      value: user.id,
      label: user.id === auth.user.id ? `@${user.username} (You)` : `@${user.username}`,
    }))
  )

  const paidByUserId = computed({
    get: () => fields.paidByUser?.id ?? null,
    set: (id: string | number | null) => {
      const user = splitUsers.value.find((entry) => entry.id === Number(id))
      fields.paidByUser = user ?? auth.user
    },
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

  async function load() {
    if (isLoading.value || isLoaded.value) return
    isLoading.value = true

    await Promise.all([
      categories.load(),
      currency.load(),
    ])
    .then(() => {
      reset()
      isLoaded.value = true
    })
    .catch(() => {
      topAlerts.add('Failed to load expense form data.', 'danger', 5)
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
  }

  async function submit(): Promise<boolean> {
    if (isLoading.value) return false

    const amountMinor = moneyUtils.toMinorUnits(fields.amount, currency.decimalPlacesById(fields.currencyId))
    const payload: ApiSeExpenseCreatePayload = {
      title: fields.title.trim(),
      amount: amountMinor,
      currencyId: fields.currencyId,
      expenseDate: fields.expenseDate,
      categoryId: fields.categoryId,
      paidByUserId: fields.paidByUser.id,
      description: fields.description.trim() || null,
      splits: buildEqualSplits(amountMinor, splitUsers.value.map((user) => user.id))
    }
    isLoading.value = true
    validation.reset()

    return client.createSplitExpense(payload)
    .then(() => {
      topAlerts.add('Expense created.', 'success', 3)
      reset()
      return true
    })
    .catch((axiosError) => {
      if (axiosError.response?.status === 422) {
        validation.applyErrors(axiosError.response.data.violations)
        return false
      }
      topAlerts.add(axiosError.response?.data?.message ?? 'Failed to create expense.', 'danger', 5)
      return false
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
    reset,
    splitUsers,
    splitUsersSelected,
    splitUsersLocked,
    paidByUserId,
    paidBySelectOptions,
    submit,
    currencyOptions: currency.currencyOptions,
    categoryOptions: categories.categoryOptions,
  }
}
