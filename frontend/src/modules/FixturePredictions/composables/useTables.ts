import { ref } from 'vue';
import Client from "@/modules/Core/apiClient";
import { Alert } from "@/models/alert";
import type { FixtureFilters } from "@/modules/FixturePredictions/composables/useFilters";
import type { HeadToHead } from "@/modules/FixturePredictions/composables/useHeadToHead";

export interface Tables {
  isLoading: Ref<{
    fixtures: boolean;
    leaderboard: boolean;
  }>;
  fixtures: Ref<any[]>;
  leaderboard: Ref<any[] | null>;
  initTable: (tab: 'matches' | 'leaderboard') => void;
  updateLoadedTables: () => void;
  loadFixtures: (userIds?: number[]) => Promise<void>;
  loadLeaderboard: () => Promise<void>;
}

export function useTables(
  filters: FixtureFilters,
  h2h: HeadToHead,
): Tables {
  const isLoading = ref({
    fixtures: false,
    leaderboard: false,
  });

  const fixtures = ref([]);
  const leaderboard = ref(null);

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
        filters.season.value
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
        filters.season.value
      );
      leaderboard.value = response.data.users;
      filters.applyFilters(response);
    } catch {
      topAlerts.add(new Alert('Error during obtaining leaderboard.', 'danger', 10));
    } finally {
      isLoading.value.leaderboard = false;
    }
  }

  return { isLoading, fixtures, leaderboard, initTable, updateLoadedTables, loadFixtures, loadLeaderboard };
}
