import type { Fixture, Prediction } from '@/modules/fixturePredictions/type'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { computed } from 'vue'

export interface Predictions {
  getPrediction: (fixtureId: number | string, userId: number | string) => Nullable<Prediction>
  scoreColorClass: (prediction: Nullable<Prediction>) => string
  fixtureDate: (fixture: Fixture) => { id: number; date: string; time: string }
}

export function usePredictions(tables: Tables): Predictions {
  const predictionMap = computed(() => {
    const map = new Map()

    for (const fixture of tables.fixtures.value) {
      if (!fixture.fixturePredictions) continue

      for (const p of fixture.fixturePredictions) {
        const key = `${fixture.id}-${p.user.id}`
        map.set(key, p)
      }
    }

    return map
  })

  function getPrediction(fixtureId, userId) {
    return predictionMap.value.get(`${fixtureId}-${userId}`) || null
  }

  function scoreColorClass(prediction) {
    switch (prediction?.points) {
      case 3:
        return 'text-success'
      case 1:
        return 'text-warning'
      case 0:
        return 'text-danger'
      default:
        return ''
    }
  }

  function fixtureDate(fixture) {
    const date = new Date(fixture.startAt)

    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const hour = String(date.getHours()).padStart(2, '0')
    const minute = String(date.getMinutes()).padStart(2, '0')

    return { id: fixture.id, date: `${day}/${month}`, time: `${hour}:${minute}` }
  }

  return {
    getPrediction,
    scoreColorClass,
    fixtureDate
  }
}
