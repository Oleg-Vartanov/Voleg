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
    season: null | number,
    offset: number = 0,
    limit: number = 20
  ) {
    return axios.get(`${apiBaseUrl}/fixtures/predictions`, {
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

  leaderboard(
    start: null | string = null,
    end: null | string = null,
    competition: null | string = null,
    season: null | number,
    offset: number = 0,
    limit: number = 20
  ) {
    return axios.get(`${apiBaseUrl}/fixtures/leaderboard`, {
      headers: getHeader(),
      params: {
        start: start,
        end: end,
        competitionCode: competition,
        season: season,
        offset: offset,
        limit: limit,
        defaultToCurrentSeason: true
      }
    })
  },

  listUsers(username: null | string = null, offset = 0, limit = 100) {
    return axios.get(`${apiBaseUrl}/users`, {
      headers: getHeader(),
      params: { username, offset, limit },
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

  listContacts(userId: number, offset = 0, limit = 100) {
    return axios.get(`${apiBaseUrl}/users/${userId}/contacts`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  addContact(userId: number, contactId: number) {
    return axios.post(`${apiBaseUrl}/users/${userId}/contacts/${contactId}`, null, {
      headers: getHeader()
    })
  },

  deleteContact(userId: number, contactId: number) {
    return axios.delete(`${apiBaseUrl}/users/${userId}/contacts/${contactId}`, {
      headers: getHeader()
    })
  },

  passwordChange(params: object) {
    return axios.post(`${apiBaseUrl}/auth/password-change`, params, {
      headers: getHeader()
    })
  },

  passwordForgot(email: string) {
    return axios.post(
      `${apiBaseUrl}/auth/password-forgot`,
      { email },
      {
        headers: getHeader()
      }
    )
  },

  passwordReset(selector: string, secret: string, password: string) {
    const params = { selector, secret, password }
    return axios.post(`${apiBaseUrl}/auth/password-reset`, params, {
      headers: getHeader()
    })
  },

  makePredictions(params: object) {
    return axios.post(`${apiBaseUrl}/fixtures/make-predictions`, params, {
      headers: getHeader()
    })
  },

  listSplitExpenses(offset = 0, limit = 100) {
    return axios.get(`${apiBaseUrl}/split-expense/expenses`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  getSplitExpenseBalances() {
    return axios.get(`${apiBaseUrl}/split-expense/balances`, {
      headers: getHeader()
    })
  },

  listSplitExpenseCategories(offset = 0, limit = 100) {
    return axios.get(`${apiBaseUrl}/split-expense/categories`, {
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
    direction: 'incoming' | 'outgoing' | null = null,
  ) {
    return axios.get(`${apiBaseUrl}/split-expense/connections`, {
      headers: getHeader(),
      params: { offset, limit, status, usersOnly, username, direction },
    })
  },

  requestSplitExpenseConnection(connectionUserId: number) {
    return axios.post(
      `${apiBaseUrl}/split-expense/connections/request`,
      { connectionUserId },
      { headers: getHeader() }
    )
  },

  respondSplitExpenseConnection(id: number, status: 'accepted' | 'rejected') {
    return axios.post(
      `${apiBaseUrl}/split-expense/connections/${id}/response`,
      { status },
      { headers: getHeader() }
    )
  },

  deleteSplitExpenseConnection(id: number) {
    return axios.delete(`${apiBaseUrl}/split-expense/connections/${id}`, {
      headers: getHeader()
    })
  },

  listCurrencies(offset = 0, limit = 200) {
    return axios.get(`${apiBaseUrl}/currencies`, {
      headers: getHeader(),
      params: { offset, limit }
    })
  },

  createSplitExpense(payload: object) {
    return axios.post(`${apiBaseUrl}/split-expense/expenses/0`, payload, {
      headers: getHeader()
    })
  },

  updateSplitExpense(id: number, payload: object) {
    return axios.put(`${apiBaseUrl}/split-expense/expenses/${id}`, payload, {
      headers: getHeader()
    })
  },

  deleteSplitExpense(id: number) {
    return axios.delete(`${apiBaseUrl}/split-expense/expenses/${id}`, {
      headers: getHeader()
    })
  }
}
