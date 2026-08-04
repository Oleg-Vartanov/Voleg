<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, useId } from 'vue'
import type { FormSelectOption } from '@/modules/core/components/form/types'

const modelValue = defineModel<string | number>({ required: true })

const props = withDefaults(
  defineProps<{
    options: FormSelectOption[]
    ariaLabel?: string
  }>(),
  {
    ariaLabel: 'Select',
  },
)

const id = useId()
const rootEl = ref<HTMLElement | null>(null)
const listEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)
const openUp = ref(false)

const label = computed(() => {
  const option = props.options.find((entry) => entry.value === modelValue.value)
  return option?.label ?? String(modelValue.value)
})

async function toggle() {
  if (isOpen.value) {
    isOpen.value = false
    return
  }

  isOpen.value = true
  openUp.value = false
  await nextTick()

  const root = rootEl.value
  const list = listEl.value
  if (!root || !list) return

  const rootRect = root.getBoundingClientRect()
  const listHeight = list.offsetHeight
  const spaceBelow = window.innerHeight - rootRect.bottom
  openUp.value = spaceBelow < listHeight && rootRect.top > spaceBelow
}

function select(option: FormSelectOption) {
  modelValue.value = option.value
  isOpen.value = false
}

function onDocumentClick(event: MouseEvent) {
  if (!isOpen.value) return
  if (rootEl.value?.contains(event.target as Node)) return
  isOpen.value = false
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <div
    ref="rootEl"
    class="form-select-menu"
    :class="{
      'form-select-menu--open': isOpen,
      'form-select-menu--up': isOpen && openUp,
    }"
  >
    <button
      :id="id"
      type="button"
      class="form-select-menu-trigger"
      :aria-label="ariaLabel"
      :aria-expanded="isOpen"
      :aria-controls="`${id}-listbox`"
      @click="toggle"
    >
      {{ label }}
    </button>

    <ul
      v-if="isOpen"
      :id="`${id}-listbox`"
      ref="listEl"
      class="form-select-menu-dropdown list-unstyled mb-0"
      :class="{ 'form-select-menu-dropdown--up': openUp }"
      role="listbox"
      @mousedown.prevent
    >
      <li
        v-for="option in options"
        :key="option.value"
        class="form-select-menu-option"
        :class="{ 'form-select-menu-option--active': option.value === modelValue }"
        role="option"
        :aria-selected="option.value === modelValue"
        @click="select(option)"
      >
        {{ option.label }}
      </li>
    </ul>
  </div>
</template>

<style scoped>
.form-select-menu {
  position: relative;
  display: flex;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  align-self: stretch;
}

.form-select-menu-trigger {
  display: flex;
  flex: 1 1 auto;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  width: 100%;
  min-width: 0;
  margin: 0;
  padding: 0.25rem 0.35rem;
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: 0;
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  font-size: 0.875rem;
  line-height: 1.5;
  cursor: pointer;
}

.form-select-menu-trigger:focus {
  border-color: var(--bs-primary);
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
  z-index: 5;
}

.form-select-menu--open .form-select-menu-trigger {
  border-color: var(--bs-primary);
  box-shadow: none;
  z-index: 5;
}

.form-select-menu--open:not(.form-select-menu--up) .form-select-menu-trigger {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.form-select-menu--up .form-select-menu-trigger {
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.form-select-menu-dropdown {
  position: absolute;
  top: calc(100% - var(--bs-border-width));
  left: 0;
  z-index: 6;
  width: 100%;
  max-height: 12.5rem;
  overflow-y: auto;
  background-color: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-primary);
  border-top: 0;
  border-bottom-left-radius: var(--bs-border-radius);
  border-bottom-right-radius: var(--bs-border-radius);
  box-shadow: 0 0.375rem 0.75rem rgba(0, 0, 0, 0.08);
}

.form-select-menu-dropdown--up {
  top: auto;
  bottom: calc(100% - var(--bs-border-width));
  border-top: var(--bs-border-width) solid var(--bs-primary);
  border-bottom: 0;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  border-top-left-radius: var(--bs-border-radius);
  border-top-right-radius: var(--bs-border-radius);
  box-shadow: 0 -0.375rem 0.75rem rgba(0, 0, 0, 0.08);
}

.form-select-menu-option {
  padding: 0.25rem 0.35rem;
  cursor: pointer;
  color: var(--bs-body-color);
  font-size: 0.875rem;
  line-height: 1.5;
  text-align: center;
}

.form-select-menu-option:hover,
.form-select-menu-option--active {
  background-color: var(--bs-primary);
  color: var(--bs-body-bg);
}
</style>
