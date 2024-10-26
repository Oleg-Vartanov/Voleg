<script setup lang="ts">
import client from '../modules/api-client';
import { useAuth } from '@/modules/auth';
import { ref } from 'vue';

const auth = useAuth();

const is401 = ref(false);
const isLoading = ref(false);

const signIn = (event: SubmitEvent) => {
  isLoading.value = true;
  is401.value = false;

  const formData = new FormData(event.target as HTMLFormElement);
  const formValues = Object.fromEntries(formData.entries());

  client.signIn(formValues)
    .then((response) => {
      auth.signIn(response.data)
    })
    .catch((response) => {
      is401.value = true;
      }
    )
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <form @submit.prevent="signIn">
    <div class="form-floating mb-3">
      <input name="email" type="email" class="form-control" id="floatingInput" placeholder="">
      <label for="floatingInput">Email address</label>
    </div>
    <div class="form-floating mb-3">
      <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="">
      <label for="floatingPassword">Password</label>
    </div>
    <button class="btn btn-primary w-100 py-2 mb-3" type="submit">Submit</button>
    <div v-if="is401" class="alert alert-danger mb-3" role="alert">Invalid credentials</div>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </form>
</template>