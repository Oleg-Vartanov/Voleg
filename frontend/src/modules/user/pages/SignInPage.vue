<script setup lang="ts">
import client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { type Ref, ref, type UnwrapRef, onMounted, reactive, computed } from 'vue'
import { useRoute } from 'vue-router'
import FormField from '@/modules/core/components/form/FormField.vue'
import FormButton from '@/modules/core/components/form/FormButton.vue';

const auth = useAuth()
const route = useRoute()
const topAlerts = useTopAlerts()

const errorMessage: Ref<UnwrapRef<string | null>> = ref(null)
const isLoading = ref(false)
const model = reactive({ email: '', password: '' })

const isError = computed(() => {
  return errorMessage.value !== null
})

const signIn = () => {
  isLoading.value = true
  errorMessage.value = null

  client
    .signIn({ ...model })
    .then((response) => {
      auth.signIn(response.data.token)
    })
    .catch((axiosError) => {
      errorMessage.value = 'Invalid credentials'
      if (
        axiosError.response.status === 401 &&
        Object.prototype.hasOwnProperty.call(axiosError.response.data, 'message')
      ) {
        errorMessage.value = axiosError.response.data.message
      }
      topAlerts.add(errorMessage.value, 'danger', 5)
    })
    .finally(() => {
      isLoading.value = false
    })
}

function verificationMessage() {
  if (route.query.verify === 'success') {
    topAlerts.add('You have successfully verified the account. 🎉', 'success')
  }
  if (route.query.verify === 'fail') {
    topAlerts.add(
      "Verification link isn't valid anymore. Resent verification request to get the new one.",
      'danger'
    )
  }
}

onMounted(() => {
  verificationMessage()
})
</script>

<template>
  <form @submit.prevent="signIn">
    <FormField
      id="email"
      v-model="model.email"
      type="email"
      label="Email address"
      :is-valid="isError ? false : null"
    />
    <FormField
      id="password"
      v-model="model.password"
      type="password"
      label="Password"
      :is-valid="isError ? false : null"
    />
    <FormButton type="submit" label="Submit" :loading="isLoading" />
    <RouterLink :to="{ name: 'passwordForgot' }">
      <FormButton label="Forgot password?" :disabled="isLoading" variant="link" />
    </RouterLink>
  </form>
</template>
