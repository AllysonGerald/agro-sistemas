<template>
  <div class="relatorios-container">
    <!-- Header -->
    <div class="page-header">
      <h2 class="page-title">Relatórios e Exportações</h2>
      <p class="page-subtitle">Gere relatórios e exporte dados do sistema agropecuário</p>
    </div>

    <!-- Filtros de Relatórios -->
    <div class="card">
      <div class="filtros-grid">
        <!-- Tipo de Relatório -->
        <div class="filtro-item">
          <label class="filtro-label">Tipo de Relatório</label>
          <Select v-model="filtros.tipoRelatorio" :options="tiposRelatorio" placeholder="Selecione o tipo de relatório"
            optionLabel="label" optionValue="value" @change="onTipoChange" class="w-full" />
        </div>

        <!-- Campo Dinâmico: Produtor (para rebanhos-produtor) -->
        <div class="filtro-item" v-if="filtros.tipoRelatorio === 'rebanhos-produtor'">
          <label class="filtro-label">Produtor</label>
          <Select v-model="filtros.produtorId" :options="produtores" placeholder="Selecione o produtor"
            optionLabel="nome" optionValue="id" class="w-full" />
        </div>

        <!-- Campo Dinâmico: Propriedade (para hectares-cultura) -->
        <div class="filtro-item filtro-propriedade" v-if="filtros.tipoRelatorio === 'hectares-cultura'">
          <label class="filtro-label">Propriedade</label>
          <AutoComplete v-model="filtros.propriedadeSearch" :suggestions="propriedadesSugestoes"
            @complete="buscarPropriedades" placeholder="Digite o nome da propriedade..." optionLabel="nome"
            class="w-full" />
        </div>

        <!-- Formato de Exportação -->
        <div class="filtro-item">
          <label class="filtro-label">Formato de Exportação</label>
          <Select v-model="filtros.formato" :options="formatosExportacao" placeholder="Selecione o formato"
            optionLabel="label" optionValue="value" class="w-full" />
        </div>

        <!-- Botão Gerar -->
        <div class="filtro-item btn-container">
          <Button label="Gerar Relatório" icon="pi pi-file-pdf" @click="gerarRelatorioFiltro"
            :loading="loading !== null" :disabled="!filtros.tipoRelatorio || !filtros.formato"
            class="w-full btn-gerar" />
        </div>
      </div>
    </div>

    <!-- Relatórios Disponíveis em Grid Compacto -->
    <div class="card">
      <h3 class="text-xl font-semibold text-900 mb-4">Relatórios Disponíveis</h3>

      <div class="relatorios-grid">
        <div v-for="relatorio in relatoriosDisponiveis" :key="relatorio.endpoint" class="relatorio-card">
          <div class="relatorio-header">
            <div class="relatorio-icon-container" :style="`background: ${relatorio.corBg}`">
              <i :class="relatorio.icon" :style="`color: ${relatorio.cor}`"></i>
            </div>
            <div class="relatorio-info">
              <h4 class="relatorio-nome">{{ relatorio.nome }}</h4>
              <p class="relatorio-desc">{{ relatorio.descricao }}</p>
            </div>
          </div>

          <div class="relatorio-acoes">
            <Button label="PDF" icon="pi pi-file-pdf" severity="danger" size="small"
              @click="exportarRelatorio(relatorio.endpoint, 'pdf')"
              :loading="loading === `${relatorio.endpoint}-pdf`" />
            <Button label="Excel" icon="pi pi-file-excel" severity="success" size="small"
              @click="exportarRelatorio(relatorio.endpoint, 'excel')"
              :loading="loading === `${relatorio.endpoint}-excel`" />
            <Button v-if="!relatorio.semCSV" label="CSV" icon="pi pi-file" severity="info" size="small"
              @click="exportarRelatorio(relatorio.endpoint, 'csv')"
              :loading="loading === `${relatorio.endpoint}-csv`" />
            <Button v-if="relatorio.dashboard" label="Dashboard" icon="pi pi-chart-bar" severity="secondary"
              size="small" @click="abrirDashboard(relatorio.dashboard)" />
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <ProgressSpinner />
        <p>Gerando relatório...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Select from 'primevue/select'
import AutoComplete from 'primevue/autocomplete'
import ProgressSpinner from 'primevue/progressspinner'
import api from '../../services/api'

// Composables
const toast = useToast()

// Estado reativo
const loading = ref<string | null>(null)
const produtores = ref<any[]>([])
const propriedadesSugestoes = ref<any[]>([])

// Filtros
const filtros = reactive({
  tipoRelatorio: '',
  formato: 'pdf',
  produtorId: null as number | null,
  propriedadeSearch: ''
})

// Relatórios disponíveis
const relatoriosDisponiveis = ref([
  {
    nome: 'Produtores Rurais',
    descricao: 'Lista completa de produtores cadastrados',
    icon: 'fas fa-user',
    cor: '#2563eb',
    corBg: '#dbeafe',
    endpoint: 'produtores-rurais',
    semCSV: false
  },
  {
    nome: 'Propriedades Rurais',
    descricao: 'Propriedades cadastradas no sistema',
    icon: 'fas fa-home',
    cor: '#16a34a',
    corBg: '#dcfce7',
    endpoint: 'propriedades-rurais',
    semCSV: false
  },
  {
    nome: 'Unidades de Produção',
    descricao: 'Unidades produtivas por propriedade',
    icon: 'fas fa-seedling',
    cor: '#6366f1',
    corBg: '#e0e7ff',
    endpoint: 'unidades-producao',
    semCSV: false
  },
  {
    nome: 'Rebanhos',
    descricao: 'Rebanhos registrados por espécie',
    icon: 'fas fa-horse',
    cor: '#a855f7',
    corBg: '#f3e8ff',
    endpoint: 'rebanhos',
    semCSV: false
  },
  {
    nome: 'Propriedades por Município',
    descricao: 'Agrupamento por localização',
    icon: 'fas fa-map-marked-alt',
    cor: '#f59e0b',
    corBg: '#fef3c7',
    endpoint: 'propriedades-municipio',
    semCSV: false
  },
  {
    nome: 'Animais por Espécie',
    descricao: 'Quantitativo por tipo de animal',
    icon: 'fas fa-paw',
    cor: '#f97316',
    corBg: '#ffedd5',
    endpoint: 'animais-especie',
    semCSV: false
  },
  {
    nome: 'Hectares por Cultura',
    descricao: 'Área cultivada por tipo de cultura',
    icon: 'fas fa-leaf',
    cor: '#10b981',
    corBg: '#d1fae5',
    endpoint: 'hectares-cultura',
    semCSV: false
  },
  {
    nome: 'Rebanhos por Produtor',
    descricao: 'Rebanhos agrupados por produtor',
    icon: 'fas fa-user-tie',
    cor: '#14b8a6',
    corBg: '#ccfbf1',
    endpoint: 'rebanhos-produtor',
    semCSV: false
  },
  {
    nome: 'Transações Financeiras',
    descricao: 'Receitas, despesas e fluxo de caixa',
    icon: 'fas fa-dollar-sign',
    cor: '#22c55e',
    corBg: '#dcfce7',
    endpoint: 'transacoes',
    semCSV: false
  }
])

// Opções para dropdowns
const tiposRelatorio = [
  { label: 'Produtores Rurais', value: 'produtores-rurais' },
  { label: 'Propriedades Rurais', value: 'propriedades-rurais' },
  { label: 'Unidades de Produção', value: 'unidades-producao' },
  { label: 'Rebanhos', value: 'rebanhos' },
  { label: 'Propriedades por Município', value: 'propriedades-municipio' },
  { label: 'Animais por Espécie', value: 'animais-especie' },
  { label: 'Hectares por Cultura', value: 'hectares-cultura' },
  { label: 'Rebanhos por Produtor', value: 'rebanhos-produtor' },
]

const formatosExportacao = [
  { label: 'PDF', value: 'pdf' },
  { label: 'Excel', value: 'excel' },
  { label: 'CSV', value: 'csv' }
]

// Carregar dados
const loadProdutores = async () => {
  try {
    const response = await api.get('/v1/produtores-rurais', { params: { per_page: 1000 } })
    if (response.data.success) {
      produtores.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Erro ao carregar produtores:', error)
  }
}

const buscarPropriedades = async (event: any) => {
  try {
    const query = event.query || ''
    const response = await api.get('/v1/propriedades', {
      params: { search: query, per_page: 10 }
    })
    if (response.data.success) {
      propriedadesSugestoes.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erro ao buscar propriedades:', error)
  }
}

const onTipoChange = () => {
  // Resetar campos específicos quando mudar o tipo
  filtros.produtorId = null
  filtros.propriedadeSearch = ''

  // Carregar dados necessários
  if (filtros.tipoRelatorio === 'rebanhos-produtor') {
    loadProdutores()
  }
}

// Função para exportar relatórios
const exportarRelatorio = async (tipo: string, formato: string) => {
  try {
    const loadingKey = `${tipo}-${formato}`
    loading.value = loadingKey

    let endpoint = ''
    let params: any = { formato }

    // Determinar endpoint correto
    if (tipo === 'transacoes') {
      endpoint = `/v1/transacoes/relatorio/${formato}`
      params = {}
    } else {
      endpoint = `/v1/relatorios/exportar/${tipo}`
    }

    const response = await api.get(endpoint, {
      params,
      responseType: 'blob'
    })

    // Extrair nome do arquivo
    const contentDisposition = response.headers['content-disposition']
    let filename = `relatorio_${tipo}.${formato}`

    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i)
      if (filenameMatch && filenameMatch.length > 1) {
        filename = filenameMatch[1]
      }
    }

    // Download automático
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: `Relatório ${formato.toUpperCase()} gerado com sucesso!`,
      life: 3000
    })
  } catch (error: any) {
    console.error('Erro ao gerar relatório:', error)
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || `Erro ao gerar relatório ${formato.toUpperCase()}`,
      life: 5000
    })
  } finally {
    loading.value = null
  }
}

// Função para gerar relatório usando os filtros
const gerarRelatorioFiltro = async () => {
  if (!filtros.tipoRelatorio || !filtros.formato) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Selecione o tipo de relatório e o formato',
      life: 3000
    })
    return
  }

  await exportarRelatorio(filtros.tipoRelatorio, filtros.formato)
}

// Função para abrir dashboard
const abrirDashboard = (url: string) => {
  window.location.href = url
}

onMounted(() => {
  // Carregar dados iniciais se necessário
})
</script>

<style scoped>
.relatorios-container {
  padding: 2rem;
  background: #f5f7fa;
  min-height: calc(100vh - 80px);
}

.page-header {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1.5rem 0;
}

/* Filtros */
.filtros-grid {
  display: grid;
  grid-template-columns: 1.2fr 1.5fr 1fr auto;
  gap: 1rem;
  align-items: end;
}

.filtro-item {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.filtro-propriedade {
  /* Largura proporcional definida pelo grid */
}

.filtro-label {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.btn-container {
  padding-top: 1.7rem;
  min-width: 180px;
}

.btn-gerar {
  height: 2.5rem;
  background: #16a34a !important;
  border-color: #16a34a !important;
}

.btn-gerar:hover {
  background: #15803d !important;
  border-color: #15803d !important;
}

.w-full {
  width: 100%;
}

/* Grid de Relatórios */
.relatorios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.relatorio-card {
  background: #ffffff;
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 1.5rem;
  transition: all 0.25s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  min-height: 280px;
}

.relatorio-card:hover {
  border-color: #10b981;
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
  transform: translateY(-4px);
}

.relatorio-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-grow: 1;
}

.relatorio-icon-container {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.relatorio-card:hover .relatorio-icon-container {
  transform: scale(1.08);
}

.relatorio-icon-container i {
  font-size: 1.75rem;
}

.relatorio-info {
  width: 100%;
}

.relatorio-nome {
  font-size: 1.0625rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  line-height: 1.4;
}

.relatorio-desc {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
}

.relatorio-acoes {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  margin-top: auto;
  padding-top: 0.5rem;
}

.relatorio-acoes button {
  width: 100%;
  font-weight: 600;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.relatorio-acoes button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

.loading-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
}

.loading-content p {
  margin-top: 1rem;
  color: #64748b;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 1400px) {
  .filtros-grid {
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .filtro-propriedade {
    min-width: 100%;
    grid-column: 1 / -1;
  }

  .btn-container {
    grid-column: 1 / -1;
    padding-top: 0;
  }

  .relatorios-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .relatorios-container {
    padding: 1rem;
  }

  .page-header {
    padding: 1.5rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .card {
    padding: 1.5rem;
  }

  .filtros-grid {
    grid-template-columns: 1fr;
  }

  .filtro-propriedade {
    min-width: 100%;
  }

  .btn-container {
    min-width: 100%;
    padding-top: 0;
  }

  .relatorios-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .relatorio-card {
    min-height: auto;
  }
}
</style>
