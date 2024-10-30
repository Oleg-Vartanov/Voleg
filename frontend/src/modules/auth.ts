import type {Router} from 'vue-router';
import {Alert} from "@/models/alert";
import {useRouter} from 'vue-router';
import {useTopAlerts} from '@/modules/top-alerts';

const topAlerts = useTopAlerts();

export const useAuth = () => {
  const router: Router = useRouter();

  const signIn = (params: object): void => {
    setToken(params.token);
    topAlerts.add(new Alert('Successfully signed in. Welcome!', 'success', 15));
    router.push({ name: 'home' });
  }

  const signOut = (): void => {
    resetToken();
    router.push({ name: 'signIn' });
  };

  const getToken = (): string => {
    return window.localStorage.getItem('voleg-jwt');
  }

  const setToken = (token: string): void => {
    window.localStorage.setItem('voleg-jwt', token);
  }

  const resetToken = (): void => {
    window.localStorage.removeItem('voleg-jwt');
  }

  return {
    signIn,
    signOut,
    getToken,
  };
};
