import { computed, reactive, ref } from 'vue'
import client from '@/modules/core/apiClient'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation'
import { useCurrency } from '@/modules/core/composables/useCurrency'
import { useCategories } from '@/modules/splitExpense/composables/useCategories'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import dateUtils from '@/modules/core/utils/dateUtils'
import type { ApiUser } from '@/modules/core/apiType'
import type { ApiSeExpenseCreatePayload } from '@/modules/splitExpense/types'
import { buildEqualSplits } from '@/modules/splitExpense/utils/splitAmounts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'
import { useConnectedUsers } from '@/modules/splitExpense/composables/useConnectedUsers.ts';
import arrayUtils from '@/modules/core/utils/arrayUtils.ts';

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
  const connectedUsers = useConnectedUsers()

  const fields = reactive<ExpenseFormFields>(defaultFields())
  const isLoaded = ref(false)
  const isLoading = ref(false)

  const splitUsersSelected = ref<ApiUser[]>([])
  const splitUsers = computed(() => {
    const splitUsersDefault = fields.paidByUser === null || fields.paidByUser.id === auth.user.id
      ? [auth.user]
      : [auth.user, fields.paidByUser]
    return arrayUtils.uniqueBy<ApiUser>([...splitUsersDefault, ...splitUsersSelected.value], (user) => user.id)
  })

  const splitUsersIsOpen = ref(false)
  const splitUsersIsInvalid = computed(() => validation.isValid('splits') === false)
  const amountDecimalPlaces = computed(() => currency.decimalPlacesById(fields.currencyId))

  async function load() {
    if (isLoading.value || isLoaded.value) return
    isLoading.value = true

    await Promise.all([
      categories.load(),
      currency.load(),
      connectedUsers.load()
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
    splitUsersIsOpen.value = false
  }

  function splitUsersAdd(user: ApiUser) {
    if (splitUsers.value.some((entry) => entry.id === user.id)) return
    if (user.id === auth.user.id) return
    if (user.id === fields.paidByUser?.id) return
    splitUsersSelected.value.push(user)
  }

  function splitUsersRemove(userId: number) {
    splitUsersSelected.value = splitUsersSelected.value.filter((user) => user.id !== userId)
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
    splitUsersIsOpen,
    splitUsersIsInvalid,
    splitUsersAdd,
    splitUsersRemove,
    submit,
    currencyOptions: currency.currencyOptions,
    categoryOptions: categories.categoryOptions,
    payerOptions: connectedUsers.payerOptions,
    splitUserOptions: connectedUsers.users,
  }
}

