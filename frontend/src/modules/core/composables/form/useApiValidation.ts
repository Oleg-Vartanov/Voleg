import { ref } from 'vue'

export function useApiValidation<T extends Record<string, any>>() {
  const isError = ref(false)
  const errors = ref<Partial<Record<keyof T, string>>>({})

  const reset = () => {
    isError.value = false
    errors.value = {}
  }

  const applyErrors = (violations: { propertyPath: string; title: string }[] = []) => {
    violations.forEach((violation) => {
      const key = violation.propertyPath as keyof T
      const prev = errors.value[key]
      errors.value[key] = prev ? `${prev}\n${violation.title}` : violation.title
    })
    isError.value = true
  }

  return {
    isError,
    errors,
    reset,
    applyErrors
  }
}
