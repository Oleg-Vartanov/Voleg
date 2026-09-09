import { computed, reactive, ref } from 'vue'
import client from '@/modules/core/apiClient'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation'
import { useCurrency } from '@/modules/core/composables/useCurrency'
import moneyUtils from '@/modules/core/utils/moneyUtils'
import dateUtils from '@/modules/core/utils/dateUtils'
import type { ApiUser } from '@/modules/core/apiType'
import type { ApiSeAdjustment, ApiSeAdjustmentPayload } from '@/modules/splitExpense/types'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

export interface AdjustmentFormFields {
  amount: string
  adjustmentDate: string
  currencyId: number | null
  description: string
  otherUser: ApiUser | null
}

export function defaultFields(): AdjustmentFormFields {
  return {
    amount: '',
    adjustmentDate: dateUtils.todayIsoDate(),
    currencyId: null,
    description: '',
    otherUser: null
  }
}

export function useAdjustmentForm() {
  const auth = useAuth()
  const topAlerts = useTopAlerts()
  const validation = useApiValidation<ApiSeAdjustmentPayload>()
  const currency = useCurrency()

  const fields = reactive<AdjustmentFormFields>(defaultFields())
  const isLoaded = ref(false)
  const isLoading = ref(false)
  const editingId = ref<number | null>(null)
  const editingCreatedById = ref<number | null>(null)
  const editingOtherUserId = ref<number | null>(null)
  const isEditing = computed(() => editingId.value !== null)

  const amountDecimalPlaces = computed(() => currency.decimalPlacesById(fields.currencyId))

  const otherUserError = computed(() => validation.getError('otherUserId'))

  const otherUserIsValid = computed(() =>
    validation.isError.value ? otherUserError.value === null : null
  )

  async function loadReferenceData(): Promise<boolean> {
    return currency
      .load()
      .then(() => true)
      .catch(() => {
        topAlerts.add('Failed to load adjustment form data.', 'danger', 5)
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

  async function loadForEdit(adjustment: ApiSeAdjustment) {
    if (isLoading.value) return
    isLoading.value = true

    await loadReferenceData()
      .then((loaded) => {
        if (!loaded) return
        fill(adjustment)
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
    editingId.value = null
    editingCreatedById.value = null
    editingOtherUserId.value = null
  }

  function fill(adjustment: ApiSeAdjustment) {
    validation.reset()
    editingId.value = adjustment.id
    editingCreatedById.value = adjustment.createdByUser.id
    editingOtherUserId.value = adjustment.otherUser.id
    const storedMinor = Number(adjustment.amount)
    const viewerMinor = adjustment.createdByUser.id === auth.user.id ? storedMinor : -storedMinor
    fields.amount = moneyUtils.fromMinorUnits(viewerMinor, adjustment.currency.decimalPlaces)
    fields.adjustmentDate = adjustment.adjustmentDate.slice(0, 10)
    fields.currencyId = adjustment.currency.id
    fields.description = adjustment.description ?? ''
    fields.otherUser =
      adjustment.createdByUser.id === auth.user.id ? adjustment.otherUser : adjustment.createdByUser
  }

  async function submit(): Promise<ApiSeAdjustment | null> {
    if (isLoading.value) return null

    const viewerMinor = moneyUtils.toMinorUnits(
      fields.amount,
      currency.decimalPlacesById(fields.currencyId)
    )
    const isCreator = editingId.value === null || editingCreatedById.value === auth.user.id
    const otherUserId =
      editingId.value === null ? (fields.otherUser?.id ?? 0) : (editingOtherUserId.value ?? 0)
    const payload: ApiSeAdjustmentPayload = {
      otherUserId,
      amount: isCreator ? viewerMinor : -viewerMinor,
      currencyId: fields.currencyId ?? 0,
      adjustmentDate: fields.adjustmentDate,
      description: fields.description.trim() || null
    }
    const id = editingId.value
    isLoading.value = true
    validation.reset()

    const request =
      id === null
        ? client.createSplitExpenseAdjustment(payload)
        : client.updateSplitExpenseAdjustment(id, payload)

    return request
      .then((response) => {
        if (id === null) {
          topAlerts.add('Adjustment created.', 'success', 3)
          reset()
        } else {
          topAlerts.add('Adjustment updated.', 'success', 3)
        }
        return response.data as ApiSeAdjustment
      })
      .catch((axiosError) => {
        if (axiosError.response?.status === 422) {
          validation.applyErrors(axiosError.response.data.violations)
          return null
        }
        const fallback =
          id === null ? 'Failed to create adjustment.' : 'Failed to update adjustment.'
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
    isEditing,
    load,
    loadForEdit,
    reset,
    submit,
    otherUserError,
    otherUserIsValid,
    currencyOptions: currency.currencyOptions
  }
}

export type AdjustmentFormState = ReturnType<typeof useAdjustmentForm>
