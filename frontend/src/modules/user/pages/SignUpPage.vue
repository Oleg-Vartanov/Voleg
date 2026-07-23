<script setup lang="ts">
import Client from '@/modules/core/apiClient'
import { type Router, useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { ref, reactive } from 'vue'
import FormInput from '@/modules/core/components/form/FormInput.vue'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation.ts'

const topAlerts = useTopAlerts()
const router: Router = useRouter()
const validation = useApiValidation()

const isLoading = ref(false)
const model = reactive({
  username: '',
  email: '',
  password: ''
})

const signUp = () => {
  isLoading.value = true
  validation.reset()

  Client.signUp({ ...model })
    .then(() => {
      topAlerts.add(
        "You've signed up! All that's left is to verify your account via email.",
        'success',
        30
      )
      router.push({ name: 'signIn' })
    })
    .catch((axiosError) => {
      if (axiosError.response.status === 422) {
        validation.applyErrors(axiosError.response.data.violations)
      } else {
        topAlerts.add('Failed to sign up.', 'danger')
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}
</script>

<template>
  <form @submit.prevent="signUp">
    <FormInput
      id="username"
      v-model="model.username"
      label="Username"
      :is-valid="validation.isValid('username')"
      :error-text="validation.getError('username')"
      help-text="Your unique public name used for display and search."
    />

    <FormInput
      id="email"
      v-model="model.email"
      label="Email address"
      type="email"
      :is-valid="validation.isValid('email')"
      :error-text="validation.getError('email')"
      help-text="Your email will stay private, it wont be shared."
    />

    <FormInput
      id="password"
      v-model="model.password"
      label="Password"
      type="password"
      :is-valid="validation.isValid('password')"
      :error-text="validation.getError('password')"
    />

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">
      Create Account
    </button>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </form>
</template>
