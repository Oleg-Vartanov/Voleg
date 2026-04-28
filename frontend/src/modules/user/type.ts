export interface User {
  id: number
  displayName: string
  tag: string
  emailChange?: string | null
  isSignedIn: boolean
  roles: string[]
}
