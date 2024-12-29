<script setup lang="ts">
</script>

<template>
  <div class="ov-center">
    <h2>Nothing Here</h2>
    <p>404 Not Found</p>
    <img src="/404-confused-john-travolta.gif" alt="this slowpoke moves" class="img-responsive"/>
  </div>
</template>

<style>
/* Fill the container on smaller screens to match navbar. */
@media (max-width: 768px) { /* Bootstrap's `md` breakpoint for tablets and below */
  .img-responsive {
    width: 100%;
    height: auto;
    object-fit: cover;
  }
}
</style>