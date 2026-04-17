<script setup lang="ts">
import client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts.ts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { type Ref, ref, type UnwrapRef, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const auth = useAuth()
const route = useRoute()
const router = useRouter()
const topAlerts = useTopAlerts()

const message401: Ref<UnwrapRef<string | null>> = ref(null)
const isLoading = ref(false)

const signIn = (event: SubmitEvent) => {
  isLoading.value = true
  message401.value = null

  const formData = new FormData(event.target as HTMLFormElement)
  const formValues = Object.fromEntries(formData.entries())

  client
    .signIn(formValues)
    .then((response) => {
      auth.signIn(response.data.token)
    })
    .catch((axiosError) => {
      message401.value = 'Invalid credentials'
      if (
        axiosError.response.status === 401 &&
        Object.prototype.hasOwnProperty.call(axiosError.response.data, 'message')
      ) {
        message401.value = axiosError.response.data.message
      }
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
    <div class="form-floating mb-3">
      <input id="email" name="email" type="email" class="form-control" placeholder="" />
      <label for="email">Email address</label>
    </div>

    <div class="form-floating mb-3">
      <input id="password" name="password" type="password" class="form-control" placeholder="" />
      <label for="password">Password</label>
    </div>

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">
      Submit
    </button>

    <div v-if="message401 !== null" class="alert alert-danger mb-3" role="alert">
      {{ message401 }}
    </div>

    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </form>
</template>
