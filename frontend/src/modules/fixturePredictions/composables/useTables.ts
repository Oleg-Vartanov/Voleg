import { computed, ref } from 'vue';
import Client from '@/modules/core/apiClient';
import { Alert } from '@/models/alert';
import { useFilters } from '@/modules/fixturePredictions/composables/useFilters';
import { useHeadToHead } from '@/modules/fixturePredictions/composables/useHeadToHead';
import type { Fixture, LeaderboardUser } from '@/modules/fixturePredictions/type';

export type TablesEnum = 'matches' | 'leaderboard';

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

const filters = useFilters();
const h2h = useHeadToHead();

const isLoading = ref({
  fixtures: false,
  leaderboard: false,
});
const isLoadingTables = computed(() => {
  return isLoading.value.fixtures || isLoading.value.leaderboard;
});
const fixtures = ref(null);
const leaderboard = ref(null);

export function useTables(): Tables {
  function initTable(tab: string) {
    if (tab === 'matches' && fixtures.value === null) {
      loadFixtures(h2h.users.value.map(u => u.id));
    }
    if (tab === 'leaderboard' && leaderboard.value === null) {
      loadLeaderboard();
    }
  }

  function updateLoadedTables() {
    if (fixtures.value !== null) {
      loadFixtures(h2h.users.value.map(u => u.id));
    }
    if (leaderboard.value !== null) {
      loadLeaderboard();
    }
  }

  async function loadFixtures(userIds: number[] = []) {
    isLoading.value.fixtures = true;
    try {
      const response = await Client.showFixtures(
        filters.start.value,
        filters.end.value,
        filters.competition.value,
        userIds,
        filters.season.value,
      );
      fixtures.value = response.data.fixtures;
      filters.applyFilters(response);
    } catch {
      topAlerts.add(new Alert('Error during obtaining data.', 'danger', 10));
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
      filters.applyFilters(response);
    } catch {
      topAlerts.add(new Alert('Error during obtaining leaderboard.', 'danger', 10));
    } finally {
      isLoading.value.leaderboard = false;
    }
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
