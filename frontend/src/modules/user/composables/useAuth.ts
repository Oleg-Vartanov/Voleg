import type { Router } from 'vue-router';
import type { User } from '@/modules/user/type';
import arrayUtils from '@/modules/core/utils/arrayUtils';
import { reactive, readonly } from 'vue';
import { useRouter } from 'vue-router';
import { useTopAlerts } from '@/modules/core/composables/useTopAlerts';
import { jwtDecode } from 'jwt-decode';

const localStorageKey = 'voleg-jwt';
const topAlerts = useTopAlerts();

const defaultUser: User = {
  isSignedIn: false,
  id: null,
  displayName: null,
  roles: [],
  tag: null,
};
const user = reactive({ ...defaultUser });
setUserByToken();

function setUserByToken(): void {
  const token: string | null = getToken();
  if (token !== null) {
    const decodedToken = jwtDecode(token);
    user.isSignedIn = isTokenValid();
    user.id = decodedToken['id'] ?? null;
    user.displayName = decodedToken['displayName'] ?? null;
    user.roles = decodedToken['roles'] ?? [];
  }
}

function getToken(): string | null {
  return window.localStorage.getItem(localStorageKey);
}

function setToken(token: string): void {
  window.localStorage.setItem(localStorageKey, token);
}

function resetToken(): void {
  window.localStorage.removeItem(localStorageKey);
}

function isTokenValid(): boolean {
  const token: string | null = getToken();
  return token !== null && !isTokenExpired(token);
}

function isTokenExpired(jwtToken: string) {
  try {
    const decodedToken = jwtDecode(jwtToken);
    const currentTime = Date.now() / 1000;
    return decodedToken.exp < currentTime;
  } catch (error) {
    console.error('Error decoding token:', error);
    return true;
  }
}

export const useAuth = () => {
  const router: Router = useRouter();

  function signIn(params: object): void {
    setToken(params.token);
    setUserByToken();
    topAlerts.add('Successfully signed in. Welcome!', 'success');
    router.push({ name: 'home' });
  }

  function signOut(): void {
    reset();
    topAlerts.add('Signed out.', 'success', 5);
    router.push({ name: 'signIn' });
  }

  function reset(): void {
    Object.assign(user, defaultUser);
    resetToken();
  }

  function hasRole(roles: string[]): boolean {
    return arrayUtils.intersects(user.roles, roles);
  }

  return {
    user: readonly(user),
    hasRole,
    signIn,
    signOut,
    reset,
    getToken,
    isTokenValid,
  };
};
