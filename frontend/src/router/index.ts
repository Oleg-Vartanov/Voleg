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
          path: 'games/football-predictions',
          name: 'footballPredictions',
          meta: { title: 'Football Predictions' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/modules/fixturePredictions/pages/FootballPredictionsPage.vue'),
        },
        {
          path: 'about',
          name: 'about',
          meta: { title: 'About' },
          component: () => import('@/modules/core/pages/AboutPage.vue'),
        },
        {
          path: 'pricing',
          name: 'pricing',
          meta: { title: 'Pricing' },
          component: () => import('@/modules/core/pages/PricingPage.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          meta: { title: 'Profile' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('@/modules/user/pages/ProfilePage.vue'),
        },
        {
          path: 'admin',
          name: 'admin',
          meta: { title: 'Admin' },
          beforeEnter: [guards.isAuthenticated, guards.hasRole(['ROLE_ADMIN'])],
          component: () => import('@/modules/admin/pages/AdminPage.vue'),
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
              component: () => import('@/modules/user/pages/SignInPage.vue'),
            },
            {
              path: 'sign-up',
              name: 'signUp',
              meta: { title: 'Sign Up' },
              component: () => import('@/modules/user/pages/SignUpPage.vue'),
            },
          ],
        },
        {
          path: '/:pathMatch(.*)*',
          name: 'notFound',
          meta: { title: 'Not Found' },
          component: () => import('@/modules/core/pages/NotFoundPage.vue'),
        },
      ],
    },
  ],
});

// Add the beforeEach guard for updating document title and description
index.beforeEach((to) => {
  const { title } = to.meta;
  const defaultTitle = 'voleg';
  document.title = title || defaultTitle;
});

export default index;
