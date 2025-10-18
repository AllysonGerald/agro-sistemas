<template>
  <div class="dashboard">
    <div class="dashboard-cards-grid">
      <!-- Cards de Estatísticas -->
      <Card class="stats-card stats-card-produtores">
        <template #content>
          <div class="stats-card-content">
            <div class="stats-card-info">
              <h3 class="stats-card-title">Produtores</h3>
              <p class="stats-card-number">{{ stats.produtores }}</p>
            </div>
            <div class="stats-card-icon">
              <i class="fas fa-user"></i>
            </div>
          </div>
        </template>
      </Card>

      <Card class="stats-card stats-card-propriedades">
        <template #content>
          <div class="stats-card-content">
            <div class="stats-card-info">
              <h3 class="stats-card-title">Propriedades</h3>
              <p class="stats-card-number">{{ stats.propriedades }}</p>
            </div>
            <div class="stats-card-icon">
              <i class="fas fa-home"></i>
            </div>
          </div>
        </template>
      </Card>

      <Card class="stats-card stats-card-unidades">
        <template #content>
          <div class="stats-card-content">
            <div class="stats-card-info">
              <h3 class="stats-card-title">Unidades</h3>
              <p class="stats-card-number">{{ stats.unidades }}</p>
            </div>
            <div class="stats-card-icon">
              <i class="fas fa-seedling"></i>
            </div>
          </div>
        </template>
      </Card>

      <Card class="stats-card stats-card-rebanhos">
        <template #content>
          <div class="stats-card-content">
            <div class="stats-card-info">
              <h3 class="stats-card-title">Rebanhos</h3>
              <p class="stats-card-number">{{ stats.rebanhos }}</p>
            </div>
            <div class="stats-card-icon">
              <i class="fas fa-cow"></i>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Gráfico de Propriedades por Município -->
      <Card class="col-span-1">
        <template #title>
          <h2 class="text-lg font-semibold text-gray-800">Propriedades por Município</h2>
        </template>
        <template #content>
          <div class="h-80">
            <Chart type="doughnut" :data="chartData.municipios" :options="municipiosOptions" />
          </div>
        </template>
      </Card>

      <!-- Gráfico de Hectares por Cultura -->
      <Card class="col-span-1">
        <template #title>
          <h2 class="text-lg font-semibold text-gray-800">Hectares por Cultura</h2>
        </template>
        <template #content>
          <div class="h-80">
            <Chart type="doughnut" :data="chartData.culturas" :options="culturasOptions" />
          </div>
        </template>
      </Card>
    </div>

    <!-- Gráfico de Animais por Espécie (largura total) -->
    <div class="mb-8">
      <Card>
        <template #title>
          <h2 class="text-lg font-semibold text-gray-800">Animais por Espécie</h2>
        </template>
        <template #content>
          <div class="h-80">
            <Chart type="bar" :data="chartData.especies" :options="especiesOptions" />
          </div>
        </template>
      </Card>
    </div>

    <!-- Atividades Recentes -->
    <Card class="mt-8">
      <template #title>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Atividades Recentes</h2>
      </template>
      <template #content>
        <div class="space-y-4">
          <div v-for="atividade in atividades.slice(0, 6)" :key="atividade.id"
            class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">

            <!-- Ícone -->
            <div class="flex-shrink-0 mr-6">
              <div class="activity-icon-container" :class="getActivityIconClass(atividade.tipo, atividade.descricao)">
                <i :class="getActivityIcon(atividade.tipo, atividade.descricao)" class="activity-icon"></i>
              </div>
            </div>

            <!-- Conteúdo -->
            <div class="flex-1 min-w-0">
              <p class="text-lg font-bold text-gray-900 mb-1"
                v-html="'&nbsp;' + formatActivityDescription(atividade.descricao, atividade.tipo)"></p>
              <div class="flex items-center ml-1">
                <span class="activity-timestamp text-gray-500">{{ atividade.tempo_relativo.replace('há', 'há ')
                }}</span>
              </div>
            </div>
          </div>

          <!-- Estado vazio -->
          <div v-if="atividades.length === 0" class="text-center py-12">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg font-medium">Nenhuma atividade recente encontrada</p>
          </div>
        </div>
      </template>
    </Card>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg">
        <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
        <p class="mt-2 text-gray-600">Carregando dados...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api } from '../services/api'

// Interfaces
interface Stats {
  produtores: number
  propriedades: number
  unidades: number
  rebanhos: number
}

interface Atividade {
  id: number
  descricao: string
  icon: string
  color: string
  cor: string
  tipo: string
  tempo_relativo: string
  usuario: string
  localizacao: string
}

// Estado reativo
const loading = ref(false)
const stats = ref<Stats>({
  produtores: 0,
  propriedades: 0,
  unidades: 0,
  rebanhos: 0
})

const chartData = ref({
  municipios: {
    labels: [] as string[],
    datasets: [{
      label: 'Propriedades',
      data: [] as number[],
      backgroundColor: [
        '#3B82F6', // Azul
        '#10B981', // Verde
        '#F59E0B', // Laranja
        '#EF4444', // Vermelho
        '#8B5CF6', // Roxo
        '#06B6D4'  // Ciano
      ],
      borderColor: ['#ffffff'],
      borderWidth: 2
    }]
  },
  especies: {
    labels: [] as string[],
    datasets: [{
      label: 'Quantidade',
      data: [] as number[],
      backgroundColor: [
        '#3B82F6', // Azul para Bovinos
        '#10B981', // Verde para Suínos
        '#F59E0B'  // Laranja para Caprinos
      ],
      borderColor: ['#ffffff'],
      borderWidth: 2
    }]
  },
  culturas: {
    labels: [] as string[],
    datasets: [{
      label: 'Hectares',
      data: [] as number[],
      backgroundColor: [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
      ],
      borderColor: ['#ffffff'],
      borderWidth: 2
    }]
  }
})

const atividades = ref<Atividade[]>([])

// Opções específicas para cada gráfico
const municipiosOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12,
          weight: 'bold'
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: '#fff',
      borderWidth: 1,
      cornerRadius: 6,
      displayColors: true
    }
  }
}

const especiesOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(16, 185, 129, 0.9)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: '#047857',
      borderWidth: 1,
      cornerRadius: 6
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
        drawBorder: false
      },
      ticks: {
        font: { size: 11 },
        color: '#6B7280'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        font: { size: 11 },
        color: '#6B7280'
      }
    }
  }
}

const culturasOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12,
          weight: 'bold'
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: '#fff',
      borderWidth: 1,
      cornerRadius: 6,
      displayColors: true
    }
  }
}

// Função para carregar dados do dashboard
const loadDashboardData = async () => {
  try {
    loading.value = true

    // Carregar dados do dashboard
    console.log('📊 Carregando dados do dashboard...')
    const response = await api.get('/v1/dashboard')
    console.log('📊 Resposta da API:', response.data)

    if (response.data.success) {
      const data = response.data.data
      console.log('📊 Dados recebidos:', data)

      // Atualizar estatísticas
      stats.value = {
        produtores: data.estatisticas?.total_produtores?.valor || 0,
        propriedades: data.estatisticas?.total_propriedades?.valor || 0,
        unidades: data.estatisticas?.total_unidades_producao?.valor || 0,
        rebanhos: data.estatisticas?.total_animais?.valor || 0
      }
      console.log('📊 Estatísticas atualizadas:', stats.value)

      // Carregar dados para gráficos
      await loadChartData()

      // Carregar atividades recentes
      if (data.atividades_recentes && Array.isArray(data.atividades_recentes)) {
        atividades.value = data.atividades_recentes.map((atividade: any) => ({
          id: atividade.id,
          descricao: atividade.descricao,
          icon: atividade.icone,
          color: atividade.cor,
          cor: atividade.cor,
          tipo: atividade.tipo,
          tempo_relativo: atividade.tempo_relativo,
          usuario: atividade.usuario || 'Sistema',
          localizacao: 'Sistema Agropecuário'
        }))
        console.log('📊 Atividades carregadas:', atividades.value)
      } else {
        atividades.value = []
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  } finally {
    loading.value = false
  }
}

// Função para carregar dados dos gráficos
const loadChartData = async () => {
  try {
    // Carregar dados de animais por espécie
    const especiesResponse = await api.get('/v1/relatorios/animais-especie')
    if (especiesResponse.data.success && especiesResponse.data.data) {
      const especiesData = especiesResponse.data.data
      chartData.value.especies.labels = especiesData.map((item: any) => item.especie)
      chartData.value.especies.datasets[0].data = especiesData.map((item: any) => item.total_animais)
      console.log('📊 Gráfico espécies carregado:', chartData.value.especies)
    }

    // Carregar dados de propriedades por município
    const municipiosResponse = await api.get('/v1/relatorios/propriedades-municipio')
    if (municipiosResponse.data.success && municipiosResponse.data.data) {
      const municipiosData = municipiosResponse.data.data
      chartData.value.municipios.labels = municipiosData.map((item: any) => item.municipio)
      chartData.value.municipios.datasets[0].data = municipiosData.map((item: any) => item.total_propriedades)
      console.log('📊 Gráfico municípios carregado:', chartData.value.municipios)
    }

    // Carregar dados de hectares por cultura
    const culturasResponse = await api.get('/v1/relatorios/hectares-cultura')
    console.log('📊 Resposta culturas:', culturasResponse.data)
    if (culturasResponse.data.success && culturasResponse.data.data) {
      const culturasData = culturasResponse.data.data
      console.log('📊 Dados culturas:', culturasData)
      // Pegar apenas as 8 principais culturas
      const culturasOrdenadas = culturasData
        .sort((a: any, b: any) => parseFloat(b.total_hectares.replace(',', '.')) - parseFloat(a.total_hectares.replace(',', '.')))
        .slice(0, 8)

      chartData.value.culturas.labels = culturasOrdenadas.map((item: any) => item.propriedade_nome)
      chartData.value.culturas.datasets[0].data = culturasOrdenadas.map((item: any) => parseFloat(item.total_hectares.replace(',', '.')))
      console.log('📊 Gráfico culturas carregado:', chartData.value.culturas)
    }
  } catch (error) {
    console.error('Erro ao carregar dados dos gráficos:', error)
  }
}

// Função para obter ícone apropriado baseado no tipo de atividade
const getActivityIcon = (tipo: string, descricao?: string) => {
  // Se for relatório, verificar se é Excel, CSV ou PDF
  if (tipo === 'relatorio_gerado') {
    if (descricao && descricao.toLowerCase().includes('excel')) {
      return 'fas fa-file-excel'
    }
    if (descricao && descricao.toLowerCase().includes('csv')) {
      return 'fas fa-file-csv'
    }
    return 'fas fa-file-pdf'
  }

  const icons: { [key: string]: string } = {
    'produtor_cadastrado': 'fas fa-user',
    'propriedade_cadastrada': 'fas fa-home',
    'rebanho_cadastrado': 'fas fa-cow',
    'unidade_cadastrada': 'fas fa-seedling',
    'relatorio_gerado': 'fas fa-file-pdf'
  }
  return icons[tipo] || 'fas fa-info-circle'
}


// Função para obter classe CSS do ícone baseado no tipo
const getActivityIconClass = (tipo: string, descricao?: string) => {
  // Se for relatório, verificar se é Excel, CSV ou PDF
  if (tipo === 'relatorio_gerado') {
    if (descricao && descricao.toLowerCase().includes('excel')) {
      return 'activity-icon-excel'
    }
    if (descricao && descricao.toLowerCase().includes('csv')) {
      return 'activity-icon-csv'
    }
    return 'activity-icon-pdf'
  }

  const iconClasses: { [key: string]: string } = {
    'produtor_cadastrado': 'activity-icon-produtor',
    'propriedade_cadastrada': 'activity-icon-propriedade',
    'rebanho_cadastrado': 'activity-icon-rebanho',
    'unidade_cadastrada': 'activity-icon-unidade',
    'relatorio_gerado': 'activity-icon-pdf'
  }
  return iconClasses[tipo] || 'activity-icon-default'
}

// Função para obter cor do tipo de arquivo
const getFileTypeColor = (tipo: string, descricao?: string) => {
  // Se for relatório, verificar se é Excel, CSV ou PDF
  if (tipo === 'relatorio_gerado') {
    if (descricao && descricao.toLowerCase().includes('excel')) {
      return '#10B981' // Verde para Excel
    }
    if (descricao && descricao.toLowerCase().includes('csv')) {
      return '#3B82F6' // Azul claro para CSV
    }
    return '#EF4444' // Vermelho para PDF
  }

  const colors: { [key: string]: string } = {
    'produtor_cadastrado': '#3b82f6', // Azul (matching activity-icon-produtor)
    'propriedade_cadastrada': '#10b981', // Verde (matching activity-icon-propriedade)
    'rebanho_cadastrado': '#8b5cf6', // Roxo (matching activity-icon-rebanho)
    'unidade_cadastrada': '#f59e0b' // Laranja (matching activity-icon-unidade)
  }
  return colors[tipo] || '#6B7280'
}


// Função para formatar descrição com palavras coloridas
const formatActivityDescription = (descricao: string, tipo: string) => {
  // Usar a função getFileTypeColor para consistência
  const color = getFileTypeColor(tipo, descricao)

  // Mapear tipos para palavras-chave
  const keywordMap: { [key: string]: string } = {
    'produtor_cadastrado': 'Produtor',
    'propriedade_cadastrada': 'Propriedade',
    'rebanho_cadastrado': 'Rebanho',
    'unidade_cadastrada': 'Unidade',
    'relatorio_gerado': 'Relatório'
  }

  const keyword = keywordMap[tipo] || 'Atividade'

  // Substituir a palavra-chave pela versão colorida
  return descricao.replace(
    new RegExp(`\\b${keyword}\\b`, 'gi'),
    `<span style="color: ${color}; font-weight: 600;">${keyword}</span>`
  )
}

// Carregar dados quando o componente for montado
onMounted(() => {
  loadDashboardData()

  // Atualizar atividades a cada 10 segundos
  setInterval(() => {
    loadAtividades()
  }, 10000)
})

// Função para carregar apenas as atividades
const loadAtividades = async () => {
  try {
    const atividadesResponse = await api.get('/v1/dashboard/atividades')

    if (atividadesResponse.data.success) {
      atividades.value = atividadesResponse.data.data.map((atividade: any) => ({
        id: atividade.id,
        descricao: atividade.descricao,
        icon: atividade.icone,
        color: atividade.cor,
        tipo: atividade.tipo,
        tempo_relativo: atividade.tempo_relativo,
        usuario: atividade.usuario,
        localizacao: atividade.localizacao || ''
      }))
    }
  } catch (error) {
    console.error('Erro ao carregar atividades:', error)
  }
}
</script>

<style scoped>
.dashboard {
  background-color: #f8fafc;
  min-height: 100vh;
  padding: 1rem;
}

@media (max-width: 1023px) {
  .dashboard {
    padding: 0.75rem;
    min-width: 1024px;
  }
}

.stats-card {
  border: 1px solid #e5e7eb;
  background: white;
}

@media (max-width: 1023px) {
  .stats-card .p-card-content {
    padding: 1rem;
  }

  .stats-card h3 {
    font-size: 0.875rem;
  }

  .stats-card p {
    font-size: 1.5rem;
  }

  .stats-card i {
    font-size: 1.5rem;
  }
}
</style>