import { createRouter, createWebHistory } from 'vue-router';
import { useGuard } from './guards';

const guards = useGuard();

const index = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'defaultLayout',
      component: () => import('@/modules/core/layout/DefaultLayout.vue'),
      children: [
        {
          path: '',
          name: 'home',
          meta: { title: 'Home' },
          redirect: { name: 'about' },
        },
        {
          path: 'games',
          name: 'games',
          meta: { title: 'Games' },
          component: () => import('@/modules/core/pages/Games.vue'),
        },
        {
          path: 'games/football-predictions',
          name: 'football-predictions',
          meta: { title: 'Predictions' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/modules/fixturePredictions/pages/FootballPredictions.vue'),
        },
        {
          path: 'about',
          name: 'about',
          meta: { title: 'About' },
          component: () => import('@/modules/core/pages/About.vue'),
        },
        {
          path: 'pricing',
          name: 'pricing',
          meta: { title: 'Pricing' },
          component: () => import('@/modules/core/pages/Pricing.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          meta: { title: 'Profile' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/modules/user/pages/Profile.vue'),
        },
        {
          path: 'admin',
          name: 'admin',
          meta: { title: 'Admin' },
          beforeEnter: [guards.isAuthenticated, guards.hasRole(['ROLE_ADMIN'])],
          component: () => import('@/modules/admin/pages/Admin.vue'),
        },
        {
          path: 'auth-forms',
          name: 'authForms',
          component: () => import('@/modules/user/components/AuthForms.vue'),
          children: [
            {
              path: 'sign-in',
              name: 'signIn',
              meta: { title: 'Sign In' },
              component: () => import('@/modules/user/pages/SignIn.vue'),
            },
            {
              path: 'sign-up',
              name: 'signUp',
              meta: { title: 'Sign Up' },
              component: () => import('@/modules/user/pages/SignUp.vue'),
            },
          ]
        },
        {
          path: '/:pathMatch(.*)*',
          name: 'notFound',
          meta: { title: 'Not Found' },
          component: () => import('@/modules/core/pages/NotFound.vue'),
        },
      ],
    },
  ]
})

// Add the beforeEach guard for updating document title and description
index.beforeEach((to) => {
  const { title } = to.meta;
  const defaultTitle = 'voleg';
  document.title = title || defaultTitle;
});

export default index
