import { computed, ref } from 'vue';
import Client from '@/modules/core/apiClient';
import { type FixtureFilters } from '@/modules/fixturePredictions/composables/useFilters';
import { type HeadToHead } from '@/modules/fixturePredictions/composables/useHeadToHead';
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts';
import type { Fixture, LeaderboardUser } from '@/modules/fixturePredictions/type';
import { useRouter } from 'vue-router';

export enum TablesEnum {
  MATCHES = 'matches',
  LEADERBOARD = 'leaderboard',
}

export interface Tables {
  isLoading: Ref<{
    fixtures: boolean;
    leaderboard: boolean;
  }>;
  isLoadingTables: Ref<boolean>;
  fixtures: Ref<Fixture[] | null>;
  leaderboard: Ref<LeaderboardUser[] | null>;
  initTable: (tab: TablesEnum) => void;
  updateLoadedTables: () => void;
  loadFixtures: (userIds?: number[]) => Promise<void>;
  loadLeaderboard: () => Promise<void>;
}

export function useTables(
  filters: FixtureFilters,
  h2h: HeadToHead,
): Tables {
  const router = useRouter();
  const topAlerts = useTopAlerts();

  const isLoading = ref({
    fixtures: false,
    leaderboard: false,
  });
  const isLoadingTables = computed(() => {
    return isLoading.value.fixtures || isLoading.value.leaderboard;
  });
  const fixtures = ref(null);
  const leaderboard = ref(null);

  function initTable(tab: TablesEnum) {
    if (tab === TablesEnum.MATCHES && fixtures.value === null) {
      loadFixtures();
    }
    if (tab === TablesEnum.LEADERBOARD && leaderboard.value === null) {
      loadLeaderboard();
    }
  }

  function updateLoadedTables() {
    if (fixtures.value !== null) {
      loadFixtures();
    }
    if (leaderboard.value !== null) {
      loadLeaderboard();
    }
  }

  async function loadFixtures() {
    isLoading.value.fixtures = true;

    try {
      const response = await Client.showFixtures(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        h2h.getUserIds(),
        filters.season.value,
      );
      fixtures.value = response.data.fixtures;
      filters.updateByResponse(response.data.filters);
      h2h.updateByResponse(response);
      updateRouteQuery();
    } catch (err) {
      if (err?.response?.status === 422) {
        topAlerts.add('Invalid request. Check the filters and retry.', 'warning');
      } else {
        topAlerts.add('Error during obtaining data.', 'danger');
      }
      reset();
    } finally {
      isLoading.value.fixtures = false;
    }
  }

  async function loadLeaderboard() {
    isLoading.value.leaderboard = true;
    try {
      const response = await Client.leaderboard(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        filters.season.value,
      );
      leaderboard.value = response.data.users;
      filters.updateByResponse(response.data.filters);
      updateRouteQuery();
    } catch (err) {
      if (err?.response?.status === 422) {
        topAlerts.add('Invalid request. Check the filters and retry.', 'warning');
      } else {
        topAlerts.add('Error during obtaining leaderboard.', 'danger');
      }
      reset();
    } finally {
      isLoading.value.leaderboard = false;
    }
  }

  function reset(): void {
    fixtures.value = [];
    leaderboard.value = [];
    filters.reset();
  }

  function updateRouteQuery(): void {
    router.replace({
      query: {
        ...router.query,
        ...filters.routeQuery(),
        ...h2h.routeQuery(),
      },
    });
  }

  return {
    isLoading,
    isLoadingTables,
    fixtures,
    leaderboard,
    initTable,
    updateLoadedTables,
    loadFixtures,
    loadLeaderboard,
  };
}
