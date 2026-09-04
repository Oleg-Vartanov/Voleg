import { computed, ref } from 'vue'
import Client from '@/modules/core/apiClient'
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters'
import { type Versus } from '@/modules/fixturePredictions/composables/useVersus.ts'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { usePagePagination } from '@/modules/core/components/pagination/usePagePagination'
import type { Fixture, LeaderboardUser } from '@/modules/fixturePredictions/type'
import { useRouter } from 'vue-router'

export enum TablesEnum {
  MATCHES = 'matches',
  LEADERBOARD = 'leaderboard'
}

export interface Tables {
  isLoading: Ref<{
    fixtures: boolean
    leaderboard: boolean
  }>
  isLoadingTables: Ref<boolean>
  fixtures: Ref<Fixture[] | null>
  fixturesPagination: ReturnType<typeof usePagePagination>
  setFixturesPage: (page: number) => Promise<void>
  setFixturesPageSize: (size: number) => Promise<void>
  leaderboard: Ref<LeaderboardUser[] | null>
  leaderboardPagination: ReturnType<typeof usePagePagination>
  setLeaderboardPage: (page: number) => Promise<void>
  setLeaderboardPageSize: (size: number) => Promise<void>
  initTable: (tab: TablesEnum) => void
  updateLoadedTables: () => void
  loadFixtures: (resetPage?: boolean) => Promise<void>
  loadLeaderboard: (resetPage?: boolean) => Promise<void>
}

export function useTables(filters: FixtureFilters, vs: Versus): Tables {
  const router = useRouter()
  const topAlerts = useTopAlerts()

  const isLoading = ref({
    fixtures: false,
    leaderboard: false
  })
  const isLoadingTables = computed(() => {
    return isLoading.value.fixtures || isLoading.value.leaderboard
  })
  const fixtures = ref(null)
  const leaderboard = ref(null)

  const fixturesPagination = usePagePagination(25)
  const leaderboardPagination = usePagePagination(20)

  function initTable(tab: TablesEnum) {
    if (tab === TablesEnum.MATCHES && fixtures.value === null) {
      loadFixtures()
    }
    if (tab === TablesEnum.LEADERBOARD && leaderboard.value === null) {
      loadLeaderboard()
    }
  }

  function updateLoadedTables() {
    if (fixtures.value !== null) {
      loadFixtures()
    }
    if (leaderboard.value !== null) {
      loadLeaderboard()
    }
  }

  async function loadFixtures(resetPage = true) {
    if (resetPage) {
      fixturesPagination.setPageIndex(1)
    }
    isLoading.value.fixtures = true

    try {
      const response = await Client.showFixtures(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        vs.getUserIds(),
        filters.season.value,
        fixturesPagination.offset.value,
        fixturesPagination.limit.value
      )
      fixtures.value = response.data.fixtures
      fixturesPagination.setTotalItems(
        Number(response.headers['x-total-count'] ?? response.data.filters.total)
      )
      filters.onLoadTable(response.data.filters)
      vs.onLoadFixtures(response.data.filters.users)
      updateRouteQuery()

      if (fixturesPagination.pageIndex.value > fixturesPagination.totalPages.value) {
        fixturesPagination.setPageIndex(fixturesPagination.totalPages.value)
        isLoading.value.fixtures = false
        await loadFixtures(false)
      }
    } catch (err) {
      if (err?.response?.status === 422) {
        topAlerts.add('Invalid request. Check the filters and retry.', 'warning')
      } else {
        topAlerts.add('Error during obtaining data.', 'danger')
      }
      reset()
    } finally {
      isLoading.value.fixtures = false
    }
  }

  async function loadLeaderboard(resetPage = true) {
    if (resetPage) {
      leaderboardPagination.setPageIndex(1)
    }
    isLoading.value.leaderboard = true

    try {
      const response = await Client.leaderboard(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        filters.season.value,
        leaderboardPagination.offset.value,
        leaderboardPagination.limit.value
      )
      leaderboard.value = response.data.users
      leaderboardPagination.setTotalItems(
        Number(response.headers['x-total-count'] ?? response.data.filters.total)
      )
      filters.onLoadTable(response.data.filters)
      updateRouteQuery()

      if (leaderboardPagination.pageIndex.value > leaderboardPagination.totalPages.value) {
        leaderboardPagination.setPageIndex(leaderboardPagination.totalPages.value)
        isLoading.value.leaderboard = false
        await loadLeaderboard(false)
      }
    } catch (err) {
      if (err?.response?.status === 422) {
        topAlerts.add('Invalid request. Check the filters and retry.', 'warning')
      } else {
        topAlerts.add('Error during obtaining leaderboard.', 'danger')
      }
      reset()
    } finally {
      isLoading.value.leaderboard = false
    }
  }

  async function setFixturesPage(page: number): Promise<void> {
    // Changing the page size also emits a page reset, which would double the request.
    if (fixtures.value !== null && fixturesPagination.pageIndex.value === page) return

    fixturesPagination.setPageIndex(page)
    await loadFixtures(false)
  }

  async function setFixturesPageSize(size: number): Promise<void> {
    fixturesPagination.setPageSize(size)
    await loadFixtures(false)
  }

  async function setLeaderboardPage(page: number): Promise<void> {
    if (leaderboard.value !== null && leaderboardPagination.pageIndex.value === page) return

    leaderboardPagination.setPageIndex(page)
    await loadLeaderboard(false)
  }

  async function setLeaderboardPageSize(size: number): Promise<void> {
    leaderboardPagination.setPageSize(size)
    await loadLeaderboard(false)
  }

  function reset(): void {
    fixtures.value = []
    leaderboard.value = []
    fixturesPagination.setTotalItems(0)
    leaderboardPagination.setTotalItems(0)
    filters.reset()
  }

  function updateRouteQuery(): void {
    router.replace({
      query: {
        ...router.query,
        ...filters.routeQuery(),
        ...vs.routeQuery()
      }
    })
  }

  return {
    isLoading,
    isLoadingTables,
    fixtures,
    fixturesPagination,
    setFixturesPage,
    setFixturesPageSize,
    leaderboard,
    leaderboardPagination,
    setLeaderboardPage,
    setLeaderboardPageSize,
    initTable,
    updateLoadedTables,
    loadFixtures,
    loadLeaderboard
  }
}
