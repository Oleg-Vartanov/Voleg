<script setup lang="ts">
import {useRouter} from 'vue-router'

const router = useRouter();
</script>

<template>
  <div class="ov-center">
    <main class="form-signin w-100 m-auto">
      <div class="btn-group mb-3 w-100" role="group" aria-label="Basic radio toggle button group">
        <input
          @click="router.push({ name: 'signIn' })"
          type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
        <label class="btn btn-outline-primary" for="btnradio1">Sign In</label>

        <input @click="router.push({ name: 'signUp' })"
               type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
        <label class="btn btn-outline-primary" for="btnradio2">Sign Up</label>
      </div>

      <router-view></router-view>
    </main>
  </div>
</template>

<style>
html, body {
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