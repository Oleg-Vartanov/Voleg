export interface ApiUser {
  id: number
  username: string
  email: string
  createdAt: string
}

export interface ApiCurrency {
  id: number
  code: string
  symbol: string
  decimalPlaces: number
}

export interface ApiFixtureFilters {
  start: string
  end: string
  competition: string
  season: number
}
