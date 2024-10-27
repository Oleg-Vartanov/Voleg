import type {Ref, UnwrapRef} from 'vue';
import type {Router} from 'vue-router';
import {ApiToken} from '@/models/api-token';
import {Alert} from "@/models/alert";
import {ref, readonly} from 'vue';
import {useRouter} from 'vue-router';
import {useTopAlerts} from '@/modules/top-alerts';

const topAlerts = useTopAlerts();
const token: Ref<UnwrapRef<ApiToken>> = ref(new ApiToken());

export const useAuth = () => {
  const router: Router = useRouter();

  const signIn = (params: object): void => {
    token.value = new ApiToken(params.token, params.expiresAtTimestamp);
    topAlerts.add(new Alert('Successfully signed in. Welcome!', 'success', 15));
    router.push({ name: 'home' });
  }

  const signOut = (): void => {
    resetToken();
    router.push({ name: 'signIn' });
  };

  const resetToken = (): void => {
    token.value = new ApiToken();
  }

  return {
    token: readonly(token),
    signIn,
    signOut,
  };
};
