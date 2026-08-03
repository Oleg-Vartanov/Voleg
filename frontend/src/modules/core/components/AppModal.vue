<script setup lang="ts">
import { onUnmounted, useId, watch } from 'vue'

const open = defineModel<boolean>('open', { required: true })

defineProps<{
  title: string
  error?: string | null
}>()

const titleId = useId()

function close() {
  open.value = false
}

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') close()
}

function setBodyScrollLocked(locked: boolean) {
  document.body.classList.toggle('modal-open', locked)
}

watch(
  open,
  (isOpen) => {
    setBodyScrollLocked(isOpen)
    if (isOpen) {
      document.addEventListener('keydown', onKeydown)
      return
    }

    document.removeEventListener('keydown', onKeydown)
  },
  { immediate: true }
)

onUnmounted(() => {
  setBodyScrollLocked(false)
  document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
  <Teleport to="body">
    <template v-if="open">
      <div class="modal-backdrop fade show" @click="close"></div>
      <div
        class="modal fade show d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        @mousedown.self="close"
      >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header flex-column align-items-stretch gap-2">
              <div class="d-flex align-items-center justify-content-between gap-2">
                <h1 :id="titleId" class="modal-title fs-5 mb-0">
                  {{ title }}
                </h1>
                <button type="button" class="btn-close" aria-label="Close" @click="close"></button>
              </div>
              <div v-if="error" class="alert alert-danger mb-0 py-2" role="alert">
                {{ error }}
              </div>
            </div>

            <slot />

            <div v-if="$slots.footer" class="modal-footer">
              <slot name="footer" :close="close" />
            </div>
          </div>
        </div>
      </div>
    </template>
  </Teleport>
</template>
