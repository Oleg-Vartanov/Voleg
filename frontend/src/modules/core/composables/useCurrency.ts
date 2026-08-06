import { computed, ref } from 'vue'
import client from '@/modules/core/apiClient'
import type { FormSelectOption } from '@/modules/core/components/form/types'
import type { ApiCurrency } from '@/modules/core/apiType'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'

const currencies = ref<ApiCurrency[] | null>(null)
let loadPromise: Promise<void> | null = null

export function useCurrency() {
  const topAlerts = useTopAlerts()

  const currencyOptions = computed((): FormSelectOption[] =>
    (currencies.value ?? []).map((currency) => ({
      value: currency.id,
      label: `${currency.code} — ${currency.name}`
    }))
  )

  async function load(): Promise<void> {
    if (loadPromise) return loadPromise

    loadPromise = client
      .listCurrencies()
      .then((response) => {
        currencies.value = response.data
      })
      .catch(() => {
        currencies.value = []
        topAlerts.add('Failed to load currencies.', 'danger', 5)
      })

    return loadPromise
  }

  function findById(id: number | null): ApiCurrency | null {
    if (id === null || currencies.value === null) return null
    return currencies.value.find((currency) => currency.id === id) ?? null
  }

  function defaultCurrencyId(): number | null {
    if (!currencies.value?.length) return null

    const usd = currencies.value.find((currency) => currency.code === 'USD')
    return (usd ?? currencies.value[0]).id
  }

  function decimalPlacesById(id: number | null): number {
    return findById(id)?.decimalPlaces ?? 2
  }

  return {
    currencies,
    currencyOptions,
    load,
    findById,
    defaultCurrencyId,
    decimalPlacesById
  }
}
