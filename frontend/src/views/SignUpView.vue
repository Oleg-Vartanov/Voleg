<script setup lang="ts">
import RouterHelper from '@/helpers/router-helper';

const verificationEmailRedirectUrl = RouterHelper.url('signIn');
</script>

<template>
  <form>
    <div class="form-floating">
      <input type="text" class="form-control" id="floatingPassword" placeholder="">
      <label for="floatingPassword">Display Name</label>
    </div>
    <div class="form-floating mt-3">
      <input type="email" class="form-control" id="floatingInput" placeholder="">
      <label for="floatingInput">Email address</label>
    </div>
    <div class="form-floating mt-3">
      <input type="password" class="form-control" id="floatingPassword" placeholder="">
      <label for="floatingPassword">Password</label>
    </div>


    <button class="btn btn-primary w-100 py-2 my-3" type="submit">Create Account</button>
  </form>
</template>