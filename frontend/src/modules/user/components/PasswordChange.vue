<script setup lang="ts">
import client from '@/modules/core/apiClient.ts'
import { ref, reactive } from 'vue'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation.ts'
import FormField from '@/modules/core/components/form/FormField.vue'
import FormButton from '@/modules/core/components/form/FormButton.vue'
import { useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

const topAlerts = useTopAlerts()
const router = useRouter()
const validation = useApiValidation()

const isLoading = ref(false)
const model = reactive({
  currentPassword: '',
  newPassword: ''
})

const resetForm = () => {
  model.currentPassword = ''
  model.newPassword = ''
}

const changePassword = () => {
  isLoading.value = true
  validation.reset()

  client
    .passwordChange({ ...model })
    .then(() => {
      resetForm()
      router.push({ name: 'profileInfo' })
      topAlerts.add('Password changed successfully.', 'success', 5)
    })
    .catch((axiosError) => {
      if (axiosError.response.status === 422) {
        validation.applyErrors(axiosError.response.data.violations)
      } else if (axiosError.response.status === 429) {
        topAlerts.add(axiosError.response.data.message, 'danger')
      } else {
        topAlerts.add('Failed to change password.', 'danger')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <h1 class="h4 mb-3 fw-normal">Password</h1>
  <form @submit.prevent="changePassword">
    <FormField
      id="currentPassword"
      v-model="model.currentPassword"
      label="Current Password"
      type="password"
      :is-valid="validation.isValid('currentPassword')"
      :error-text="validation.getError('currentPassword')"
    />
    <FormField
      id="newPassword"
      v-model="model.newPassword"
      label="New Password"
      type="password"
      :is-valid="validation.isValid('newPassword')"
      :error-text="validation.getError('newPassword')"
    />
    <FormButton label="Change Password" variant="primary" :loading="isLoading" type="submit" />
    <FormButton
      label="Cancel"
      variant="outline-primary"
      :disabled="isLoading"
      @click="router.push({ name: 'profileInfo' })"
    />
  </form>
</template>

<style scoped></style>
