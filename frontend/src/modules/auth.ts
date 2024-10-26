import type { Ref, UnwrapRef } from 'vue';
import type { Router } from 'vue-router';
import { ApiToken } from '@/models/api-token';
import { useRouter } from 'vue-router';
import { ref, readonly } from 'vue';

// const token: Ref<UnwrapRef<ApiToken>> = ref(new ApiToken());

export const useAuth = () => {
  const router: Router = useRouter();
  const token: Ref<UnwrapRef<ApiToken>> = ref(new ApiToken());

  const signIn = (params: object): void => {
    token.value.value = params.token;
    token.value.expiresAtTimestamp = params.expiresAtTimestamp;

    console.log(token.value);

    // TODO: View info message.
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
    authState: readonly({
      token: token,
    }),
    signIn,
    signOut,
  };
};
