import type {UnwrapRefSimple, ReactiveMarker} from "@vue/reactivity";
import {reactive, watch} from "vue";
import ArrayHelper from "@/helpers/array-helper";
import {Alert} from "@/models/alert";

const alerts: Alert[]|UnwrapRefSimple<any>[] & ReactiveMarker = reactive([]);

export const useTopAlerts = ()  => {
  const add = (alert: Alert): void => {
    alert.id = generateNewAlertId();
    alerts.push(alert);
    removeAfterTimeout(alert);
  };

  const remove = (alert: Alert): void => {
    const index: number = alerts.indexOf(alert);
    if (index !== -1) {
      ArrayHelper.remove(alerts, index);
    }
  };

  const generateNewAlertId = (): number => {
    let highestAlertId: null|number = getHighestAlertId();
    return highestAlertId !== null ? highestAlertId + 1: 0;
  };

  const getHighestAlertId = (): null|number => {
    if (alerts.length === 0) {
      return null;
    }

    return alerts.reduce(
      (max: number, alert: Alert): number => (alert.id > max ? alert.id : max),
      alerts[0].id
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