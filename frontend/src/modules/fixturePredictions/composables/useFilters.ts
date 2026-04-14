import { type Ref, ref } from 'vue'
import { useRoute } from 'vue-router'
import { CompetitionCode } from '@/modules/fixturePredictions/enum'
import type { FixtureFiltersResponse } from '@/modules/core/response.ts'

export interface FixtureFilters {
  start: Ref<string | null>
  end: Ref<string | null>
  competition: Ref<CompetitionCode>
  season: Ref<number | null>
  updateByResponse: (filters: FixtureFiltersResponse) => void
  reset: () => void
  routeQuery: () => object
}

export function useFilters() {
  const defaults = {
    start: null,
    end: null,
    competition: CompetitionCode.PL,
    season: null
  }

  const route = useRoute()

  const start = ref<string | null>(route.query.start ?? defaults.start)
  const end = ref<string | null>(route.query.end ?? defaults.end)
  const competition = ref(route.query.competition ?? defaults.competition)
  const season = ref<number | null>(route.query.season ?? defaults.season)

  function reset(): void {
    start.value = defaults.start
    end.value = defaults.end
    competition.value = defaults.competition
    season.value = defaults.season
  }

  function updateByResponse(filters: FixtureFiltersResponse): void {
    start.value = filters.start
    end.value = filters.end
    competition.value = filters.competition
    season.value = filters.season
  }

  function routeQuery() {
    return {
      start: start.value || undefined,
      end: end.value || undefined,
      competition: competition.value || undefined,
      season: season.value || undefined
    }
  }

  return {
    start,
    end,
    competition,
    season,
    updateByResponse,
    reset,
    routeQuery
  }
}
