import {reactive, watch} from "vue";
import ArrayHelper from "@/helpers/array-helper";
import {Alert} from "@/models/alert";

const alerts = reactive([]);

export const useTopAlerts = ()  => {
  const add = (alert: Alert): void => {
    alerts.push(alert);

    // Remove after timeout (countdown runs ouw).
    if (alert.timeout > 0) {
      watch(alert.countdown, (newValue: number): void => {
        if (newValue === 0) {
          remove(alert);
        }
      });
    }
  };

  const remove = (alert: Alert): void => {
    const index: number = alerts.indexOf(alert);
    if (index !== -1) {
      ArrayHelper.remove(alerts, index);
    }
  };

  return { alerts, add, remove };
};