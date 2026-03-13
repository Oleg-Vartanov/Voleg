import type { UnwrapRefSimple, ReactiveMarker } from '@vue/reactivity';
import { ref, reactive, watch } from 'vue';
import arrayUtils from '@/modules/core/utils/arrayUtils';

export type AlertType = 'primary' | 'success' | 'danger' | 'info' | 'warning';

export interface Alert {
  id: number;
  text: string;
  type: AlertType;
  timeout: number;
  countdown: Ref<number>;
}

const alerts: Alert[] | UnwrapRefSimple<any>[] & ReactiveMarker = reactive([]);

export const useTopAlerts = () => {
  function createAlert(text: string, type: AlertType = 'primary', timeout: number = 10): Alert {
    const countdown = ref(timeout);

    if (timeout > 0) {
      const tick = () => {
        setTimeout(() => {
          if (countdown.value > 0) {
            countdown.value--;
            tick();
          }
        }, 1000);
      };

      tick();
    }

    return {
      id: 0,
      text,
      type,
      timeout,
      countdown,
    };
  }

  const add = (text: string, type: AlertType = 'primary', timeout: number = 10) => {
    const alert = createAlert(text, type, timeout);
    alert.id = generateNewAlertId();
    alerts.push(alert);
    removeAfterTimeout(alert);
  };

  const remove = (alert: Alert): void => {
    const index: number = alerts.indexOf(alert);
    if (index !== -1) {
      arrayUtils.removeIndex(alerts, index);
    }
  };

  const generateNewAlertId = (): number => {
    const highestAlertId: null | number = getHighestAlertId();
    return highestAlertId !== null ? highestAlertId + 1 : 0;
  };

  const getHighestAlertId = (): null | number => {
    if (alerts.length === 0) {
      return null;
    }

    return alerts.reduce(
      (max: number, alert: Alert): number => (alert.id > max ? alert.id : max),
      alerts[0].id,
    );
  };

  const removeAfterTimeout = (alert: Alert): void => {
    if (alert.timeout > 0) {
      watch(alert.countdown, (newValue: number): void => {
        if (newValue === 0) {
          remove(alert);
        }
      });
    }
  };

  return { alerts, add, remove };
};