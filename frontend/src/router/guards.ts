import { useAuth } from '@/modules/auth';
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";

export const useGuard = () => {
  const auth = useAuth();
  const topAlerts = useTopAlerts();

  function isAuthenticated(to: object, from: object, next: any): void {
    if (auth.isTokenValid()) {
      next();
    } else {
      topAlerts.add(new Alert('Not authenticated.', 'info', 5));
      auth.reset();
      next({name: 'signIn'});
    }
  }

  function hasRole(roles: string[]) {
    return function(to, from, next) {
      if (auth.hasRole(roles)) {
        next();
      } else {
        topAlerts.add(new Alert('Forbidden.', 'danger', 5));
        next({name: 'home'});
      }
    };
  }

  return { isAuthenticated, hasRole };
}