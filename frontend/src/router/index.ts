import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/reset-password',
      name: 'ResetPassword',
      component: () => import('@/views/auth/ResetPasswordView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      name: 'Dashboard',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'DashboardHome',
          component: () => import('@/views/DashboardView.vue')
        },
        {
          path: '/produtores',
          name: 'Produtores',
          component: () => import('@/views/produtores/ProdutoresView.vue')
        },
        {
          path: '/propriedades',
          name: 'Propriedades',
          component: () => import('@/views/propriedades/PropriedadesView.vue')
        },
        {
          path: '/unidades-producao',
          name: 'UnidadesProducao',
          component: () => import('@/views/unidades-producao/UnidadesProducaoView.vue')
        },
        {
          path: '/rebanhos',
          name: 'Rebanhos',
          component: () => import('@/views/rebanhos/RebanhosView.vue')
        },
        {
          path: '/relatorios',
          name: 'Relatorios',
          component: () => import('@/views/relatorios/RelatoriosView.vue')
        }
      ]
    }
  ]
})

// Navigation Guards
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router