<script setup lang="ts">

import { useAuth } from "@/modules/auth";
import { useTopAlerts } from "@/modules/top-alerts";
import { Alert } from "@/models/alert";
import { type Router, useRouter } from "vue-router";

const router: Router = useRouter();
const auth = useAuth();
const topAlerts = useTopAlerts();

const games = [
  {
    title: 'Football Predictions',
    description: 'Guess the result and score points against other players.',
    logo: '/football-predictions-logo.png',
    path: 'football-predictions',
    requireSignIn: true
  }
];

function checkRequirements(game) {
  if (game.requireSignIn && !auth.user.isSignedIn) {
    topAlerts.add(new Alert(game.title+' requires to sign in. To keep track of results.', 'info', 5));
    return;
  }
  router.push({ name: game.path })
}

</script>

<template>
  <div class="ov-center ps-2 pe-2">
    <div class="row row-cols-1 row-cols-md-6 g-4">
      <div class="col" v-for="game in games">
        <a class="text-decoration-none" @click="checkRequirements(game)" href="javascript:void(0)">
          <div class="card">
            <img :src="game.logo" class="card-img-top" alt="logo">
            <div class="card-body">
              <h5 class="card-title">{{ game.title }}</h5>
              <p class="card-text">{{ game.description }}</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>