import { ref } from 'vue'

export function useApiValidation<T>() {
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

  const hasError = (path: keyof T): boolean => {
    return Object.prototype.hasOwnProperty.call(errors.value, path)
  }

  const getError = (path: keyof T): string | null => {
    return errors.value[path] ?? null
  }

  const isValid = (path: keyof T): boolean | null => {
    return isError.value ? !hasError(path) : null
  }

  return {
    isError,
    errors,
    reset,
    applyErrors,
    hasError,
    getError,
    isValid
  }
}
