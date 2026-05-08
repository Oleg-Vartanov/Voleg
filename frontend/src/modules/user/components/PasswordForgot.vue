<script setup lang="ts">
import client from '@/modules/core/apiClient.ts'
import { ref } from 'vue'
import FormField from '@/modules/core/components/form/FormField.vue'
import FormButton from '@/modules/core/components/form/FormButton.vue'
import { useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'

const topAlerts = useTopAlerts()
const router = useRouter()

const isLoading = ref(false)
const email = ref('')

const submit = () => {
  isLoading.value = true

  client
    .passwordForgot(email.value)
    .then(() => {
      router.push({ name: 'signIn' })
      topAlerts.add('Password reset was requested. Check your email.', 'success', 5)
    })
    .catch(() => {
      topAlerts.add('Failed to request password reset.', 'danger')
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <form @submit.prevent="submit">
    <FormField
      id="email"
      v-model="email"
      label="Email"
      type="email"
      help-text="Provide the email address associated with your account."
    />
    <FormButton
      label="Request Password Reset"
      variant="primary"
      :loading="isLoading"
      type="submit"
    />
  </form>
</template>

<style scoped></style>
