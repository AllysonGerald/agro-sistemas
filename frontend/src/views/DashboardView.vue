<template>
  <div class="dashboard-container">
    <!-- Header com Boas-vindas e Clima -->
    <div class="dashboard-header">
      <div class="welcome-section">
        <h1 class="welcome-title">
          Olá, {{ authStore.user?.name || 'Usuário' }}!
        </h1>
        <p class="welcome-subtitle">
          <i class="fas fa-calendar-day"></i>
          <span>{{ formatDate(new Date()) }}</span>
        </p>
      </div>

      <!-- Widget de Clima -->
      <div class="clima-widget" v-if="clima">
        <div class="clima-icon">
          <i :class="getClimaIcon(clima.condicao)"></i>
        </div>
        <div class="clima-info">
          <div class="clima-temp">{{ clima.temperatura }}°C</div>
          <div class="clima-desc">{{ clima.descricao }}</div>
          <div class="clima-local">
            <i class="fas fa-map-marker-alt"></i>
            <span>{{ clima.cidade }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Cards de Estatísticas Principais -->
    <div class="stats-grid">
      <div class="stat-card-modern" style="border-left-color: #2563eb;">
        <div class="stat-icon-wrapper" style="background: #dbeafe;">
          <i class="fas fa-paw" style="color: #2563eb;"></i>
        </div>
        <div class="stat-content-modern">
          <span class="stat-label-modern">Total de Animais</span>
          <span class="stat-value-modern">{{ stats.total_animais }}</span>
          <span class="stat-change positive">
            <i class="fas fa-arrow-up"></i> +{{ stats.variacao_animais }}% este mês
          </span>
        </div>
      </div>

      <div class="stat-card-modern" style="border-left-color: #16a34a;">
        <div class="stat-icon-wrapper" style="background: #dcfce7;">
          <i class="fas fa-dollar-sign" style="color: #16a34a;"></i>
        </div>
        <div class="stat-content-modern">
          <span class="stat-label-modern">Saldo Financeiro</span>
          <span class="stat-value-modern">R$ {{ formatCurrency(stats.saldo_financeiro) }}</span>
          <span :class="stats.variacao_financeiro >= 0 ? 'stat-change positive' : 'stat-change negative'">
            <i :class="stats.variacao_financeiro >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
            {{ stats.variacao_financeiro >= 0 ? '+' : '' }}{{ stats.variacao_financeiro }}% este mês
          </span>
        </div>
      </div>

      <div class="stat-card-modern" style="border-left-color: #f59e0b;">
        <div class="stat-icon-wrapper" style="background: #fef3c7;">
          <i class="fas fa-warehouse" style="color: #f59e0b;"></i>
        </div>
        <div class="stat-content-modern">
          <span class="stat-label-modern">Itens em Estoque</span>
          <span class="stat-value-modern">{{ stats.itens_estoque }}</span>
          <span class="stat-alert" v-if="stats.estoque_baixo > 0">
            <i class="fas fa-exclamation-triangle"></i> {{ stats.estoque_baixo }} em nível baixo
          </span>
        </div>
      </div>

      <div class="stat-card-modern" style="border-left-color: #8b5cf6;">
        <div class="stat-icon-wrapper" style="background: #f3e8ff;">
          <i class="fas fa-tasks" style="color: #8b5cf6;"></i>
        </div>
        <div class="stat-content-modern">
          <span class="stat-label-modern">Atividades do Mês</span>
          <span class="stat-value-modern">{{ stats.atividades_mes }}</span>
          <span class="stat-info">
            <i class="fas fa-check-circle"></i> {{ stats.ultimas_24h }} nas últimas 24h
          </span>
        </div>
      </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="card">
      <h3 class="section-title">
        <i class="fas fa-bolt"></i>
        <span>Ações Rápidas</span>
      </h3>
      <div class="acoes-rapidas-grid">
        <router-link to="/animais" class="acao-rapida-card">
          <div class="acao-icon" style="background: #dbeafe; color: #2563eb;">
            <i class="fas fa-paw"></i>
          </div>
          <span class="acao-label">Novo Animal</span>
        </router-link>

        <router-link to="/financeiro" class="acao-rapida-card">
          <div class="acao-icon" style="background: #dcfce7; color: #16a34a;">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <span class="acao-label">Transação</span>
        </router-link>

        <router-link to="/manejo" class="acao-rapida-card">
          <div class="acao-icon" style="background: #fef3c7; color: #f59e0b;">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <span class="acao-label">Manejo</span>
        </router-link>

        <router-link to="/estoque" class="acao-rapida-card">
          <div class="acao-icon" style="background: #fee2e2; color: #dc2626;">
            <i class="fas fa-warehouse"></i>
          </div>
          <span class="acao-label">Estoque</span>
        </router-link>

        <router-link to="/lotes" class="acao-rapida-card">
          <div class="acao-icon" style="background: #f3e8ff; color: #8b5cf6;">
            <i class="fas fa-box"></i>
          </div>
          <span class="acao-label">Lotes</span>
        </router-link>

        <router-link to="/relatorios" class="acao-rapida-card">
          <div class="acao-icon" style="background: #e0e7ff; color: #6366f1;">
            <i class="fas fa-file-alt"></i>
          </div>
          <span class="acao-label">Relatórios</span>
        </router-link>
      </div>
    </div>

    <!-- Gráficos -->
    <div class="graficos-grid">
      <!-- Gráfico de Animais por Mês -->
      <div class="card">
        <h3 class="section-title">
          <i class="fas fa-chart-line"></i>
          <span>Evolução do Rebanho</span>
        </h3>
        <div class="grafico-container">
          <Line :data="graficos.evolucao" :options="evolucaoOptions" />
        </div>
      </div>

      <!-- Gráfico de Receitas vs Despesas -->
      <div class="card">
        <h3 class="section-title">
          <i class="fas fa-chart-bar"></i>
          <span>Receitas vs Despesas (6 meses)</span>
        </h3>
        <div class="grafico-container">
          <Bar :data="graficos.financeiro" :options="financeiroOptions" />
        </div>
      </div>
    </div>

    <!-- Mais Gráficos -->
    <div class="graficos-grid-secondary">
      <!-- Distribuição de Animais por Situação -->
      <div class="card">
        <h3 class="section-title">
          <i class="fas fa-chart-pie"></i>
          <span>Animais por Situação</span>
        </h3>
        <div class="grafico-container-small">
          <Doughnut :data="graficos.situacao" :options="doughnutOptions" />
        </div>
      </div>

      <!-- Tipos de Estoque -->
      <div class="card">
        <h3 class="section-title">
          <i class="fas fa-chart-pie"></i>
          <span>Distribuição de Estoque</span>
        </h3>
        <div class="grafico-container-small">
          <Doughnut :data="graficos.estoque" :options="doughnutOptions" />
        </div>
      </div>

      <!-- Atividades por Tipo -->
      <div class="card">
        <h3 class="section-title">
          <i class="fas fa-chart-pie"></i>
          <span>Atividades por Tipo</span>
        </h3>
        <div class="grafico-container-small">
          <Doughnut :data="graficos.atividades" :options="doughnutOptions" />
        </div>
      </div>
    </div>

    <!-- Atividades Recentes -->
    <div class="card">
      <h3 class="section-title">
        <i class="fas fa-history mr-2"></i>
        Atividades Recentes
      </h3>
      <div v-if="atividadesRecentes.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Nenhuma atividade registrada ainda</p>
      </div>
      <div v-else class="atividades-list">
        <div v-for="atividade in atividadesRecentes" :key="atividade.id" class="atividade-item">
          <div class="atividade-icon" :class="`icon-${atividade.tipo}`">
            <i :class="getAtividadeIcon(atividade.tipo)"></i>
          </div>
          <div class="atividade-content">
            <p class="atividade-descricao">{{ atividade.descricao }}</p>
            <span class="atividade-data">{{ atividade.data_relativa }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay">
      <ProgressSpinner />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line, Bar, Doughnut } from 'vue-chartjs'
import ProgressSpinner from 'primevue/progressspinner'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const authStore = useAuthStore()
const loading = ref(false)

// Clima
const clima = ref<any>(null)

// Estatísticas
const stats = reactive({
  total_animais: 0,
  variacao_animais: 0,
  saldo_financeiro: 0,
  variacao_financeiro: 0,
  itens_estoque: 0,
  estoque_baixo: 0,
  atividades_mes: 0,
  ultimas_24h: 0,
})

// Atividades Recentes
const atividadesRecentes = ref<any[]>([])

// Gráficos
const graficos = reactive({
  evolucao: {
    labels: [] as string[],
    datasets: [
      {
        label: 'Total de Animais',
        data: [] as number[],
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 3,
      }
    ]
  },
  financeiro: {
    labels: [] as string[],
    datasets: [
      {
        label: 'Receitas',
        data: [] as number[],
        backgroundColor: '#16a34a',
      },
      {
        label: 'Despesas',
        data: [] as number[],
        backgroundColor: '#dc2626',
      }
    ]
  },
  situacao: {
    labels: ['Ativos', 'Vendidos', 'Transferidos'],
    datasets: [{
      data: [0, 0, 0],
      backgroundColor: ['#16a34a', '#2563eb', '#f59e0b'],
    }]
  },
  estoque: {
    labels: ['Rações', 'Medicamentos', 'Vacinas', 'Suplementos', 'Outros'],
    datasets: [{
      data: [0, 0, 0, 0, 0],
      backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#64748b'],
    }]
  },
  atividades: {
    labels: ['Pesagens', 'Vacinações', 'Tratamentos', 'Outros'],
    datasets: [{
      data: [0, 0, 0, 0],
      backgroundColor: ['#f59e0b', '#16a34a', '#dc2626', '#64748b'],
    }]
  },
})

// Opções dos Gráficos
const evolucaoOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top' as const,
    },
    tooltip: {
      mode: 'index' as const,
      intersect: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
    },
    x: {
      grid: {
        display: false,
      },
    },
  },
}

const financeiroOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top' as const,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        callback: function (value: any) {
          return 'R$ ' + value.toLocaleString('pt-BR')
        }
      }
    },
    x: {
      grid: {
        display: false,
      },
    },
  },
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
    },
  },
}

// Funções
const loadDashboardData = async () => {
  try {
    loading.value = true

    // Carregar estatísticas
    const statsResponse = await api.get('/v1/dashboard/estatisticas')
    if (statsResponse.data.success) {
      Object.assign(stats, statsResponse.data.data)
    }

    // Carregar gráfico de evolução
    const evolucaoResponse = await api.get('/v1/dashboard/grafico-evolucao')
    console.log('📊 Evolução Response:', evolucaoResponse.data)
    if (evolucaoResponse.data.success) {
      graficos.evolucao.labels = evolucaoResponse.data.data.labels
      graficos.evolucao.datasets[0].data = evolucaoResponse.data.data.valores
      console.log('✅ Evolução carregado:', graficos.evolucao)
    }

    // Carregar gráfico financeiro
    const financeiroResponse = await api.get('/v1/dashboard/grafico-financeiro')
    console.log('💰 Financeiro Response:', financeiroResponse.data)
    if (financeiroResponse.data.success) {
      graficos.financeiro.labels = financeiroResponse.data.data.labels
      graficos.financeiro.datasets[0].data = financeiroResponse.data.data.receitas
      graficos.financeiro.datasets[1].data = financeiroResponse.data.data.despesas
      console.log('✅ Financeiro carregado:', graficos.financeiro)
    }

    // Carregar distribuições
    const distribuicaoResponse = await api.get('/v1/dashboard/distribuicoes')
    console.log('📦 Distribuições Response:', distribuicaoResponse.data)
    if (distribuicaoResponse.data.success) {
      const dist = distribuicaoResponse.data.data

      if (dist.situacao) {
        graficos.situacao.datasets[0].data = [
          dist.situacao.ativo || 0,
          dist.situacao.vendido || 0,
          dist.situacao.transferido || 0
        ]
      }

      if (dist.estoque) {
        graficos.estoque.datasets[0].data = [
          dist.estoque.racao || 0,
          dist.estoque.medicamento || 0,
          dist.estoque.vacina || 0,
          dist.estoque.suplemento || 0,
          dist.estoque.outros || 0
        ]
      }

      if (dist.atividades) {
        graficos.atividades.datasets[0].data = [
          dist.atividades.pesagem || 0,
          dist.atividades.vacinacao || 0,
          dist.atividades.tratamento || 0,
          dist.atividades.outros || 0
        ]
      }
      console.log('✅ Distribuições carregadas:', { situacao: graficos.situacao, estoque: graficos.estoque, atividades: graficos.atividades })
    }

    // Carregar atividades recentes
    const atividadesResponse = await api.get('/v1/dashboard/atividades-recentes')
    if (atividadesResponse.data.success) {
      atividadesRecentes.value = atividadesResponse.data.data
    }

    // Carregar clima
    await loadClima()

  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  } finally {
    loading.value = false
  }
}

const loadClima = async () => {
  try {
    // Tentar obter localização do usuário
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const { latitude, longitude } = position.coords

          try {
            // Buscar dados do clima
            const weatherResponse = await fetch(
              `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,weather_code&timezone=auto`
            )

            // Buscar nome da cidade usando API BigDataCloud (sem CORS)
            let nomeCidade = 'Sua Localização'

            try {
              const geoResponse = await fetch(
                `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=pt`
              )

              if (geoResponse.ok) {
                const geoData = await geoResponse.json()
                nomeCidade = geoData.city || geoData.locality || geoData.principalSubdivision || 'Sua Localização'
              }
            } catch (geoError) {
              // Se geocoding falhar, usar coordenadas aproximadas
              nomeCidade = `${latitude.toFixed(2)}°, ${longitude.toFixed(2)}°`
            }

            if (weatherResponse.ok) {
              const data = await weatherResponse.json()
              const weatherCode = data.current.weather_code

              // Mapear códigos WMO para condições
              let condicao = 'Clear'
              let descricao = 'Ensolarado'

              if (weatherCode >= 51 && weatherCode <= 67) {
                condicao = 'Rain'
                descricao = 'Chuva'
              } else if (weatherCode >= 71 && weatherCode <= 77) {
                condicao = 'Snow'
                descricao = 'Neve'
              } else if (weatherCode >= 1 && weatherCode <= 3) {
                condicao = 'Clouds'
                descricao = 'Parcialmente nublado'
              } else if (weatherCode === 0) {
                condicao = 'Clear'
                descricao = 'Ensolarado'
              }

              clima.value = {
                temperatura: Math.round(data.current.temperature_2m),
                descricao: descricao,
                condicao: condicao,
                cidade: nomeCidade
              }
            } else {
              setClimaDefault()
            }
          } catch (error) {
            setClimaDefault()
          }
        },
        () => {
          // Se usuário negar ou erro na geolocalização, usar dados padrão
          setClimaDefault()
        }
      )
    } else {
      // Navegador não suporta geolocalização
      setClimaDefault()
    }
  } catch (error) {
    setClimaDefault()
  }
}

const setClimaDefault = () => {
  clima.value = {
    temperatura: 28,
    descricao: 'Ensolarado',
    condicao: 'Clear',
    cidade: 'Goiânia'
  }
}

const getClimaIcon = (condicao: string) => {
  const icons: any = {
    'Clear': 'fas fa-sun',
    'Clouds': 'fas fa-cloud',
    'Rain': 'fas fa-cloud-rain',
    'Drizzle': 'fas fa-cloud-rain',
    'Thunderstorm': 'fas fa-bolt',
    'Snow': 'fas fa-snowflake',
  }
  return icons[condicao] || 'fas fa-cloud-sun'
}

const getAtividadeIcon = (tipo: string) => {
  const icons: any = {
    'animal': 'fas fa-paw',
    'financeiro': 'fas fa-dollar-sign',
    'manejo': 'fas fa-clipboard-list',
    'estoque': 'fas fa-warehouse',
    'lote': 'fas fa-box',
  }
  return icons[tipo] || 'fas fa-info-circle'
}

const formatDate = (date: Date) => {
  return date.toLocaleDateString('pt-BR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (value: number) => {
  if (!value) return '0,00'
  return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
.dashboard-container {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 2rem;
}

.welcome-section {
  flex: 1;
}

.welcome-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.welcome-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.welcome-subtitle i {
  font-size: 0.95rem;
}

/* Clima Widget */
.clima-widget {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  min-width: 280px;
}

.clima-icon {
  font-size: 3rem;
}

.clima-temp {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.clima-desc {
  font-size: 0.875rem;
  opacity: 0.9;
  text-transform: capitalize;
}

.clima-local {
  font-size: 0.75rem;
  opacity: 0.8;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.clima-local i {
  font-size: 0.7rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card-modern {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.stat-content-modern {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.stat-label-modern {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.5rem;
}

.stat-value-modern {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.stat-change,
.stat-alert,
.stat-info {
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.stat-change.positive {
  color: #16a34a;
}

.stat-change.negative {
  color: #dc2626;
}

.stat-alert {
  color: #f59e0b;
}

.stat-info {
  color: #64748b;
}

/* Card Genérico */
.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title i {
  color: #16a34a;
  font-size: 1.25rem;
}

.section-title span {
  flex: 1;
}

/* Ações Rápidas */
.acoes-rapidas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.acao-rapida-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem 1rem;
  background: #f8fafc;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.2s;
  border: 2px solid transparent;
}

.acao-rapida-card:hover {
  background: white;
  border-color: currentColor;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.acao-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.acao-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

/* Gráficos */
.graficos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.graficos-grid-secondary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.grafico-container {
  height: 320px;
  position: relative;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.grafico-container-small {
  height: 300px;
  position: relative;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Atividades */
.atividades-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.atividade-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  transition: background 0.2s;
}

.atividade-item:hover {
  background: #f1f5f9;
}

.atividade-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.icon-animal {
  background: #dbeafe;
  color: #2563eb;
}

.icon-financeiro {
  background: #dcfce7;
  color: #16a34a;
}

.icon-manejo {
  background: #fef3c7;
  color: #f59e0b;
}

.icon-estoque {
  background: #fee2e2;
  color: #dc2626;
}

.icon-lote {
  background: #f3e8ff;
  color: #8b5cf6;
}

.atividade-content {
  flex: 1;
}

.atividade-descricao {
  font-size: 0.875rem;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
  font-weight: 500;
}

.atividade-data {
  font-size: 0.75rem;
  color: #64748b;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Loading */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
  }

  .clima-widget {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .graficos-grid {
    grid-template-columns: 1fr;
  }

  .graficos-grid-secondary {
    grid-template-columns: 1fr;
  }

  .acoes-rapidas-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
