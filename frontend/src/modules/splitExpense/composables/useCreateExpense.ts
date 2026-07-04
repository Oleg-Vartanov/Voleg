import { computed, reactive, ref, watch } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'
import client from '@/modules/core/apiClient'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import type { ApiUser } from '@/modules/core/apiType'
import type {
  ApiSeCategory,
  ApiSeConnection,
  ApiSeCurrency,
  SeExpenseCreatePayload
} from '@/modules/splitExpense/types'
import { getConnectionPartner } from '@/modules/splitExpense/utils/connections'
import { buildEqualSplits } from '@/modules/splitExpense/utils/splitAmounts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

type ExpenseFormField =
  | 'title'
  | 'amount'
  | 'currencyId'
  | 'expenseDate'
  | 'description'
  | 'paidByUserId'
  | 'categoryId'
  | 'splits'

export interface ExpenseFormFields {
  title: string
  amount: number
  expenseDate: string
  categoryId: number | null
  currencyId: number | null
  paidByUserId: number | null
  description: string
}

function todayIsoDate(): string {
  return new Date().toISOString().slice(0, 10)
}

function createEmptyFields(): ExpenseFormFields {
  return {
    title: '',
    amount: '',
    expenseDate: todayIsoDate(),
    categoryId: null,
    currencyId: null,
    paidByUserId: null,
    description: ''
  }
}

export function useCreateExpense() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()
  const validation = useApiValidation<ExpenseFormField>()

  const categories = ref<ApiSeCategory[]>([])
  const currencies = ref<ApiSeCurrency[]>([])
  const connections = ref<ApiSeConnection[]>([])
  const fields = reactive<ExpenseFormFields>(createEmptyFields())

  const ui = reactive({
    isOptionsLoading: false,
    isSubmitting: false,
    splitWithUsers: [] as ApiUser[]
  })

  const acceptedConnections = computed(() =>
    connections.value.filter((connection) => connection.status === 'accepted')
  )

  const connectionPartners = computed(() => {
    if (auth.user.id === null) return []

    return acceptedConnections.value.map((connection) =>
      getConnectionPartner(connection, auth.user.id!)
    )
  })

  const splitPartnerOptions = computed(() => connectionPartners.value)

  const currentUser = computed((): ApiUser | null => {
    if (auth.user.id === null) return null

    return {
      id: auth.user.id,
      displayName: auth.user.displayName ?? 'Me',
      tag: auth.user.tag ?? '',
      email: '',
      createdAt: ''
    }
  })

  const payerOptions = computed((): ApiUser[] => {
    if (currentUser.value === null) return []

    return [currentUser.value, ...connectionPartners.value]
  })

  const selectedPayer = computed((): ApiUser | null => {
    if (fields.paidByUserId === null) return null

    return payerOptions.value.find((user) => user.id === fields.paidByUserId) ?? null
  })

  const categoryOptions = computed(() =>
    categories.value.map((category) => ({
      value: category.id,
      label: category.title
    }))
  )

  const currencyOptions = computed(() =>
    currencies.value.map((currency) => ({
      value: currency.id,
      label: `${currency.code} (${currency.symbol})`
    }))
  )

  const selectedCurrency = computed(
    () => currencies.value.find((currency) => currency.id === fields.currencyId) ?? null
  )

  const amountDecimalPlaces = computed(() => selectedCurrency.value?.decimalPlaces ?? 2)

  function fieldAttrs(name: ExpenseFormField) {
    return {
      isValid: validation.isValid(name),
      errorText: validation.getError(name) ?? ''
    }
  }

  function applyDefaultSelections() {
    if (fields.categoryId === null && categories.value.length > 0) {
      fields.categoryId = categories.value[0].id
    }

    if (fields.currencyId === null && currencies.value.length > 0) {
      const usd = currencies.value.find((currency) => currency.code === 'USD')
      fields.currencyId = (usd ?? currencies.value[0]).id
    }

    if (fields.paidByUserId === null && auth.user.id !== null) {
      fields.paidByUserId = auth.user.id
    }
  }

  async function loadOptions() {
    if (ui.isOptionsLoading) return
    ui.isOptionsLoading = true

    try {
      const [categoriesResponse, currenciesResponse, connectionsResponse] = await Promise.all([
        client.listSplitExpenseCategories(),
        client.listCurrencies(),
        client.listSplitExpenseConnections()
      ])

      categories.value = categoriesResponse.data
      currencies.value = currenciesResponse.data
      connections.value = connectionsResponse.data

      applyDefaultSelections()
    } catch {
      topAlerts.add('Failed to load expense form data.', 'danger', 5)
    } finally {
      ui.isOptionsLoading = false
    }
  }

  function reset() {
    validation.reset()
    Object.assign(fields, createEmptyFields())
    ui.splitWithUsers = []
    applyDefaultSelections()
  }

  function splitExcludeUserIds(): number[] {
    const excluded = ui.splitWithUsers.map((user) => user.id)

    if (fields.paidByUserId !== null) {
      excluded.push(fields.paidByUserId)
    }

    if (auth.user.id !== null) {
      excluded.push(auth.user.id)
    }

    return excluded
  }

  function addSplitWith(user: ApiUser) {
    if (fields.paidByUserId === user.id) return
    if (auth.user.id === user.id) return
    if (ui.splitWithUsers.some((entry) => entry.id === user.id)) return

    ui.splitWithUsers.push(user)
  }

  function removeSplitWith(userId: number) {
    ui.splitWithUsers = ui.splitWithUsers.filter((user) => user.id !== userId)
  }

  function closeModal() {
    const el = document.getElementById('addExpenseModal')
    if (!el) return

    Modal.getInstance(el)?.hide()
  }

  async function submit(): Promise<boolean> {
    if (auth.user.id === null || fields.paidByUserId === null || ui.isSubmitting) return false

    validation.reset()

    const participantIds = [
      fields.paidByUserId,
      auth.user.id,
      ...ui.splitWithUsers.map((user) => user.id)
    ]
    const uniqueParticipantIds = [...new Set(participantIds)]
    const amountMinor = moneyUtils.toMinorUnits(fields.amount, selectedCurrency.value.decimalPlaces)

    const payload: SeExpenseCreatePayload = {
      title: fields.title.trim(),
      amount: amountMinor,
      currencyId: selectedCurrency.value.id,
      expenseDate: fields.expenseDate,
      categoryId: fields.categoryId,
      paidByUserId: fields.paidByUserId,
      description: fields.description.trim() || null,
      splits: buildEqualSplits(amountMinor, uniqueParticipantIds)
    }

    ui.isSubmitting = true
    try {
      await client.createSplitExpense(payload)
      topAlerts.add('Expense created.', 'success', 3)
      reset()
      closeModal()
      return true
    } catch (error) {
      if (axios.isAxiosError(error)) {
        if (error.response?.status === 422) {
          validation.applyErrors(error.response.data.violations)
          return false
        }

        const message = error.response?.data?.message ?? 'Failed to create expense.'
        topAlerts.add(message, 'danger', 5)
        return false
      }

      topAlerts.add('Failed to create expense.', 'danger', 5)
      return false
    } finally {
      ui.isSubmitting = false
    }
  }

  watch(
    () => fields.paidByUserId,
    (paidByUserId) => {
      if (paidByUserId === null) return

      ui.splitWithUsers = ui.splitWithUsers.filter((user) => user.id !== paidByUserId)
    }
  )

  return reactive({
    fields,
    ui,
    currentUser,
    selectedPayer,
    splitPartnerOptions,
    categoryOptions,
    currencyOptions,
    payerOptions,
    amountDecimalPlaces,
    validation,
    fieldAttrs,
    loadOptions,
    reset,
    splitExcludeUserIds,
    addSplitWith,
    removeSplitWith,
    submit
  })
}

