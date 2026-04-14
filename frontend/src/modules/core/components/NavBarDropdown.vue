<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = withDefaults(defineProps<{
  isOpen: boolean;
  text: string;
  active?: boolean;
}>(), {
  active: false,
});

const emit = defineEmits<{
  (e: 'update:isOpen', value: boolean): void;
}>();

const root = ref<HTMLElement | null>(null);

const isOpenProxy = computed({
  get: () => props.isOpen,
  set: (v) => emit('update:isOpen', v),
});

function toggle() {
  isOpenProxy.value = !isOpenProxy.value;
}

function close() {
  isOpenProxy.value = false;
}

function onOutsideClick(e: MouseEvent) {
  if (root.value && !root.value.contains(e.target as Node)) {
    close();
  }
}

onMounted(() => document.addEventListener('click', onOutsideClick));
onBeforeUnmount(() => isOpenProxy.value = false);
</script>

<template>
  <li ref="root" class="nav-item">
    <a
      class="nav-link dropdown-toggle"
      :class="{ active: active }"
      href="#"
      aria-expanded="false"
      @click.prevent="toggle"
    >
      {{ text }}
    </a>

    <ul
      class="dropdown-menu"
      :class="{ show: isOpenProxy }"
      @click="close"
    >
      <slot/>
    </ul>
  </li>
</template>