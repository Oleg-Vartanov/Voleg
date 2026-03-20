import { useAuth } from '@/modules/user/stores/useAuth';
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts';

export const useGuard = () => {
  function isAuthenticated(to: object, from: object, next: any): void {
    const auth = useAuth();
    const topAlerts = useTopAlerts();

    if (auth.isTokenValid()) {
      next();
    } else {
      topAlerts.add('Not authenticated.', 'info', 5);
      auth.reset();
      next({ name: 'signIn' });
    }
  }

  function hasRole(roles: string[]) {
    return function (to, from, next) {
      const auth = useAuth();
      const topAlerts = useTopAlerts();

      if (auth.hasRole(roles)) {
        next();
      } else {
        topAlerts.add('Forbidden.', 'danger', 5);
        next({ name: 'home' });
      }
    };
  }

  return { isAuthenticated, hasRole };
};