<template>
  <div class="layout-wrapper" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <!-- Sidebar -->
    <div class="layout-sidebar bg-white border-r border-gray-200 shadow-lg"
      :class="{ 'active': sidebarVisible, 'collapsed': sidebarCollapsed }">
      <div class="layout-sidebar-content">
        <!-- Logo -->
        <div class="sidebar-logo-container p-4 border-b border-gray-200 flex items-center" style="min-height: 64px;">
          <div class="logo-normal">
            <SimpleLogo size="small" />
          </div>
          <div class="logo-collapsed">
            <div class="logo-icon-only">
              <i class="fas fa-seedling"></i>
              <i class="fas fa-cow"></i>
            </div>
          </div>
        </div>

        <!-- Navigation Menu -->
        <div class="p-4">
          <nav class="space-y-2">
            <router-link to="/dashboard" class="nav-item" exact-active-class="nav-item-active">
              <i class="far fa-chart-bar mr-3"></i>
              <span>Dashboard</span>
            </router-link>

            <router-link to="/produtores" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-user mr-3"></i>
              <span>Produtores Rurais</span>
            </router-link>

            <router-link to="/propriedades" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-home mr-3"></i>
              <span>Propriedades</span>
            </router-link>

            <router-link to="/unidades-producao" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-seedling mr-3"></i>
              <span>Unidades de Produção</span>
            </router-link>

            <router-link to="/rebanhos" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-cow mr-3"></i>
              <span>Rebanhos</span>
            </router-link>

            <router-link to="/animais" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-paw mr-3"></i>
              <span>Animais</span>
            </router-link>

            <router-link to="/lotes" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-box mr-3"></i>
              <span>Lotes</span>
            </router-link>

            <router-link to="/pastos" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-map mr-3"></i>
              <span>Pastos</span>
            </router-link>

            <router-link to="/manejo" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-clipboard-list mr-3"></i>
              <span>Manejo</span>
            </router-link>

            <router-link to="/financeiro" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-dollar-sign mr-3"></i>
              <span>Financeiro</span>
            </router-link>

            <router-link to="/estoque" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-warehouse mr-3"></i>
              <span>Estoque</span>
            </router-link>

            <router-link to="/calculadora" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-calculator mr-3"></i>
              <span>Calculadora</span>
            </router-link>

            <router-link to="/relatorios" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-file-alt mr-3"></i>
              <span>Relatórios</span>
            </router-link>

            <div class="border-t border-gray-200 my-2"></div>

            <router-link to="/configuracoes" class="nav-item" exact-active-class="nav-item-active">
              <i class="fas fa-cog mr-3"></i>
              <span>Configurações</span>
            </router-link>
          </nav>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="layout-main-container">
      <!-- Top Bar -->
      <header class="layout-topbar bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between h-12">
          <div class="flex items-center h-full">
            <Button icon="pi pi-bars" class="p-button-text p-button-rounded" @click="toggleSidebar" />
            <h1 class="text-lg font-semibold text-gray-800 ml-4 flex items-center h-full">{{ pageTitle }}</h1>
          </div>

          <div class="flex items-center space-x-4 h-full">
            <span class="text-sm text-gray-600 flex items-center h-full">Olá, {{ authStore.user?.name }}</span>
            <Button icon="pi pi-sign-out" class="p-button-outlined p-button-sm flex items-center h-full" @click="logout"
              label="Sair" />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="layout-content">
        <router-view :key="route.fullPath" />
      </main>
    </div>

    <!-- Mobile Overlay -->
    <div v-if="sidebarVisible" class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
      @click="sidebarVisible = false"></div>
  </div>

  <Toast />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import SimpleLogo from '@/components/common/SimpleLogo.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()

const sidebarVisible = ref(false)
const sidebarCollapsed = ref(false)

const pageTitle = computed(() => {
  const routeTitle: Record<string, string> = {
    'DashboardHome': 'Dashboard',
    'Produtores': 'Produtores Rurais',
    'Propriedades': 'Propriedades Rurais',
    'UnidadesProducao': 'Unidades de Produção',
    'Rebanhos': 'Rebanhos',
    'Animais': 'Animais',
    'Lotes': 'Lotes',
    'Pastos': 'Pastos e Áreas de Pastagem',
    'Manejo': 'Manejo e Atividades',
    'Financeiro': 'Gestão Financeira',
    'Estoque': 'Controle de Estoque',
    'Calculadora': 'Calculadora Pecuária',
    'Relatorios': 'Relatórios e Exportações'
  }
  return routeTitle[route.name as string] || 'Sistema Agropecuário'
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const logout = async () => {
  try {
    await authStore.logout()
    toast.add({
      severity: 'success',
      summary: 'Logout',
      detail: 'Logout realizado com sucesso',
      life: 3000
    })
    router.push('/login')
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro ao fazer logout',
      life: 3000
    })
  }
}

onMounted(() => {
  authStore.checkAuth()
})
</script>

<style scoped>
.nav-item {
  @apply flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 hover:text-green-600 transition-colors duration-200;
}

.nav-item-active {
  @apply bg-green-50 text-green-600 border-r-2 border-green-600;
}

.layout-wrapper {
  @apply min-h-screen bg-gray-50;
}

.layout-sidebar {
  width: 256px;
  transition: width 0.3s ease;
  position: fixed;
  height: 100vh;
  z-index: 40;
  left: 0;
  top: 0;
}

.layout-sidebar.collapsed {
  width: 64px;
}

.layout-sidebar.collapsed .nav-item {
  justify-content: center;
}

.layout-sidebar.collapsed .nav-item span {
  display: none;
}

.layout-sidebar.collapsed .nav-item i {
  margin-right: 0;
}

.layout-sidebar.collapsed .p-4 {
  padding: 0.5rem;
}

.layout-sidebar.collapsed .flex.items-center {
  justify-content: center;
}

.layout-sidebar.collapsed .text-xl {
  display: none;
}

/* Logo na sidebar */
.sidebar-logo-container {
  justify-content: flex-start;
}

.sidebar-logo-container .logo-normal {
  display: flex;
}

.sidebar-logo-container .logo-collapsed {
  display: none;
}

.logo-icon-only {
  position: relative;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.logo-icon-only i.fa-seedling {
  position: relative;
  z-index: 2;
  font-size: 1.125rem;
}

.logo-icon-only i.fa-cow {
  position: absolute;
  bottom: 2px;
  right: 2px;
  background: rgba(0, 0, 0, 0.25);
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.5);
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.5rem;
}

/* Quando colapsada */
.layout-sidebar.collapsed .sidebar-logo-container {
  justify-content: center !important;
}

.layout-sidebar.collapsed .sidebar-logo-container .logo-normal {
  display: none !important;
}

.layout-sidebar.collapsed .sidebar-logo-container .logo-collapsed {
  display: flex !important;
  justify-content: center;
}

.layout-sidebar.collapsed .fa-seedling {
  margin-right: 0;
}

.layout-topbar {
  @apply flex items-center justify-between px-6 py-4;
  min-height: 4rem;
  height: 4rem;
}

.layout-sidebar .p-4:first-child {
  height: 4rem;
  min-height: 4rem;
}

.layout-main-container {
  margin-left: 256px;
  transition: margin-left 0.3s ease;
}

.layout-wrapper.sidebar-collapsed .layout-main-container {
  margin-left: 64px;
}

/* Responsividade para tablets */
@media (max-width: 1024px) {
  .layout-sidebar {
    width: 240px;
  }

  .layout-main-container {
    margin-left: 240px;
  }
}

/* Responsividade para mobile */
@media (max-width: 768px) {
  .layout-sidebar {
    @apply transform -translate-x-full transition-transform duration-300;
    width: 280px;
  }

  .layout-sidebar.active {
    @apply translate-x-0;
  }

  .layout-main-container {
    @apply ml-0;
  }

  .layout-topbar {
    @apply px-4 py-3;
    height: auto;
    min-height: 3.5rem;
  }

  .layout-topbar h1 {
    @apply text-base;
  }

  .layout-topbar .p-button {
    @apply text-xs;
  }

  .layout-topbar span {
    @apply text-xs;
  }
}

/* Responsividade para mobile pequeno */
@media (max-width: 480px) {
  .layout-topbar {
    @apply px-3 py-2;
    min-height: 3rem;
  }

  .layout-topbar h1 {
    @apply text-sm ml-2;
  }

  .layout-topbar .p-button {
    @apply p-1;
  }

  .layout-topbar span {
    @apply hidden;
  }
}
</style>