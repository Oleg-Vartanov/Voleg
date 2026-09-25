import { ref } from 'vue'
import Client from '@/modules/core/apiClient'
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters'
import { type Tables } from '@/modules/fixturePredictions/composables/useTables'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { usePagePagination } from '@/modules/core/components/pagination/usePagePagination'
import { useAuth } from '@/modules/user/stores/useAuth'
import type { Fixture } from '@/modules/fixturePredictions/type'

/** Scores are raw input values: a `type="number"` input binds a number, or '' once cleared. */
export interface ScoreDraft {
  fixtureId: number
  homeScore: number | string
  awayScore: number | string
}

export type PredictionsForm = ReturnType<typeof usePredictionsForm>

/**
 * State of the predictions modal: fixtures of the selected period that have not started yet,
 * paged independently of the fixtures table.
 */
export function usePredictionsForm(filters: FixtureFilters, tables: Tables) {
  const auth = useAuth()
  const topAlerts = useTopAlerts()

  const isLoading = ref(false)
  const isSaving = ref(false)
  const fixtures = ref<Fixture[]>([])
  const pagination = usePagePagination(10)
  // Keyed by fixture id and kept across pages, so paging never drops typed scores.
  const drafts = ref<Record<string, ScoreDraft>>({})
  // Scores as loaded, so only changed predictions are sent.
  let loadedDrafts: Record<string, ScoreDraft> = {}

  async function init(): Promise<void> {
    drafts.value = {}
    loadedDrafts = {}
    pagination.setPageIndex(1)
    await loadFixtures()
  }

  async function loadFixtures(): Promise<void> {
    isLoading.value = true

    try {
      const response = await Client.showFixtures(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        null,
        filters.season.value,
        pagination.offset.value,
        pagination.limit.value,
        true
      )
      const loaded: Fixture[] = response.data.fixtures
      loaded.forEach(initDraft)
      fixtures.value = loaded
      pagination.setTotalItems(
        Number(response.headers['x-total-count'] ?? response.data.filters.total)
      )
    } catch {
      fixtures.value = []
      pagination.setTotalItems(0)
      topAlerts.add('Error during obtaining data.', 'danger')
    } finally {
      isLoading.value = false
    }
  }

  function initDraft(fixture: Fixture): void {
    const id = String(fixture.id)
    if (drafts.value[id]) return

    const prediction = fixture.fixturePredictions?.find(
      (p) => String(p.user.id) === String(auth.user.id)
    )
    const draft = {
      fixtureId: Number(fixture.id),
      homeScore: prediction?.homeScore ?? '',
      awayScore: prediction?.awayScore ?? ''
    }
    drafts.value[id] = draft
    loadedDrafts[id] = { ...draft }
  }

  async function setPage(page: number): Promise<void> {
    // Changing the page size also emits a page reset, which would double the request.
    if (pagination.pageIndex.value === page) return

    pagination.setPageIndex(page)
    await loadFixtures()
  }

  async function setPageSize(size: number): Promise<void> {
    pagination.setPageSize(size)
    await loadFixtures()
  }

  async function save(): Promise<void> {
    const changed = Object.values(drafts.value).flatMap((draft) => {
      const homeScore = parseScore(draft.homeScore)
      const awayScore = parseScore(draft.awayScore)
      const loaded = loadedDrafts[draft.fixtureId]
      // Half filled or cleared rows are skipped: a prediction can't be removed.
      if (homeScore === null || awayScore === null) return []
      if (
        homeScore === parseScore(loaded.homeScore) &&
        awayScore === parseScore(loaded.awayScore)
      ) {
        return []
      }

      return [{ fixtureId: draft.fixtureId, homeScore, awayScore }]
    })

    if (changed.length === 0) return

    isSaving.value = true

    try {
      await Client.makePredictions(changed)
      tables.updateLoadedTables()
      topAlerts.add('Updated.', 'success')
    } catch (err) {
      switch (err?.response?.status) {
        case 409:
          topAlerts.add('Some fixtures have already started. Try to reload the page.', 'danger')
          break
        default:
          topAlerts.add('Error. Try again later or contact support.', 'danger')
      }
    } finally {
      isSaving.value = false
    }
  }

  function parseScore(value: number | string): number | null {
    const score = parseInt(String(value), 10)

    return isNaN(score) ? null : score
  }

  return {
    isLoading,
    isSaving,
    fixtures,
    pagination,
    drafts,
    init,
    setPage,
    setPageSize,
    save
  }
}
