<script setup lang="ts">
import { ref, watch } from 'vue';

const localStorageKey = 'voleg-is-dark-theme'
const element = document.getElementsByTagName("body")[0];

const isDark = ref(initIsDark());
apply(isDark.value);

function initIsDark(): boolean {
  return window.localStorage.getItem(localStorageKey) === '1';
}

function saveIsDark(isDark: boolean = false): void {
  window.localStorage.setItem(localStorageKey, isDark ? '1' : '0');
}

function apply(isDark: boolean = false) {
  element.setAttribute("data-bs-theme", isDark ? "dark" : "light");
}

watch(isDark, async (newIsChecked, oldIsChecked) => {
  saveIsDark(newIsChecked);
  apply(newIsChecked);
})
</script>

<template>
  <div class="form-check form-switch">
    <input v-model="isDark" class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
    <label class="form-check-label" for="flexSwitchCheckChecked">
      <i class="bi" :class="[isDark ? 'bi-moon-stars-fill' : 'bi-moon-stars']"></i>
    </label>
  </div>
</template>