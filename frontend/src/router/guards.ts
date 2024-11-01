import { useAuth } from '@/modules/auth';

export const useGuard = () => {
  const {reset, isTokenValid} = useAuth();

  function isAuthenticated(to: object, from: object, next: any): void {
    if (isTokenValid()) {
      next();
    } else {
      reset();
      next({name: 'signIn'});
    }
  }

  return { isAuthenticated };
}