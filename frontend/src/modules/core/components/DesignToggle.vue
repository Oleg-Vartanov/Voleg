<script setup lang="ts">
import { ref, watch } from 'vue'

const localStorageKey = 'voleg-design'
const element = document.getElementsByTagName('body')[0]

const isInk = ref(initIsInk())
apply(isInk.value)

// Ink is the default until the visitor switches it off.
function initIsInk(): boolean {
  return window.localStorage.getItem(localStorageKey) !== 'default'
}

function saveIsInk(isInk: boolean = false): void {
  window.localStorage.setItem(localStorageKey, isInk ? 'ink' : 'default')
}

function apply(isInk: boolean = false) {
  element.setAttribute('data-ov-design', isInk ? 'ink' : 'default')
}

watch(isInk, async (isChecked) => {
  saveIsInk(isChecked)
  apply(isChecked)
})
</script>

<template>
  <label class="design-toggle form-check form-switch">
    <input
      v-model="isInk"
      class="form-check-input"
      type="checkbox"
      role="switch"
      aria-label="Ink design"
    />
    <i class="bi" :class="[isInk ? 'bi-pen-fill' : 'bi-pen']"></i>
  </label>
</template>
