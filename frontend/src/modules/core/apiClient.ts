import axios from 'axios'
import { useAuth } from '@/modules/user/stores/useAuth'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL + '/v1'
const authType: string = 'Bearer'

const getHeader = () => {
  const auth = useAuth()
  return auth.user.isSignedIn ? { Authorization: `${authType} ${auth.getToken()}` } : {}
}

export default {
  signIn(params: object): Promise<axios.AxiosResponse> {
    return axios.post(`${apiBaseUrl}/auth/sign-in`, params)
  },

  signUp(params: object) {
    return axios.post(`${apiBaseUrl}/auth/sign-up`, params)
  },

  syncFixtures(competitionCode: string, seasonYear: number, from: string, to: string) {
    return axios.post(
      `${apiBaseUrl}/fixtures/sync`,
      { competitionCode, seasonYear, from, to },
      { headers: getHeader() }
    )
  },

  showFixtures(
    start: null | string = null,
    end: null | string = null,
    competition: null | string = null,
    userIds: null | number[] = null,
    season: null | number
  ) {
    return axios.get(`${apiBaseUrl}/fixtures/predictions`, {
      headers: getHeader(),
      params: {
        start: start,
        end: end,
        competitionCode: competition,
        userIds: userIds,
        season: season,
        defaultToCurrentSeason: true
      }
    })
  },

  leaderboard(
    start: null | string = null,
    end: null | string = null,
    competition: null | string = null,
    season: null | number
  ) {
    return axios.get(`${apiBaseUrl}/fixtures/leaderboard`, {
      headers: getHeader(),
      params: {
        start: start,
        end: end,
        competitionCode: competition,
        season: season,
        defaultToCurrentSeason: true
      }
    })
  },

  listUsers(userTag: null | string = null) {
    return axios.get(`${apiBaseUrl}/users`, {
      headers: getHeader(),
      params: { tag: userTag }
    })
  },

  getUser(id: number) {
    return axios.get(`${apiBaseUrl}/users/${id}`, {
      headers: getHeader()
    })
  },

  patchUser(id: number, params: object) {
    return axios.patch(`${apiBaseUrl}/users/${id}`, params, {
      headers: getHeader()
    })
  },

  makePredictions(params: object) {
    return axios.post(`${apiBaseUrl}/fixtures/make-predictions`, params, {
      headers: getHeader()
    })
  }
}
