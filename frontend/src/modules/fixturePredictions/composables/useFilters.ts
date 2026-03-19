import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import type { Ref } from 'vue';
import { CompetitionCode } from '@/modules/fixturePredictions/enum';

export interface FixtureFilters {
  start: Ref<string | null>;
  end: Ref<string | null>;
  competition: Ref<CompetitionCode>;
  season: Ref<number | null>;
  applyByResponse: (response: any) => void;
  reset: (response: any) => void;
}

export function useFilters(): FixtureFilters {
  const defaults = {
    start: null,
    end: null,
    competition: CompetitionCode.PL,
    season: null,
  };

  const route = useRoute();
  const router = useRouter();

  const start = ref<string | null>(
    route.query.start ?? defaults.start
  );
  const end = ref<string | null>(
    route.query.end ?? defaults.end
  );
  const competition = ref(
    route.query.competition ?? defaults.competition
  );
  const season = ref<number | null>(
    route.query.season ?? defaults.season
  );

  function reset(): void {
    start.value = defaults.start;
    end.value = defaults.end;
    competition.value = defaults.competition;
    season.value = defaults.season;
  }

  function applyByResponse(response: any): void {
    start.value = response.data.filters.start;
    end.value = response.data.filters.end;
    competition.value = response.data.filters.competition;
    season.value = response.data.filters.season;

    router.replace({
      query: {
        ...route.query,
        start: start.value || undefined,
        end: end.value || undefined,
        competition: competition.value || undefined,
        season: season.value || undefined,
      },
    });
  }

  return { start, end, competition, season, applyByResponse, reset };
}
