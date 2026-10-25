import { createRouter, createWebHistory } from 'vue-router'
import VacantView from '@/view/vacant/VacantView.vue'
import DetailVacantView from '@/view/vacant/DetailVacantView.vue'
import LoginRegister from '@/view/auth/LoginRegister.vue'
import { TokenService } from '@/services/auth/TokenService'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/vacants',
      name: 'Vacant',
      component: VacantView,
      meta: { requiresAuth: true },
    },
    {
      path: '/vacant/:id',
      name: 'VacantDetail',
      component: DetailVacantView,
      meta: { requiresAuth: true },
    },
    {
      path: '/auth',
      name: 'Auth',
      component: LoginRegister,
    },
  ],
})

router.beforeEach((to, from) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const isAuthenticated = TokenService.getToken() !== null
  console.log('requiresAuth:', requiresAuth, 'isAuthenticated:', isAuthenticated)
  console.log('token:', TokenService.getToken())

  if (requiresAuth && !isAuthenticated) {
    return { name: 'Auth' }
  }
})

export default router
