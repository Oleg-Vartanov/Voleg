<script setup lang="ts">
import client from '@/modules/core/apiClient'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { useAuth } from '@/modules/user/stores/useAuth'
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const auth = useAuth()
const topAlerts = useTopAlerts()
const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isEditing = ref(false)

const defaults = {
  displayName: '',
  tag: '',
  email: ''
}
const form = reactive(structuredClone(defaults))
/** Last persisted values from the server (used to build a partial PATCH body). */
const baseline = reactive(structuredClone(defaults))

const fields = reactive({
  displayName: { isValid: null as null | boolean, errorMessage: '' },
  tag: { isValid: null as null | boolean, errorMessage: '' },
  email: { isValid: null as null | boolean, errorMessage: '' }
})

const emailChange = ref<string | null>(null)

const syncBaselineFromForm = () => {
  baseline.displayName = form.displayName
  baseline.tag = form.tag
  baseline.email = form.email
}

const openEditMode = () => {
  resetValidation()
  isEditing.value = true
}

const closeEditMode = () => {
  form.displayName = baseline.displayName
  form.tag = baseline.tag
  form.email = baseline.email
  resetValidation()
  isEditing.value = false
}

const buildUpdatePayload = (): Record<string, string> | null => {
  const payload: Record<string, string> = {}
  if (form.displayName !== baseline.displayName) {
    payload.displayName = form.displayName
  }
  if (form.tag !== baseline.tag) {
    payload.tag = form.tag
  }
  if (form.email !== baseline.email) {
    payload.email = form.email
  }
  return Object.keys(payload).length > 0 ? payload : null
}

const resetValidation = () => {
  Object.keys(fields).forEach((field) => {
    fields[field].isValid = null
    fields[field].errorMessage = ''
  })
}

const applyValidationErrors = (violations: { propertyPath: string; title: string }[] = []) => {
  violations.forEach((violation) => {
    const property = violation.propertyPath
    if (Object.prototype.hasOwnProperty.call(fields, property)) {
      fields[property].isValid = false
      fields[property].errorMessage +=
        (fields[property].errorMessage ? '\n' : '') + violation.title
    }
  })
}

const loadProfile = () => {
  if (auth.user.id === null) {
    return
  }

  isLoading.value = true

  client
    .getUser(auth.user.id)
    .then((response) => {
      form.displayName = response.data.displayName ?? ''
      form.tag = response.data.tag ?? ''
      form.email = response.data.email ?? ''
      emailChange.value = response.data.emailChange ?? null
      syncBaselineFromForm()
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
  resetValidation()

  client
    .patchUser(auth.user.id, payload)
    .then((response) => {
      form.displayName = response.data.displayName ?? form.displayName
      form.tag = response.data.tag ?? form.tag
      form.email = response.data.email ?? form.email
      emailChange.value = response.data.emailChange ?? null
      auth.user.displayName = response.data.displayName ?? form.displayName
      auth.user.tag = response.data.tag ?? form.tag
      syncBaselineFromForm()
      isEditing.value = false
      topAlerts.add('Profile updated successfully.', 'success', 5)
    })
    .catch((axiosError) => {
      const violations = axiosError?.response?.data?.violations
      if (Array.isArray(violations)) {
        applyValidationErrors(violations)
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
  <div class="ov-center">
    <main class="form-signin w-100 m-auto">
      <h1 class="h4 mb-3 fw-normal">Your Profile</h1>

      <div v-if="emailChange !== null" class="alert alert-warning">
        Pending email verification: <strong>{{ emailChange }}</strong
        >.
      </div>

      <div v-if="!isEditing" class="card mb-3">
        <ul class="list-group list-group-flush text-start">
          <li class="list-group-item">
            <div class="small text-muted fw-semibold">Display Name</div>
            <div class="fw-medium">{{ form.displayName || '—' }}</div>
          </li>
          <li class="list-group-item">
            <div class="small text-muted fw-semibold">Tag</div>
            <div class="fw-medium">{{ form.tag || '—' }}</div>
          </li>
          <li class="list-group-item">
            <div class="small text-muted fw-semibold">Email</div>
            <div class="fw-medium">{{ form.email || '—' }}</div>
          </li>
        </ul>
      </div>

      <button
        v-if="!isEditing"
        :disabled="isLoading"
        class="btn btn-primary w-100 py-2 mb-3"
        type="button"
        @click="openEditMode"
      >
        Edit Profile
      </button>

      <form v-else @submit.prevent="saveProfile">
        <!-- Display Name -->
        <div class="form-floating mb-3">
          <input
            id="displayName"
            v-model="form.displayName"
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
            placeholder=""
            required
          />
          <label for="displayName">Display Name</label>
          <div
            v-if="fields.displayName.isValid === false"
            class="invalid-feedback"
            style="white-space: pre-line"
          >
            {{ fields.displayName.errorMessage }}
          </div>
        </div>

        <!-- Tag -->
        <div class="form-floating mb-3">
          <input
            id="tag"
            v-model="form.tag"
            name="tag"
            type="text"
            class="form-control"
            :class="[
              fields.tag.isValid === null ? '' : fields.tag.isValid ? 'is-valid' : 'is-invalid'
            ]"
            placeholder=""
            required
          />
          <label for="tag">Tag</label>
          <div
            v-if="fields.tag.isValid === false"
            class="invalid-feedback"
            style="white-space: pre-line"
          >
            {{ fields.tag.errorMessage }}
          </div>
        </div>

        <!-- Email address -->
        <div class="form-floating mb-3">
          <input
            id="email"
            v-model="form.email"
            name="email"
            type="email"
            class="form-control"
            :class="[
              fields.email.isValid === null ? '' : fields.email.isValid ? 'is-valid' : 'is-invalid'
            ]"
            placeholder=""
            required
          />
          <label for="email">Email address</label>
          <div
            v-if="fields.email.isValid === false"
            class="invalid-feedback"
            style="white-space: pre-line"
          >
            {{ fields.email.errorMessage }}
          </div>
        </div>

        <button :disabled="isLoading" class="btn btn-primary w-100 py-2 mb-3" type="submit">
          Save Changes
        </button>
        <button
          :disabled="isLoading"
          class="btn btn-outline-secondary w-100 py-2 mb-3"
          type="button"
          @click="closeEditMode"
        >
          Cancel
        </button>
      </form>

      <div v-if="isLoading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </main>
  </div>
</template>

<style scoped>
.form-signin {
  max-width: 480px;
  padding: 1rem;
}
</style>
