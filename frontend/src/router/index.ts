import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'LandingPage',
      component: () => import('@/views/landing/LandingPage.vue')
    },
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
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'DashboardHome',
          component: () => import('@/views/DashboardView.vue')
        }
      ]
    },
    {
      path: '/produtores',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Produtores',
          component: () => import('@/views/produtores/ProdutoresView.vue')
        }
      ]
    },
    {
      path: '/propriedades',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Propriedades',
          component: () => import('@/views/propriedades/PropriedadesView.vue')
        }
      ]
    },
    {
      path: '/unidades-producao',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'UnidadesProducao',
          component: () => import('@/views/unidades-producao/UnidadesProducaoView.vue')
        }
      ]
    },
    {
      path: '/rebanhos',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Rebanhos',
          component: () => import('@/views/rebanhos/RebanhosView.vue')
        }
      ]
    },
    {
      path: '/relatorios',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Relatorios',
          component: () => import('@/views/relatorios/RelatoriosView.vue')
        }
      ]
    },
    {
      path: '/animais',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Animais',
          component: () => import('@/views/animais/AnimaisView.vue')
        }
      ]
    },
    {
      path: '/financeiro',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Financeiro',
          component: () => import('@/views/financeiro/FinanceiroView.vue')
        }
      ]
    },
    {
      path: '/manejo',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Manejo',
          component: () => import('@/views/manejo/ManejoView.vue')
        }
      ]
    },
    {
      path: '/lotes',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Lotes',
          component: () => import('@/views/lotes/LotesView.vue')
        }
      ]
    },
    {
      path: '/estoque',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Estoque',
          component: () => import('@/views/estoque/EstoqueView.vue')
        }
      ]
    },
    {
      path: '/pastos',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Pastos',
          component: () => import('@/views/pastos/PastosView.vue')
        }
      ]
    },
    {
      path: '/calculadora',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Calculadora',
          component: () => import('@/views/calculadora/CalculadoraView.vue')
        }
      ]
    },
    {
      path: '/relatorios',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Relatorios',
          component: () => import('@/views/relatorios/RelatoriosView.vue')
        }
      ]
    },
    {
      path: '/configuracoes',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'Configuracoes',
          component: () => import('@/views/configuracoes/ConfiguracoesView.vue')
        }
      ]
    }
  ]
})

// Navigation Guards
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()
  
  // Redirecionar usuários autenticados de "/" para "/dashboard"
  if (to.path === '/' && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router