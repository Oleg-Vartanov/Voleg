import { useAuth } from '@/modules/user/composables/useAuth';
import { useTopAlerts } from '@/modules/core/composables/useTopAlerts';

export const useGuard = () => {
  const auth = useAuth();
  const topAlerts = useTopAlerts();

  function isAuthenticated(to: object, from: object, next: any): void {
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