<script setup lang="ts">
import client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { onMounted, provide, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FormField from '@/modules/core/components/form/FormField.vue'
import type { ApiUser } from '@/modules/core/apiType.ts'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation.ts'
import FormButton from '@/modules/core/components/form/FormButton.vue'

const auth = useAuth()
const topAlerts = useTopAlerts()
const route = useRoute()
const router = useRouter()
const validation = useApiValidation()

const isLoading = ref(false)
const isEditing = ref(false)
const userData = ref<ApiUser>({})
const editForm = reactive({
  displayName: '',
  tag: '',
  email: ''
})
provide('isLoading', isLoading)

const fillForm = () => {
  editForm.displayName = userData.value.displayName
  editForm.tag = userData.value.tag
  editForm.email = userData.value.email
}

const buildUpdatePayload = (): Record<string, string> | null => {
  const payload: Record<string, string> = {}
  if (editForm.displayName !== userData.value.displayName) {
    payload.displayName = editForm.displayName
  }
  if (editForm.tag !== userData.value.tag) {
    payload.tag = editForm.tag
  }
  if (editForm.email !== userData.value.email) {
    payload.email = editForm.email
  }
  return Object.keys(payload).length > 0 ? payload : null
}

const openEditMode = () => {
  validation.reset()
  isEditing.value = true
}
const closeEditMode = () => {
  fillForm()
  validation.reset()
  isEditing.value = false
}

const loadProfile = () => {
  if (auth.user.id === null) return
  isLoading.value = true

  client
    .getUser(auth.user.id)
    .then((response) => {
      userData.value = response.data
      fillForm()
    })
    .catch(() => {
      topAlerts.add('Failed to load profile data.', 'danger')
    })
    .finally(() => {
      isLoading.value = false
    })
}

const saveProfile = () => {
  if (auth.user.id === null) {
    return
  }

  const payload = buildUpdatePayload()
  if (payload === null) {
    topAlerts.add('No changes to save.', 'info', 5)
    return
  }

  isLoading.value = true
  validation.reset()

  client
    .patchUser(auth.user.id, payload)
    .then((response) => {
      userData.value = response.data
      fillForm()
      auth.user.displayName = response.data.displayName
      auth.user.tag = response.data.tag
      isEditing.value = false
      topAlerts.add('Profile updated successfully.', 'success', 5)
    })
    .catch((axiosError) => {
      if (axiosError.response.status === 422) {
        validation.applyErrors(axiosError.response.data.violations)
      } else {
        topAlerts.add('Failed to update profile.', 'danger')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const showEmailChangeVerificationResult = () => {
  if (route.query.emailChange === 'success') {
    topAlerts.add('Your new email has been verified successfully.', 'success')
  } else if (route.query.emailChange === 'fail') {
    topAlerts.add('Email verification link is invalid or expired.', 'danger', 20)
  } else {
    return
  }

  router.replace({ name: 'profile', query: {} })
}

onMounted(() => {
  showEmailChangeVerificationResult()
  loadProfile()
})
</script>

<template>
  <h1 class="h4 mb-3 fw-normal">Profile info</h1>

  <div v-if="userData?.emailChange" class="alert alert-warning">
    Pending email verification: <strong>{{ userData?.emailChange }}</strong
    >.
  </div>

  <form @submit.prevent="saveProfile">
    <FormField
      id="displayName"
      v-model="editForm.displayName"
      label="Display Name"
      :error="validation.errors.value.displayName"
      :is-validation-error="validation.isError.value"
      help-text="Your public name displayed on the platform."
      :disabled="!isEditing"
    />

    <FormField
      id="tag"
      v-model="editForm.tag"
      label="Tag"
      :error="validation.errors.value.tag"
      :is-validation-error="validation.isError.value"
      help-text="A unique tag used for search purposes."
      :disabled="!isEditing"
    />

    <FormField
      id="email"
      v-model="editForm.email"
      label="Email address"
      type="email"
      :error="validation.errors.value.email"
      :is-validation-error="validation.isError.value"
      :disabled="!isEditing"
    />

    <template v-if="isEditing">
      <FormButton label="Save Changes" :loading="isLoading" type="submit" />
      <FormButton
        label="Cancel"
        variant="outline-secondary"
        :disabled="isLoading"
        @click="closeEditMode"
      />
    </template>
    <template v-else>
      <FormButton label="Edit Profile" :loading="isLoading" @click="openEditMode" />
      <FormButton
        label="Change Password"
        variant="outline-primary"
        :disabled="isLoading"
        @click="router.push({ name: 'passwordChange' })"
      />
    </template>
  </form>
</template>
