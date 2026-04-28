<script setup lang="ts">
import Client from '@/modules/core/apiClient'
import { type Router, useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { ref, reactive } from 'vue'

const topAlerts = useTopAlerts()
const router: Router = useRouter()

const fields = reactive({
  displayName: { isValid: null, errorMessage: '' },
  tag: { isValid: null, errorMessage: '' },
  email: { isValid: null, errorMessage: '' },
  password: { isValid: null, errorMessage: '' },
  code: { isValid: null, errorMessage: '' }
})
const isLoading = ref(false)

const resetResult = () => {
  Object.keys(fields).forEach((v) => {
    fields[v]['isValid'] = null
    fields[v]['errorMessage'] = ''
  })
}

const signUp = (event: SubmitEvent) => {
  isLoading.value = true
  resetResult()

  const formData = new FormData(event.target as HTMLFormElement)
  const formValues = Object.fromEntries(formData.entries())

  Client.signUp(formValues)
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
        axiosError.response.data.violations.forEach((violation) => {
          const property = violation.propertyPath
          if (Object.prototype.hasOwnProperty.call(fields, property)) {
            fields[property].isValid = false
            fields[property].errorMessage +=
              (fields[property].errorMessage ? '\n' : '') + violation.title
          }
        })

        Object.keys(fields).forEach((field) => {
          if (fields[field]['isValid'] === null) {
            fields[field]['isValid'] = true
          }
        })
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
    <div class="form-floating mb-3">
      <input
        name="code"
        type="text"
        class="form-control"
        :class="[
          fields.code.isValid === null ? '' : fields.code.isValid ? 'is-valid' : 'is-invalid'
        ]"
        aria-describedby="code-validation-feedback"
        placeholder=""
        required
      />
      <label for="code">Special Code</label>
      <div
        v-if="fields.code.isValid === false"
        id="code-validation-feedback"
        class="invalid-feedback"
        style="white-space: pre-line"
      >
        {{ fields.code.errorMessage }}
      </div>
      <div class="form-text">
        While site is in development sign-up is restricted and requires this code.
      </div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="displayName"
        type="text"
        class="form-control"
        :class="[
          fields.displayName.isValid === null
            ? ''
            : fields.displayName.isValid
              ? 'is-valid'
              : 'is-invalid'
        ]"
        aria-describedby="display-name-validation-feedback"
        placeholder=""
        required
      />
      <label for="displayName">Display Name</label>
      <div
        v-if="fields.displayName.isValid === false"
        id="display-name-validation-feedback"
        class="invalid-feedback"
        style="white-space: pre-line"
      >
        {{ fields.displayName.errorMessage }}
      </div>
      <div class="form-text">Your public name displayed on the platform.</div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="tag"
        type="text"
        class="form-control"
        :class="[fields.tag.isValid === null ? '' : fields.tag.isValid ? 'is-valid' : 'is-invalid']"
        aria-describedby="tag-validation-feedback"
        placeholder=""
        required
      />
      <label for="tag">Tag</label>
      <div
        v-if="fields.tag.isValid === false"
        id="tag-validation-feedback"
        class="invalid-feedback"
        style="white-space: pre-line"
      >
        {{ fields.tag.errorMessage }}
      </div>
      <div class="form-text">A unique tag used for search purposes.</div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="email"
        type="email"
        class="form-control"
        :class="[
          fields.email.isValid === null ? '' : fields.email.isValid ? 'is-valid' : 'is-invalid'
        ]"
        aria-describedby="email-validation-feedback"
        placeholder=""
        required
      />
      <label for="email">Email address</label>
      <div
        v-if="fields.email.isValid === false"
        id="email-validation-feedback"
        class="invalid-feedback"
        style="white-space: pre-line"
      >
        {{ fields.email.errorMessage }}
      </div>
      <div id="emailHelp" class="form-text">Your email will stay private, it wont be shared.</div>
    </div>

    <div class="form-floating mb-3">
      <input
        name="password"
        type="password"
        class="form-control"
        :class="[
          fields.password.isValid === null
            ? ''
            : fields.password.isValid
              ? 'is-valid'
              : 'is-invalid'
        ]"
        aria-describedby="password-validation-feedback"
        placeholder=""
        required
      />
      <label for="password">Password</label>
      <div
        v-if="fields.password.isValid === false"
        id="password-validation-feedback"
        class="invalid-feedback"
        style="white-space: pre-line"
      >
        {{ fields.password.errorMessage }}
      </div>
    </div>

    <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">
      Create Account
    </button>
    <div v-if="isLoading" class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </form>
</template>
