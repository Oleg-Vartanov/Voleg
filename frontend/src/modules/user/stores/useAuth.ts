import { defineStore } from 'pinia'
import type { Router } from 'vue-router'
import type { User } from '@/modules/user/type'
import arrayUtils from '@/modules/core/utils/arrayUtils'
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useTopAlerts } from '@/modules/core/stores/useTopAlerts'
import { jwtDecode, type JwtPayload } from 'jwt-decode'
import client from '@/modules/core/apiClient'
import {
  getAccessToken,
  isTokenExpired,
  isTokenValid,
  setAccessToken,
  setRefreshHandler
} from '@/modules/user/accessToken'

interface apiJwtPayload extends JwtPayload {
  id: number
  username: string
  roles: string[]
}

const defaultUser: User = {
  isSignedIn: false,
  id: null,
  username: null,
  roles: []
}

export const useAuth = defineStore('auth', () => {
  const topAlerts = useTopAlerts()
  const router: Router = useRouter()

  const user = reactive({ ...defaultUser })
  let refreshing: Promise<boolean> | null = null
  let sessionPromise: Promise<void> | null = null

  function reset(): void {
    setAccessToken(null)
    Object.assign(user, defaultUser)
  }

  function applyToken(token: string | null): void {
    if (token === null || isTokenExpired(token)) {
      reset()
      return
    }

    try {
      const decodedToken: apiJwtPayload = jwtDecode(token)
      setAccessToken(token)
      user.isSignedIn = true
      user.id = decodedToken.id ?? null
      user.username = decodedToken.username ?? null
      user.roles = decodedToken.roles ?? []
    } catch {
      reset()
    }
  }

  function refreshSession(): Promise<boolean> {
    if (refreshing === null) {
      refreshing = client
        .refresh()
        .then((response) => {
          applyToken(response.data.token)
          return user.isSignedIn
        })
        .catch(() => {
          reset()
          return false
        })
        .finally(() => {
          refreshing = null
        })
    }

    return refreshing
  }

  function ensureSession(): Promise<void> {
    if (sessionPromise === null) {
      sessionPromise = (async () => {
        window.localStorage.removeItem('voleg-jwt')
        await refreshSession()
      })()
    }

    return sessionPromise
  }

  function signIn(token: string): void {
    applyToken(token)
    topAlerts.add('Successfully signed in. Welcome!', 'success')
    router.push({ name: 'home' })
  }

  async function signOut(): Promise<void> {
    try {
      await client.signOut()
    } catch {
      // The local session is dropped either way.
    }
    reset()
    topAlerts.add('Signed out.', 'success', 5)
    router.push({ name: 'signIn' })
  }

  function hasRole(roles: string[]): boolean {
    return arrayUtils.intersects(user.roles, roles)
  }

  setRefreshHandler(async () => {
    const restored = await refreshSession()
    return restored ? getAccessToken() : null
  })

  return {
    user,
    hasRole,
    signIn,
    signOut,
    applyToken,
    reset,
    isTokenValid,
    ensureSession
  }
})
