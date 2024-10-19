<template>
<!--  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">-->
    <ul class="nav justify-content-center">
      <li class="nav-item">
        <router-link class="nav-link active" :to="{ name: 'home' }">Home</router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link active" :to="{ name: 'about' }">About</router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link active" :to="{ name: 'signIn' }">Sign In</router-link>
      </li>
<!--      <li class="nav-item">-->
<!--        <a class="nav-link disabled" aria-disabled="true">Disabled</a>-->
<!--      </li>-->
    </ul>
<!--  </nav>-->
</template>
