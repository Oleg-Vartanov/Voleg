export interface ApiUser {
  id: number
  username: string
  email: string
  createdAt: string
}

export interface ApiCurrency {
  id: number
  name: string
  code: string
  decimalPlaces: number
}

export interface ApiFixtureFilters {
  start: string
  end: string
  competition: string
  season: number
  limit: number
  offset: number
  total: number
}
