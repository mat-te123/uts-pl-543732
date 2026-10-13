import { createRouter, createWebHistory } from 'vue-router'
import VacantView from '@/view/VacantView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/vacants',
      name: 'Vacant',
      component: VacantView,
    },
  ],
})

export default router
