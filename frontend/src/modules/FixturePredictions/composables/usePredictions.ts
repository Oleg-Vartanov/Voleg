import { computed, ref } from 'vue';
import Client from "@/modules/Core/apiClient";
import { useTopAlerts } from "@/modules/Core/topAlerts";
import { Alert } from '@/models/alert';

export function usePredictions(tables) {
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

  async function makePredictions(event: SubmitEvent) {
    isLoading.value = true;

    const form = event.target as HTMLFormElement;
    const elements = form.elements;
    const predictions: Record<string, any> = {};

    for (let element of elements) {
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
        delete predictions[index]
      }
    });

    try {
      await Client.makePredictions(Object.values(predictions));
      tables.updateLoadedTables();
      topAlerts.add(new Alert('Updated.', 'success', 10));
    } catch (err: any) {
      switch (err?.response?.status) {
        case 409:
          topAlerts.add(new Alert('Some fixtures have already started. Try to reload the page.', 'danger', 10));
          break;
        default:
          topAlerts.add(new Alert('Error. Try again later or contact support.', 'danger', 10));
      }
    } finally {
      isLoading.value = false;
      closePredictionsModal();
    }
  }

  function closePredictionsModal() {
    document.getElementById('closePredictionsModal')?.click();
  }

  return { isLoading, getPrediction, makePredictions };
}
