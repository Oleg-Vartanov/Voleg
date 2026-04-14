<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
</script>

<template>
  <div class="ov-center">
    <main class="form-signin w-100 m-auto">
      <div class="btn-group mb-3 w-100" role="group" aria-label="Basic radio toggle button group">
        <input
          id="sign-in"
          :checked="route.name === 'signIn'"
          type="radio"
          class="btn-check"
          @click="router.push({ name: 'signIn' })"
        />
        <label class="btn btn-outline-primary" for="sign-in">Sign In</label>

        <input
          id="sign-up"
          :checked="route.name === 'signUp'"
          type="radio"
          class="btn-check"
          @click="router.push({ name: 'signUp' })"
        />
        <label class="btn btn-outline-primary" for="sign-up">Sign Up</label>
      </div>

      <router-view />
    </main>
  </div>
</template>

<style>
html,
body {
  height: 100%;
}

.form-signin {
  max-width: 330px;
  padding: 1rem;
}

.form-signin .form-floating:focus-within {
  z-index: 2;
}
</style>
