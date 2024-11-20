<script setup lang="ts">

const games = [
  {
    title: 'Football Predictions',
    description: 'Guess the result and score points against other players.',
    logo: '/football-predictions-logo.png',
    path: 'football-predictions',
  }
];

</script>

<template>
  <div class="ov-center">
    <div class="row row-cols-1 row-cols-md-6 g-4">
      <div class="col" v-for="game in games">
        <router-link :to="{ name: game.path }" class="text-decoration-none">
          <div class="card">
            <img :src="game.logo" class="card-img-top" alt="logo">
            <div class="card-body">
              <h5 class="card-title">{{ game.title }}</h5>
              <p class="card-text">{{ game.description }}</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>