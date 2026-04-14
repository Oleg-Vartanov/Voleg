import type { RouteLocationNormalized } from 'vue-router'
import { useAuth } from '@/modules/user/stores/useAuth'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'

export const useGuard = () => {
  function isAuthenticated(to: RouteLocationNormalized): void {
    const auth = useAuth()
    const topAlerts = useTopAlerts()

    if (auth.isTokenValid()) {
      return true
    } else {
      let message = 'Not authenticated.'
      if (to.name === 'footballPredictions') {
        message = 'Football Predictions requires to sign in. To keep track of results.'
      }
      topAlerts.add(message, 'info', 5)
      auth.reset()

      return { name: 'signIn' }
    }
  }

  function hasRole(roles: string[]) {
    return function () {
      const auth = useAuth()
      const topAlerts = useTopAlerts()

      if (auth.hasRole(roles)) {
        return true
      } else {
        topAlerts.add('Forbidden.', 'danger', 5)

        return { name: 'home' }
      }
    }
  }

  return { isAuthenticated, hasRole }
}
