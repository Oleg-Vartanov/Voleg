<script setup lang="ts">
import { onUnmounted, ref, useAttrs, watch, type CSSProperties } from 'vue'

defineOptions({ inheritAttrs: false })

const props = withDefaults(
  defineProps<{
    open: boolean
    openUp?: boolean
    invalid?: boolean
    panelStyle?: CSSProperties | Record<string, string>
  }>(),
  {
    openUp: false,
    invalid: false,
    panelStyle: () => ({})
  }
)

/** Parent binds this for useFixedDropdown outside-click / panel checks. */
const el = defineModel<HTMLElement | null>('el', { default: null })

const attrs = useAttrs()
const rootEl = ref<HTMLElement | null>(null)

watch(
  rootEl,
  (node) => {
    el.value = node
  },
  { immediate: true }
)

watch(
  () => props.open,
  (open) => {
    if (!open) el.value = null
  }
)

onUnmounted(() => {
  el.value = null
})

defineExpose({ rootEl })
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      ref="rootEl"
      class="form-select-dropdown"
      :class="{
        'form-select-dropdown--up': openUp,
        'form-select-dropdown--invalid': invalid
      }"
      :style="panelStyle"
      v-bind="attrs"
      @mousedown.prevent
    >
      <div v-if="$slots.header" class="form-select-dropdown-header">
        <slot name="header" />
      </div>
      <div class="form-select-dropdown-body">
        <slot />
      </div>
    </div>
  </Teleport>
</template>

<style>
.form-select-dropdown {
  position: fixed;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: var(--bs-border-radius);
  box-shadow: var(--bs-box-shadow);
}

.form-select-dropdown--invalid {
  border-color: var(--bs-form-invalid-border-color);
}

.form-select-dropdown-header {
  flex: 0 0 auto;
  border-bottom: var(--bs-border-width) solid var(--bs-border-color);
}

.form-select-dropdown--up .form-select-dropdown-header {
  order: 1;
}

.form-select-dropdown-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
}

.form-select-dropdown-search {
  display: block;
  width: 100%;
  margin: 0;
  padding: 0.5rem 0.75rem;
  border: 0;
  background: transparent;
  color: var(--bs-body-color);
  font: inherit;
  line-height: 1.5;
  outline: 0;
  box-shadow: none;
}

.form-select-dropdown-search::placeholder {
  color: var(--bs-secondary-color);
  opacity: 0.85;
}

.form-select-dropdown-search::-webkit-search-decoration,
.form-select-dropdown-search::-webkit-search-cancel-button {
  -webkit-appearance: none;
}

.form-select-dropdown-empty {
  padding: 0.5rem 0.75rem;
  color: var(--bs-secondary-color);
  font-size: var(--ov-font-size-sm);
}

.form-select-dropdown-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.5rem 0.75rem;
  color: var(--bs-body-color);
  text-align: left;
  background: transparent;
  border: 0;
  cursor: pointer;
  font: inherit;
  line-height: 1.5;
}

.form-select-dropdown-option:hover,
.form-select-dropdown-option:focus,
.form-select-dropdown-option.is-active,
.form-select-dropdown-option.is-highlighted {
  color: var(--bs-body-bg);
  background: var(--bs-primary);
  outline: 0;
}
</style>
