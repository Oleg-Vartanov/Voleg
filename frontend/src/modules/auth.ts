import type {Router} from 'vue-router';
import type {Ref, UnwrapRef} from 'vue';
import {Alert} from '@/models/alert';
import {readonly, ref} from 'vue';
import {useRouter} from 'vue-router';
import {useTopAlerts} from '@/modules/top-alerts';
import {jwtDecode} from 'jwt-decode';

const topAlerts = useTopAlerts();
const isSignedIn: Ref<UnwrapRef<boolean>> = ref(isTokenValid());

function getToken(): string | null {
  return window.localStorage.getItem('voleg-jwt');
}

function setToken(token: string): void {
  window.localStorage.setItem('voleg-jwt', token);
}

function resetToken(): void {
  window.localStorage.removeItem('voleg-jwt');
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
    isSignedIn.value = true;
    setToken(params.token);
    topAlerts.add(new Alert('Successfully signed in. Welcome!', 'success', 10));
    router.push({ name: 'home' });
  }

  function signOut(): void {
    isSignedIn.value = false;
    resetToken();
    topAlerts.add(new Alert('Signed out.', 'success', 5));
    router.push({ name: 'signIn' });
  }

  return {
    state: readonly({
      isSignedIn: isSignedIn,
    }),
    signIn,
    signOut,
    getToken,
  };
};
