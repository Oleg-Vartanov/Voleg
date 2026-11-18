import axios, { type AxiosError, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios'
import { getAccessToken, refreshAccessToken } from '@/modules/user/accessToken'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL + '/v1'

const api = axios.create({ withCredentials: true })

type RetryConfig = InternalAxiosRequestConfig & { _retry?: boolean }

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as RetryConfig | undefined
    const url = config?.url ?? ''
    const skipsRefresh =
      url.includes('/auth/refresh') ||
      url.includes('/auth/sign-in') ||
      url.includes('/auth/sign-out')

    if (!config || error.response?.status !== 401 || config._retry || skipsRefresh) {
      return Promise.reject(error)
    }

    config._retry = true
    const token = await refreshAccessToken()
    if (token === null) {
      return Promise.reject(error)
    }

    config.headers.Authorization = `Bearer ${token}`
    return api(config)
  }
)

const getHeader = () => {
  const token = getAccessToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export default {
  signIn(params: object): Promise<AxiosResponse> {
    return api.post(`${apiBaseUrl}/auth/sign-in`, params)
  },

  refresh() {
    return api.post(`${apiBaseUrl}/auth/refresh`)
  },

  signOut() {
    return api.post(`${apiBaseUrl}/auth/sign-out`)
  },

  signUp(params: object) {
    return api.post(`${apiBaseUrl}/auth/sign-up`, params)
  },

  syncFixtures(competitionCode: string, seasonYear: number, from: string, to: string) {
    return api.post(
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
    season: null | number,
    offset: number = 0,
    limit: number = 20,
    upcomingOnly: boolean = false
  ) {
    return api.get(`${apiBaseUrl}/fixtures/predictions`, {
      headers: getHeader(),
      params: {
        start: start,
        end: end,
        competitionCode: competition,
        userIds: userIds,
        season: season,
        offset: offset,
        limit: limit,
        defaultToCurrentSeason: true,
        upcomingOnly: upcomingOnly
      }
    })
  },

  leaderboard(
    start: null | string = null,
    end: null | string = null,
    competition: null | string = null,
    userIds: null | number[] = null,
    season: null | number,
    offset: number = 0,
    limit: number = 20
  ) {
    return api.get(`${apiBaseUrl}/fixtures/leaderboard`, {
      headers: getHeader(),
      params: {
        start: start,
        end: end,
        competitionCode: competition,
        userIds: userIds,
        season: season,
        offset: offset,
        limit: limit,
        defaultToCurrentSeason: true
      }
    })
  },

  listUsers(username: null | string = null, offset = 0, limit = 100) {
    return api.get(`${apiBaseUrl}/users`, {
      headers: getHeader(),
      params: { username, offset, limit }
    })
  },

  getUser(id: number) {
    return api.get(`${apiBaseUrl}/users/${id}`, {
      headers: getHeader()
    })
  },

  patchUser(id: number, params: object) {
    return api.patch(`${apiBaseUrl}/users/${id}`, params, {
      headers: getHeader()
    })
  },

  listContacts(userId: number, offset = 0, limit = 100) {
    return api.get(`${apiBaseUrl}/users/${userId}/contacts`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  addContact(userId: number, contactId: number) {
    return api.post(`${apiBaseUrl}/users/${userId}/contacts/${contactId}`, null, {
      headers: getHeader()
    })
  },

  deleteContact(userId: number, contactId: number) {
    return api.delete(`${apiBaseUrl}/users/${userId}/contacts/${contactId}`, {
      headers: getHeader()
    })
  },

  passwordChange(params: object) {
    return api.post(`${apiBaseUrl}/auth/password-change`, params, {
      headers: getHeader()
    })
  },

  passwordForgot(email: string) {
    return api.post(
      `${apiBaseUrl}/auth/password-forgot`,
      { email },
      {
        headers: getHeader()
      }
    )
  },

  passwordReset(token: string, password: string) {
    const params = { token, password }
    return api.post(`${apiBaseUrl}/auth/password-reset`, params, {
      headers: getHeader()
    })
  },

  makePredictions(params: object) {
    return api.post(`${apiBaseUrl}/fixtures/make-predictions`, params, {
      headers: getHeader()
    })
  },

  listSplitExpenses(offset = 0, limit = 100) {
    return api.get(`${apiBaseUrl}/split-expense/expenses`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  getSplitExpenseBalances() {
    return api.get(`${apiBaseUrl}/split-expense/balances`, {
      headers: getHeader()
    })
  },

  getSplitExpenseInsights(
    offset = 0,
    limit = 6,
    categoryId: number | null = null,
    userIds: number[] = []
  ) {
    return api.get(`${apiBaseUrl}/split-expense/insights`, {
      headers: getHeader(),
      params: {
        offset,
        limit,
        categoryId,
        userIds: userIds.length > 0 ? userIds : undefined
      }
    })
  },

  listSplitExpenseCategories(offset = 0, limit = 100) {
    return api.get(`${apiBaseUrl}/split-expense/categories`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  listSplitExpenseConnections(
    offset = 0,
    limit = 100,
    status: 'accepted' | 'pending' | 'rejected' | null = null,
    usersOnly: boolean = false,
    username: string | null = null,
    direction: 'incoming' | 'outgoing' | null = null
  ) {
    return api.get(`${apiBaseUrl}/split-expense/connections`, {
      headers: getHeader(),
      params: { offset, limit, status, usersOnly, username, direction }
    })
  },

  requestSplitExpenseConnection(connectionUserId: number) {
    return api.post(
      `${apiBaseUrl}/split-expense/connections/request`,
      { connectionUserId },
      { headers: getHeader() }
    )
  },

  respondSplitExpenseConnection(id: number, status: 'accepted' | 'rejected') {
    return api.post(
      `${apiBaseUrl}/split-expense/connections/${id}/response`,
      { status },
      { headers: getHeader() }
    )
  },

  deleteSplitExpenseConnection(id: number) {
    return api.delete(`${apiBaseUrl}/split-expense/connections/${id}`, {
      headers: getHeader()
    })
  },

  listCurrencies(offset = 0, limit = 200) {
    return api.get(`${apiBaseUrl}/currencies`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  createSplitExpense(payload: object) {
    return api.post(`${apiBaseUrl}/split-expense/expenses/0`, payload, {
      headers: getHeader()
    })
  },

  updateSplitExpense(id: number, payload: object) {
    return api.put(`${apiBaseUrl}/split-expense/expenses/${id}`, payload, {
      headers: getHeader()
    })
  },

  deleteSplitExpense(id: number) {
    return api.delete(`${apiBaseUrl}/split-expense/expenses/${id}`, {
      headers: getHeader()
    })
  },

  listSplitExpenseAdjustments(offset = 0, limit = 100) {
    return api.get(`${apiBaseUrl}/split-expense/adjustments`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  createSplitExpenseAdjustment(payload: object) {
    return api.post(`${apiBaseUrl}/split-expense/adjustments`, payload, {
      headers: getHeader()
    })
  },

  updateSplitExpenseAdjustment(id: number, payload: object) {
    return api.put(`${apiBaseUrl}/split-expense/adjustments/${id}`, payload, {
      headers: getHeader()
    })
  },

  deleteSplitExpenseAdjustment(id: number) {
    return api.delete(`${apiBaseUrl}/split-expense/adjustments/${id}`, {
      headers: getHeader()
    })
  },

  listSplitExpenseFeed(offset = 0, limit = 100, types: string[] = []) {
    return api.get(`${apiBaseUrl}/split-expense/feed`, {
      headers: getHeader(),
      params: { offset, limit, types }
    })
  }
}
