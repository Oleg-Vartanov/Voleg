import { createRouter, createWebHistory } from 'vue-router'

const index = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'defaultLayout',
      component: () => import('../views/layouts/DefaultLayout.vue'),
      children: [
        {
          path: '/',
          name: 'home',
          component: () => import('../views/HomeView.vue'),
        },
        {
          path: '/about',
          name: 'about',
          component: () => import('../views/AboutView.vue'),
        },
        {
          path: '/pricing',
          name: 'pricing',
          component: () => import('../views/PricingView.vue'),
        },
        {
          path: '/sign-in',
          name: 'signIn',
          component: () => import('../views/SignInView.vue'),
        },
      ],
    },
  ]
})

export default index
