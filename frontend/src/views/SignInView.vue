<script setup lang="ts">
import client from '@/modules/api-client';
import {useAuth} from '@/modules/auth';
import {type Ref, ref, type UnwrapRef} from 'vue';

const auth = useAuth();

const message401: Ref<UnwrapRef<string|null>> = ref(null);
const isLoading = ref(false);

const signIn = (event: SubmitEvent) => {
  isLoading.value = true;
  message401.value = null;

  const formData = new FormData(event.target as HTMLFormElement);
  const formValues = Object.fromEntries(formData.entries());

  client.signIn(formValues)
    .then((response) => {
      auth.signIn(response.data)
    })
    .catch((axiosError) => {
      message401.value = 'Invalid credentials';
      if (axiosError.response.status === 401 && axiosError.response.data.hasOwnProperty('message')) {
        message401.value = axiosError.response.data.message;
      }
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <form @submit.prevent="signIn">

    <div class="form-floating mb-3">
      <input name="email" type="email" class="form-control" id="email" placeholder="">
      <label for="email">Email address</label>
    </div>

    <div class="form-floating mb-3">
      <input name="password" type="password" class="form-control" id="password" placeholder="">
      <label for="password">Password</label>
    </div>

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">Submit</button>

    <div v-if="message401 !== null" class="alert alert-danger mb-3" role="alert">{{ message401 }}</div>

    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

  </form>
</template>