<script setup lang="ts">
defineProps(['teamName'])

function getTag(name: string) {
  return name.toLowerCase().replace(' ', '-');
}
</script>

<template>
  <img :src="'/team-logo/'+getTag(teamName)+'.png'"
       width="20" height="20"
       alt="logo">
</template>