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
          path: '/auth-forms',
          name: 'authForms',
          component: () => import('../components/AuthForms.vue'),
          children: [
            {
              path: '/sign-in',
              name: 'signIn',
              component: () => import('../views/SignInView.vue'),
            },
            {
              path: '/sign-up',
              name: 'signUp',
              component: () => import('../views/SignUpView.vue'),
            },
          ]
        },
        {
          path: "/:notFound",
          component: () => import('../views/NotFoundView.vue'),
        },
      ],
    },
  ]
})

export default index
