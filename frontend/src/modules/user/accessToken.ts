import { jwtDecode, type JwtPayload } from 'jwt-decode'

let accessToken: string | null = null

type RefreshHandler = () => Promise<string | null>

let refreshHandler: RefreshHandler | null = null

export function getAccessToken(): string | null {
  return accessToken
}

export function setAccessToken(token: string | null): void {
  accessToken = token
}

export function setRefreshHandler(handler: RefreshHandler): void {
  refreshHandler = handler
}

export function refreshAccessToken(): Promise<string | null> {
  if (refreshHandler === null) {
    return Promise.resolve(null)
  }

  return refreshHandler()
}

export function isTokenExpired(jwtToken: string): boolean {
  try {
    const decodedToken: JwtPayload = jwtDecode(jwtToken)
    if (decodedToken.exp == null) {
      return true
    }
    return decodedToken.exp < Date.now() / 1000
  } catch {
    return true
  }
}

export function isTokenValid(): boolean {
  const token = getAccessToken()
  return token !== null && !isTokenExpired(token)
}
