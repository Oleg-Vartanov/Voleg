<script setup lang="ts">
import Client from '@/modules/core/apiClient'
import { type Router, useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { ref, reactive } from 'vue'
import FormField from '@/modules/core/components/form/FormField.vue'
import { useApiValidation } from '@/modules/core/composables/form/useApiValidation.ts'

const topAlerts = useTopAlerts()
const router: Router = useRouter()
const validation = useApiValidation()

const isLoading = ref(false)
const model = reactive({
  displayName: '',
  tag: '',
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
    <FormField
      id="displayName"
      v-model="model.displayName"
      label="Display Name"
      :error="validation.errors.value.displayName"
      :is-validation-error="validation.isError.value"
      help-text="Your public name displayed on the platform."
    />

    <FormField
      id="tag"
      v-model="model.tag"
      label="Tag"
      :error="validation.errors.value.tag"
      :is-validation-error="validation.isError.value"
      help-text="A unique tag used for search purposes."
    />

    <FormField
      id="email"
      v-model="model.email"
      label="Email address"
      type="email"
      :error="validation.errors.value.email"
      :is-validation-error="validation.isError.value"
      help-text="Your email will stay private, it wont be shared."
    />

    <FormField
      id="password"
      v-model="model.password"
      label="Password"
      type="password"
      :error="validation.errors.value.password"
      :is-validation-error="validation.isError.value"
    />

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">
      Create Account
    </button>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </form>
</template>
