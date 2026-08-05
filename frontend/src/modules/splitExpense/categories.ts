export const categories = {
  bills: 'bi-lightbulb',
  education: 'bi-mortarboard',
  entertainment: 'bi-controller',
  gifts: 'bi-gift',
  groceries: 'bi-cart3', // or 'bi-basket2'
  health: 'bi-heart-pulse',
  hobby: 'bi-brush',
  household: 'bi-house-gear',
  other: 'bi-journal-text', // or 'bi-clipboard2'
  rent: 'bi-house-check',
  restaurants: 'bi-fork-knife',
  shopping: 'bi-handbag',
  sport: 'bi-trophy',
  subscriptions: 'bi-link-45deg',
  transport: 'bi-taxi-front',
  travel: 'bi-luggage',
}

export type CategoryKey = keyof typeof categories;