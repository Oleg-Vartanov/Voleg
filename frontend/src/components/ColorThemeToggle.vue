<script setup lang="ts">
import { ref, watch  } from 'vue';

const isChecked = ref(false);

let element = document.getElementsByTagName("body")[0];
watch(isChecked, async (newIsChecked, oldIsChecked) => {
  element.setAttribute("data-bs-theme", newIsChecked ? "dark" : "light");
})
</script>

<template>
  <div class="form-check form-switch">
    <input v-model="isChecked" class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
    <label class="form-check-label" for="flexSwitchCheckChecked">
      <i class="bi" :class="[isChecked ? 'bi-moon-stars-fill' : 'bi-moon-stars']"></i>
    </label>
  </div>
</template>