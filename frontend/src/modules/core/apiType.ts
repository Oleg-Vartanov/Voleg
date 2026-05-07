export interface ApiUser {
  id: number
  displayName: string
  tag: string
  email: string
  createdAt: string
}

export interface ApiFixtureFilters {
  start: string
  end: string
  competition: string
  season: number
}
