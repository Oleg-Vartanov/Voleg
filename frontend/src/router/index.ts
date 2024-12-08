import { createRouter, createWebHistory } from 'vue-router';
import { useGuard } from './guards';

const guards = useGuard();

const index = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'defaultLayout',
      component: () => import('@/views/layouts/DefaultLayout.vue'),
      children: [
        {
          path: '/',
          name: 'home',
          meta: { title: 'Home' },
          redirect: { name: 'about' },
        },
        {
          path: '/games',
          name: 'games',
          meta: { title: 'Games' },
          component: () => import('@/views/Games.vue'),
        },
        {
          path: '/games/football-predictions',
          name: 'football-predictions',
          meta: { title: 'Predictions' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/views/games/FootballPredictions.vue'),
        },
        {
          path: '/about',
          name: 'about',
          meta: { title: 'About' },
          component: () => import('@/views/About.vue'),
        },
        {
          path: '/pricing',
          name: 'pricing',
          meta: { title: 'Pricing' },
          component: () => import('@/views/Pricing.vue'),
        },
        {
          path: '/profile',
          name: 'profile',
          meta: { title: 'Profile' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/views/Profile.vue'),
        },
        {
          path: '/admin',
          name: 'admin',
          meta: { title: 'Admin' },
          beforeEnter: [guards.isAuthenticated, guards.hasRole(['ROLE_ADMIN'])],
          component: () => import('@/views/Admin.vue'),
        },
        {
          path: '/auth-forms',
          name: 'authForms',
          component: () => import('@/components/AuthForms.vue'),
          children: [
            {
              path: '/sign-in',
              name: 'signIn',
              meta: { title: 'Sign In' },
              component: () => import('@/views/SignIn.vue'),
            },
            {
              path: '/sign-up',
              name: 'signUp',
              meta: { title: 'Sign Up' },
              component: () => import('@/views/SignUp.vue'),
            },
          ]
        },
        {
          path: "/:notFound",
          meta: { title: 'Not Found' },
          component: () => import('@/views/NotFound.vue'),
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
