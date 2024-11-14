import type {Router} from 'vue-router';
import {Alert} from '@/models/alert';
import {reactive, readonly} from 'vue';
import {useRouter} from 'vue-router';
import {useTopAlerts} from '@/modules/top-alerts';
import {jwtDecode} from 'jwt-decode';

const localStorageKey = 'voleg-jwt'
const topAlerts = useTopAlerts();

const user = reactive({
  isSignedIn: false,
  id: null,
  displayName: null,
});
setUserByToken();

function setUserByToken(): void {
  const token: string | null = getToken();
  if (token !== null) {
    const decodedToken = jwtDecode(token);
    user.isSignedIn = isTokenValid();
    user.id = decodedToken['id'] ?? null;
    user.displayName = decodedToken['displayName'] ?? null;
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
    topAlerts.add(new Alert('Successfully signed in. Welcome!', 'success', 10));
    router.push({ name: 'home' });
  }

  function signOut(): void {
    reset();
    topAlerts.add(new Alert('Signed out.', 'success', 5));
    router.push({ name: 'signIn' });
  }

  function reset(): void {
    user.isSignedIn = false;
    resetToken();
  }

  return {
    user: readonly(user),
    signIn,
    signOut,
    reset,
    getToken,
    isTokenValid,
  };
};
