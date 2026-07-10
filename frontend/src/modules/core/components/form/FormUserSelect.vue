<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import { validationClass } from '@/modules/core/components/form/types'
import UserSearch from '@/modules/core/components/UserSearch.vue'
import type { ApiUser } from '@/modules/core/apiType'

interface Props {
  id: string
  label: string
  users: ApiUser[]
  isValid?: boolean | null
  errorText?: string
  excludeSelf?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  excludeSelf: false
})

const modelValue = defineModel<ApiUser | null>({ required: true })

const rootEl = ref<HTMLElement | null>(null)
const isOpen = ref(false)

const fieldValidationClass = computed(() => validationClass(props.isValid))
const isInvalid = computed(() => props.isValid === false)

const displayLabel = computed(() => {
  const user = modelValue.value
  if (!user) return 'Select user…'
  return `${user.displayName} (@${user.tag})`
})

function selectUser(user: ApiUser) {
  modelValue.value = user
  isOpen.value = false
}

function toggleOpen() {
  isOpen.value = !isOpen.value
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
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText">
    <div
      ref="rootEl"
      class="form-user-select"
      :class="{ 'form-user-select--open': isOpen, 'form-user-select--invalid': isInvalid }"
    >
      <div class="form-floating">
        <button
          :id="id"
          type="button"
          class="form-select form-user-select-trigger text-start text-truncate"
          :class="fieldValidationClass"
          :aria-expanded="isOpen"
          :aria-controls="`${id}-picker`"
          @click.stop="toggleOpen"
        >
          {{ displayLabel }}
        </button>
        <label :for="id">{{ label }}</label>
      </div>

      <div
        v-if="isOpen"
        :id="`${id}-picker`"
        class="form-user-select-panel"
        @click.stop
      >
        <UserSearch
          :users="users"
          :exclude-self="excludeSelf"
          embedded
          action-label="Select"
          :validation-id="`${id}-validation`"
          @action="selectUser"
        />
      </div>
    </div>
  </BaseFormField>
</template>

<style scoped>
.form-user-select-trigger {
  width: 100%;
  background-color: var(--bs-body-bg);
}

.form-user-select--open .form-user-select-trigger {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  box-shadow: none;
}

.form-user-select-panel {
  margin-top: -1px;
  padding: 0.75rem;
  background-color: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-top: none;
  border-bottom-left-radius: var(--bs-border-radius);
  border-bottom-right-radius: var(--bs-border-radius);
  box-shadow: 0 0.375rem 0.75rem rgba(0, 0, 0, 0.08);
}

.form-user-select--open.form-user-select--invalid .form-user-select-trigger,
.form-user-select--open.form-user-select--invalid .form-user-select-panel {
  border-color: var(--bs-form-invalid-border-color);
}

.form-user-select--open:not(.form-user-select--invalid) .form-user-select-trigger,
.form-user-select--open:not(.form-user-select--invalid) .form-user-select-panel {
  border-color: var(--bs-primary);
}

.form-user-select--open:not(.form-user-select--invalid) .form-user-select-trigger:focus {
  border-color: var(--bs-primary);
  box-shadow: none;
}
</style>
