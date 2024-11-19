<script setup lang="ts">
import RouterHelper from '@/helpers/router-helper';
import Client from '@/modules/api-client';
import {type Router, useRouter} from 'vue-router';
import {useTopAlerts} from '@/modules/top-alerts';
import {ref, reactive} from 'vue';
import {Alert} from "@/models/alert";

const topAlerts = useTopAlerts();
const router: Router = useRouter();

const fields = reactive({
  'displayName': { 'isValid': null, 'errorMessage': ''},
  'email': { 'isValid': null, 'errorMessage': ''},
  'password': { 'isValid': null, 'errorMessage': ''},
});
const isLoading = ref(false);

const resetResult = () => {
  Object.keys(fields).forEach((v) => {
    fields[v]['isValid'] = null;
    fields[v]['errorMessage'] = '';
  })
}

const signUp = (event: SubmitEvent) => {
  isLoading.value = true;
  resetResult();

  const formData = new FormData(event.target as HTMLFormElement);
  const formValues = Object.fromEntries(formData.entries());

  Client.signUp(formValues)
    .then(() => {
      topAlerts.add(new Alert('You\'ve signed up! All that\'s left is to verify your account via email.', 'success', 30));
      router.push({ name: 'signIn' });
    })
    .catch((axiosError) => {
      axiosError.response.data.violations.forEach(violation => {
        const property = violation.propertyPath;
        if (fields.hasOwnProperty(property)) {
          fields[property].isValid = false;
          fields[property].errorMessage += violation.title + "<br>";
        }
      });

      Object.keys(fields).forEach((field) => {
        if (fields[field]['isValid'] === null) {
          fields[field]['isValid'] = true;
        }
      })
    })
    .finally(() => {
      isLoading.value = false;
    });
}
</script>

<template>
  <form @submit.prevent="signUp">

    <input name="verificationEmailRedirectUrl" hidden type="url" :value="RouterHelper.url('signIn')">

    <div class="form-floating mb-3">
      <input
        name="displayName"
        type="text"
        class="form-control"
        :class="[ fields.displayName.isValid === null ? '' : (fields.displayName.isValid ? 'is-valid' : 'is-invalid') ]"
        aria-describedby="display-name-validation-feedback"
        placeholder=""
        required
      >
      <label for="displayName">Display Name</label>
      <div
        v-if="fields.displayName.isValid === false"
        id="display-name-validation-feedback"
        class="invalid-feedback"
        v-html="fields.displayName.errorMessage">
      </div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="email"
        type="email"
        class="form-control"
        :class="[ fields.email.isValid === null ? '' : (fields.email.isValid ? 'is-valid' : 'is-invalid') ]"
        aria-describedby="email-validation-feedback"
        placeholder=""
        required
      >
      <label for="email">Email address</label>
      <div
        v-if="fields.email.isValid === false"
        id="email-validation-feedback"
        class="invalid-feedback"
        v-html="fields.email.errorMessage">
      </div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="password"
        type="password"
        class="form-control"
        :class="[ fields.password.isValid === null ? '' : (fields.password.isValid ? 'is-valid' : 'is-invalid') ]"
        aria-describedby="password-validation-feedback"
        placeholder=""
        required
      >
      <label for="password">Password</label>
      <div
          v-if="fields.password.isValid === false"
        id="password-validation-feedback"
        class="invalid-feedback"
        v-html="fields.password.errorMessage">
      </div>
    </div>

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">Create Account</button>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

  </form>
</template>