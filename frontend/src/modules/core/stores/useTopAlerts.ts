import { defineStore } from 'pinia'
import { ref, type Ref } from 'vue'
import arrayUtils from '@/modules/core/utils/arrayUtils'

export type AlertType = 'primary' | 'success' | 'danger' | 'info' | 'warning'

export interface Alert {
  id: number
  text: string
  type: AlertType
  timeout: number
  countdown: Ref<number>
}

export const useTopAlerts = defineStore('topAlerts', () => {
  const alerts = ref<Alert[]>([])

  function createAlert(
    text: string,
    type: AlertType = 'primary',
    timeout: number = 10
  ): Alert {
    const countdown = ref(timeout)

    const alert: Alert = {
      id: 0,
      text,
      type,
      timeout,
      countdown
    }

    if (timeout > 0) {
      const tick = () => {
        setTimeout(() => {
          if (countdown.value > 0) {
            countdown.value--

            if (countdown.value === 0) {
              remove(alert)
              return
            }

            tick()
          }
        }, 1000)
      }

      tick()
    }

    return alert
  }

  const add = (
    text: string,
    type: AlertType = 'primary',
    timeout: number = 10
  ) => {
    const alert = createAlert(text, type, timeout)

    alert.id = generateNewAlertId()

    alerts.value.push(alert)
  }

  const remove = (alert: Alert): void => {
    const index = alerts.value.findIndex(a => a.id === alert.id)

    if (index !== -1) {
      arrayUtils.removeIndex(alerts.value, index)
    }
  }

  const generateNewAlertId = (): number => {
    const highestAlertId = getHighestAlertId()

    return highestAlertId !== null
      ? highestAlertId + 1
      : 0
  }

  const getHighestAlertId = (): number | null => {
    if (alerts.value.length === 0) {
      return null
    }

    return alerts.value.reduce(
      (max: number, alert: Alert): number =>
        alert.id > max ? alert.id : max,
      alerts.value[0].id
    )
  }

  return {
    alerts,
    add,
    remove
  }
})