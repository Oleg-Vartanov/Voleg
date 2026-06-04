export interface FormSelectOption {
  value: string | number
  label: string
}

export function validationClass(isValid: boolean | null | undefined): string {
  if (isValid == null) return ''
  return !isValid ? 'is-invalid' : 'is-valid'
}
