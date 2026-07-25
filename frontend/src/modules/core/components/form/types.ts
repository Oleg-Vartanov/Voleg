import type { ApiUser } from '@/modules/core/apiType'

export interface FormSelectOption {
  value: string | number
  label: string
}

export type UserSearchFn = (query: string) => Promise<ApiUser[]>

export function validationClass(isValid: boolean | null | undefined): string {
  if (isValid == null) return ''
  return !isValid ? 'is-invalid' : 'is-valid'
}
