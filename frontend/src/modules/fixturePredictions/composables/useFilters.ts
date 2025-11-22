import { ref } from 'vue';
import type { Ref } from 'vue';

export interface FixtureFilters {
  start: Ref<string | null>;
  end: Ref<string | null>;
  competition: Ref<string>;
  season: Ref<number | null>;
  applyFilters?: (response: any) => void;
}

const start = ref<string | null>(null);
const end = ref<string | null>(null);
const competition = ref('PL');
const season = ref<number | null>(null);

export function useFilters(): FixtureFilters {
  function applyFilters(response: any) {
    start.value = response.data.filters.start;
    end.value = response.data.filters.end;
    competition.value = response.data.filters.competition;
    season.value = response.data.filters.season;
  }

  return { start, end, competition, season, applyFilters };
}
