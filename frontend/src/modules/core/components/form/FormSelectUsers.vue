<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import BaseFormField from '@/modules/core/components/form/BaseFormField.vue'
import { validationClass, type UserSearchFn } from '@/modules/core/components/form/types'
import type { ApiUser } from '@/modules/core/apiType'

const SEARCH_DEBOUNCE_MS = 300

interface Props {
  id: string
  label: string
  search: UserSearchFn
  isValid?: boolean | null
  errorText?: string
  placeholder?: string
  multiple?: boolean
  /** Always shown as non-removable tags (e.g. You). */
  lockedUsers?: ApiUser[]
  showTags?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isValid: null,
  errorText: '',
  placeholder: 'Search by username…',
  multiple: false,
  lockedUsers: () => [],
  showTags: true
})

const modelValue = defineModel<ApiUser | ApiUser[] | null>({ required: true })

const rootEl = ref<HTMLElement | null>(null)
const controlEl = ref<HTMLElement | null>(null)
const dropdownEl = ref<HTMLElement | null>(null)
const inputEl = ref<HTMLInputElement | null>(null)
const searchQuery = ref('')
const searchResults = ref<ApiUser[]>([])
const isSearching = ref(false)
const isOpen = ref(false)
const activeIndex = ref(-1)
const dropdownStyle = ref<Record<string, string>>({})

let debounceTimer: ReturnType<typeof setTimeout> | null = null
let searchId = 0

const fieldValidationClass = computed(() => validationClass(props.isValid))
const isInvalid = computed(() => props.isValid === false)

const selectedUsers = computed((): ApiUser[] => {
  if (props.multiple) {
    return Array.isArray(modelValue.value) ? modelValue.value : []
  }
  return modelValue.value && !Array.isArray(modelValue.value) ? [modelValue.value] : []
})

const removableTags = computed(() => {
  const lockedIds = new Set(props.lockedUsers.map((user) => user.id))
  return selectedUsers.value.filter((user) => !lockedIds.has(user.id))
})

const hasTags = computed(
  () => props.showTags && (props.lockedUsers.length > 0 || removableTags.value.length > 0)
)

const isDropdownVisible = computed(() => isOpen.value && searchResults.value.length > 0)

watch(searchResults, (users) => {
  activeIndex.value = users.length > 0 ? 0 : -1
})

watch(isDropdownVisible, async (visible) => {
  if (!visible) return
  await nextTick()
  updateDropdownPosition()
})

function clearDebounce() {
  if (debounceTimer !== null) {
    clearTimeout(debounceTimer)
    debounceTimer = null
  }
}

function updateDropdownPosition() {
  if (!isDropdownVisible.value || !controlEl.value) return

  const rect = controlEl.value.getBoundingClientRect()
  dropdownStyle.value = {
    top: `${rect.bottom - 1}px`,
    left: `${rect.left}px`,
    width: `${rect.width}px`
  }
}

watch(searchQuery, (searchQueryValue) => {
  clearDebounce()

  const query = searchQueryValue.trim()
  if (query === '') {
    searchId += 1
    searchResults.value = []
    isSearching.value = false
    return
  }

  isOpen.value = true
  debounceTimer = setTimeout(() => {
    debounceTimer = null
    void runSearch(query)
  }, SEARCH_DEBOUNCE_MS)
})

async function runSearch(query: string) {
  const requestId = ++searchId
  isSearching.value = true

  try {
    const users = await props.search(query)
    if (requestId !== searchId) return
    searchResults.value = users.slice(0, 5)
  } catch {
    if (requestId !== searchId) return
    searchResults.value = []
  } finally {
    if (requestId === searchId) {
      isSearching.value = false
    }
  }
}

function closeDropdown() {
  isOpen.value = false
  activeIndex.value = -1
}

function isAlreadySelected(user: ApiUser): boolean {
  if (props.lockedUsers.some((entry) => entry.id === user.id)) return true
  return selectedUsers.value.some((entry) => entry.id === user.id)
}

function selectUser(user: ApiUser) {
  if (isAlreadySelected(user)) {
    searchQuery.value = ''
    return
  }

  if (props.multiple) {
    modelValue.value = [...selectedUsers.value, user]
  } else {
    modelValue.value = user
    closeDropdown()
  }
  searchQuery.value = ''
}

function removeUser(userId: number) {
  if (props.multiple) {
    modelValue.value = selectedUsers.value.filter((user) => user.id !== userId)
  } else {
    modelValue.value = null
  }
  inputEl.value?.focus()
}

function onKeydown(event: KeyboardEvent) {
  if (!isDropdownVisible.value) {
    if (event.key === 'Escape') closeDropdown()
    return
  }

  const count = searchResults.value.length

  if (event.key === 'ArrowDown') {
    event.preventDefault()
    activeIndex.value = (activeIndex.value + 1) % count
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    activeIndex.value = (activeIndex.value - 1 + count) % count
  } else if (event.key === 'Enter') {
    event.preventDefault()
    const user = searchResults.value[activeIndex.value]
    if (user) selectUser(user)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    closeDropdown()
  }
}

function onDocumentClick(event: MouseEvent) {
  if (!isOpen.value) return
  const target = event.target as Node
  if (rootEl.value?.contains(target) || dropdownEl.value?.contains(target)) return
  closeDropdown()
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
  window.addEventListener('resize', updateDropdownPosition)
  window.addEventListener('scroll', updateDropdownPosition, true)
})
onUnmounted(() => {
  document.removeEventListener('click', onDocumentClick)
  window.removeEventListener('resize', updateDropdownPosition)
  window.removeEventListener('scroll', updateDropdownPosition, true)
  clearDebounce()
  searchId += 1
})
</script>

<template>
  <BaseFormField :id="id" :is-valid="isValid" :error-text="errorText">
    <div
      ref="rootEl"
      class="form-user-select-input"
      :class="{
        'form-user-select-input--open': isDropdownVisible,
        'form-user-select-input--invalid': isInvalid,
        'form-user-select-input--filled': hasTags || searchQuery !== ''
      }"
    >
      <div class="form-floating">
        <div
          ref="controlEl"
          class="form-control form-user-select-input-control"
          :class="fieldValidationClass"
          @click="inputEl?.focus()"
        >
          <template v-if="showTags">
            <span
              v-for="user in lockedUsers"
              :key="`locked-${user.id}`"
              class="form-user-select-input-tag badge text-bg-secondary"
            >
              <span class="text-truncate">@{{ user.username }}</span>
            </span>
            <span
              v-for="user in removableTags"
              :key="user.id"
              class="form-user-select-input-tag badge text-bg-secondary"
            >
              <span class="text-truncate">@{{ user.username }}</span>
              <button
                type="button"
                class="form-user-select-input-tag-remove"
                :aria-label="`Remove ${user.username}`"
                @click.stop="removeUser(user.id)"
              >
                <i class="bi bi-x" aria-hidden="true"></i>
              </button>
            </span>
          </template>

          <input
            :id="id"
            ref="inputEl"
            v-model="searchQuery"
            type="text"
            class="form-user-select-input-field"
            :placeholder="hasTags ? placeholder : ' '"
            autocomplete="off"
            role="combobox"
            :aria-expanded="isDropdownVisible"
            :aria-controls="`${id}-listbox`"
            :aria-activedescendant="
              activeIndex >= 0 ? `${id}-option-${searchResults[activeIndex]?.id}` : undefined
            "
            @focus="isOpen = true"
            @keydown="onKeydown"
          />

          <div
            v-if="isSearching"
            class="form-user-select-input-spinner spinner-border spinner-border-sm text-primary"
            role="status"
          >
            <span class="visually-hidden">Searching…</span>
          </div>
        </div>
        <label :for="id">{{ label }}</label>
      </div>
    </div>
  </BaseFormField>

  <Teleport to="body">
    <ul
      v-if="isDropdownVisible"
      :id="`${id}-listbox`"
      ref="dropdownEl"
      class="form-user-select-input-dropdown list-unstyled mb-0"
      :class="{ 'form-user-select-input-dropdown--invalid': isInvalid }"
      role="listbox"
      :style="dropdownStyle"
      @mousedown.prevent
    >
      <li
        v-for="(user, index) in searchResults"
        :id="`${id}-option-${user.id}`"
        :key="user.id"
        class="form-user-select-input-option"
        :class="{ 'form-user-select-input-option--active': index === activeIndex }"
        role="option"
        :aria-selected="index === activeIndex"
        @click="selectUser(user)"
        @mouseenter="activeIndex = index"
      >
        @{{ user.username }}
      </li>
    </ul>
  </Teleport>
</template>

<style scoped>
.form-user-select-input {
  position: relative;
}

.form-user-select-input-control {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.375rem;
  position: relative;
  min-height: calc(3.5rem + calc(var(--bs-border-width) * 2));
  height: auto;
  padding-top: 1.625rem;
  padding-bottom: 0.625rem;
  cursor: text;
}

.form-floating > .form-user-select-input-control ~ label {
  opacity: 0.65;
  transform: scale(1) translateY(0) translateX(0);
}

.form-user-select-input--filled .form-floating > .form-user-select-input-control ~ label,
.form-floating > .form-user-select-input-control:focus-within ~ label {
  color: rgba(var(--bs-body-color-rgb), 0.65);
  transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
  opacity: 0.65;
}

.form-user-select-input-control:focus-within {
  color: var(--bs-body-color);
  background-color: var(--bs-body-bg);
  border-color: var(--bs-primary);
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

.form-user-select-input--invalid .form-user-select-input-control:focus-within {
  border-color: var(--bs-form-invalid-border-color);
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-danger-rgb), 0.25);
}

.form-user-select-input-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  max-width: 100%;
  min-height: 1.625rem;
  padding: 0.2rem 0.5rem;
  font-size: 0.875em;
  font-weight: 500;
  line-height: 1.25;
}

.form-user-select-input-tag-remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.125rem;
  height: 1.125rem;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: transparent;
  color: inherit;
  line-height: 1;
  opacity: 0.85;
}

.form-user-select-input-tag-remove:hover,
.form-user-select-input-tag-remove:focus-visible {
  opacity: 1;
  background: rgba(255, 255, 255, 0.2);
}

.form-user-select-input-tag-remove .bi {
  font-size: 0.875rem;
  line-height: 1;
}

.form-user-select-input-field {
  flex: 1 1 6rem;
  min-width: 6rem;
  margin: 0;
  padding: 0;
  border: 0;
  background: transparent;
  color: inherit;
  outline: 0;
  box-shadow: none;
  line-height: 1.5;
}

.form-user-select-input-spinner {
  position: absolute;
  top: 50%;
  right: 0.75rem;
  margin-top: -0.5rem;
}

.form-user-select-input--open .form-user-select-input-control {
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.form-user-select-input--open:not(.form-user-select-input--invalid)
  .form-user-select-input-control {
  border-color: var(--bs-primary);
}

.form-user-select-input--open:not(.form-user-select-input--invalid)
  .form-user-select-input-control:focus-within {
  border-color: var(--bs-primary);
  box-shadow: none;
}

.form-user-select-input--open.form-user-select-input--invalid .form-user-select-input-control {
  border-color: var(--bs-form-invalid-border-color);
}
</style>

<style>
.form-user-select-input-dropdown {
  position: fixed;
  z-index: 1080;
  max-height: 12.5rem;
  overflow-y: auto;
  background-color: var(--bs-body-bg);
  border: var(--bs-border-width) solid var(--bs-primary);
  border-top: none;
  border-bottom-left-radius: var(--bs-border-radius);
  border-bottom-right-radius: var(--bs-border-radius);
  box-shadow: 0 0.375rem 0.75rem rgba(0, 0, 0, 0.08);
}

.form-user-select-input-dropdown--invalid {
  border-color: var(--bs-form-invalid-border-color);
}

.form-user-select-input-option {
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  color: var(--bs-body-color);
}

.form-user-select-input-option--active {
  background-color: var(--bs-primary);
  color: var(--bs-body-bg);
}
</style>
