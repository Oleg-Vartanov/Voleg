import type { Fixture, Prediction } from '@/modules/fixturePredictions/type';
import type Tables from '@/modules/fixturePredictions/composables/useTables';
import Client from '@/modules/core/apiClient';
import { computed, ref } from 'vue';
import { useTopAlerts } from '@/modules/core/composables/useTopAlerts';

export interface Predictions {
  isLoading: Ref<boolean>;
  getPrediction: (fixtureId: number | string, userId: number | string) => Nullable<Prediction>;
  getHomeScore: (prediction: Nullable<Prediction>, defaultValue?: string) => number | string | null;
  getAwayScore: (prediction: Nullable<Prediction>, defaultValue?: string) => number | string | null;
  makePredictions: (event: SubmitEvent) => Promise<void>;
  scoreColorClass: (prediction: Nullable<Prediction>) => string;
  fixtureDate: (fixture: Fixture) => { date: string; time: string };
}

export function usePredictions(
  tables: Tables,
): Predictions {
  const topAlerts = useTopAlerts();

  const isLoading = ref(false);
  const predictionMap = computed(() => {
    const map = new Map();

    for (const fixture of tables.fixtures.value) {
      if (!fixture.fixturePredictions) continue;

      for (const p of fixture.fixturePredictions) {
        const key = `${fixture.id}-${p.user.id}`;
        map.set(key, p);
      }
    }

    return map;
  });

  function getPrediction(fixtureId, userId) {
    return predictionMap.value.get(`${fixtureId}-${userId}`) || null;
  }

  function getHomeScore(prediction, defaultValue: string = '-') {
    return prediction?.homeScore == null ? defaultValue : prediction.homeScore;
  }

  function getAwayScore(prediction, defaultValue: string = '-') {
    return prediction?.awayScore == null ? defaultValue : prediction.awayScore;
  }

  function scoreColorClass(prediction) {
    switch (prediction?.points) {
      case 3:
        return 'text-success';
      case 1:
        return 'text-warning';
      case 0:
        return 'text-danger';
      default:
        return '';
    }
  }

  function fixtureDate(fixture) {
    const date = new Date(fixture.startAt);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');

    return { date: `${day}/${month}`, time: `${hour}:${minute}` };
  }

  async function makePredictions(event: SubmitEvent) {
    isLoading.value = true;

    const form = event.target as HTMLFormElement;
    const elements = form.elements;
    const predictions: Record<string, any> = {};

    for (const element of elements) {
      if (!(element instanceof HTMLInputElement)) continue;

      const fixtureId = element.dataset.id || null;
      const side = element.dataset.side || null;
      if (fixtureId === null || side === null) continue;
      const parsedValue = parseInt(element.value, 10);
      const score = isNaN(parsedValue) ? null : parsedValue;

      if (!predictions[fixtureId]) {
        predictions[fixtureId] = {
          fixtureId: parseInt(fixtureId, 10),
          homeScore: null,
          awayScore: null,
        };
      }

      if (side === 'home') predictions[fixtureId].homeScore = score;
      if (side === 'away') predictions[fixtureId].awayScore = score;
    }

    // Exclude not filled fixtures.
    Object.entries(predictions).forEach(([index, prediction]) => {
      if (prediction.homeScore === null || prediction.awayScore === null) {
        delete predictions[index];
      }
    });

    try {
      await Client.makePredictions(Object.values(predictions));
      tables.updateLoadedTables();
      topAlerts.add('Updated.', 'success');
    } catch (err: any) {
      switch (err?.response?.status) {
        case 409:
          topAlerts.add('Some fixtures have already started. Try to reload the page.', 'danger');
          break;
        default:
          topAlerts.add('Error. Try again later or contact support.', 'danger');
      }
    } finally {
      isLoading.value = false;
      closePredictionsModal();
    }
  }

  function closePredictionsModal() {
    document.getElementById('closePredictionsModal')?.click();
  }

  return {
    isLoading,
    getPrediction,
    getHomeScore,
    getAwayScore,
    makePredictions,
    scoreColorClass,
    fixtureDate,
  };
}
