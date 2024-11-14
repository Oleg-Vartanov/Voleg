import { createRouter, createWebHistory } from 'vue-router';
import { useGuard } from './guards';

const guards = useGuard();

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
          meta: { title: 'Home' },
          component: () => import('../views/HomeView.vue'),
        },
        {
          path: '/about',
          name: 'about',
          meta: { title: 'About' },
          component: () => import('../views/AboutView.vue'),
        },
        {
          path: '/pricing',
          name: 'pricing',
          meta: { title: 'Pricing' },
          component: () => import('../views/PricingView.vue'),
        },
        {
          path: '/profile',
          name: 'profile',
          meta: { title: 'Profile' },
          beforeEnter: [guards.isAuthenticated],
          component: () => import('../views/ProfileView.vue'),
        },
        {
          path: '/auth-forms',
          name: 'authForms',
          component: () => import('../components/AuthForms.vue'),
          children: [
            {
              path: '/sign-in',
              name: 'signIn',
              meta: { title: 'Sign In' },
              component: () => import('../views/SignInView.vue'),
            },
            {
              path: '/sign-up',
              name: 'signUp',
              meta: { title: 'Sign Up' },
              component: () => import('../views/SignUpView.vue'),
            },
          ]
        },
        {
          path: "/:notFound",
          meta: { title: 'Not Found' },
          component: () => import('../views/NotFoundView.vue'),
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
