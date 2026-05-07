<script setup lang="ts">
import client from '@/modules/core/apiClient.ts'
import { ref } from 'vue'
import FormField from '@/modules/core/components/form/FormField.vue'
import FormButton from '@/modules/core/components/form/FormButton.vue'
import { useRoute, useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation.ts';

const topAlerts = useTopAlerts()
const route = useRoute()
const router = useRouter()
const validation = useApiValidation()

const isLoading = ref(false)
const password = ref('');

const submit = () => {
  isLoading.value = true

  client
    .passwordReset(
      route.query.selector as string,
      route.query.secret as string,
      password.value,
    )
    .then(() => {
      router.push({ name: 'signIn' })
      topAlerts.add('Password updated successfully.', 'success', 5)
    })
    .catch((axiosError) => {
      if (axiosError.response.status === 422) {
        validation.applyErrors(axiosError.response.data.violations)
      } else if (axiosError.response.status === 403) {
        topAlerts.add('Invalid reset token. It may be expired. Try to request password change again.', 'warning')
      } else {
        topAlerts.add('Failed to reset password.', 'danger')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <form @submit.prevent="submit">
    <FormField
      id="password"
      v-model="password"
      label="New Password"
      type="password"
      :is-valid="validation.isValid('password')"
      :error-text="validation.getError('password')"
    />
    <FormButton label="Reset Password" variant="primary" :loading="isLoading" type="submit" />
  </form>
</template>

<style scoped></style>
