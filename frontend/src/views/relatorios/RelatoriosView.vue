<template>
  <div class="relatorios-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Relatórios e Exportações</h2>
          <p class="text-600 mt-0 mb-0">Gere relatórios e exporte dados do sistema agropecuário</p>
        </div>
      </div>
    </div>

    <!-- Filtros de Relatórios -->
    <div class="card">
      <div class="filtros-container">
        <div class="filtro-item">
          <label class="block text-900 font-medium mb-2">Tipo de Relatório</label>
          <Select v-model="filtros.tipoRelatorio" :options="tiposRelatorio" class="w-full"
            placeholder="Selecione o tipo de relatório" optionLabel="label" optionValue="value" />
        </div>

        <div class="filtro-item" v-if="filtros.tipoRelatorio === 'rebanhos_por_produtor'">
          <label class="block text-900 font-medium mb-2">Produtor</label>
          <Select v-model="filtros.produtorId" :options="produtores" class="w-full" placeholder="Selecione o produtor"
            optionLabel="nome" optionValue="id" />
        </div>

        <div class="filtro-item" v-if="filtros.tipoRelatorio === 'hectares_por_cultura'">
          <label class="block text-900 font-medium mb-2">Nome das Propriedades</label>
          <AutoComplete 
            v-model="filtros.search" 
            :suggestions="propriedadesSugestoes" 
            @complete="buscarPropriedades"
            @focus="carregarPropriedadesIniciais"
            placeholder="Digite o nome da propriedade para buscar..."
            class="w-full"
            :dropdown="true"
            :minLength="0"
            optionLabel="label"
            forceSelection
            @item-select="onPropriedadeSelecionada"
          />
        </div>

        <div class="filtro-item">
          <label class="block text-900 font-medium mb-2">Formato de Exportação</label>
          <Select v-model="filtros.formato" :options="formatosExportacao" class="w-full"
            placeholder="Selecione o formato" optionLabel="label" optionValue="value" />
        </div>

        <div class="filtro-item">
          <Button label="Gerar Relatório" icon="pi pi-file-pdf" @click="gerarRelatorio" :loading="loading"
            class="gerar-btn w-full" />
        </div>
      </div>
    </div>

    <!-- Relatórios Disponíveis -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-6 w-full">
      <!-- Relatório 1: Produtores Rurais -->
      <div class="card relatorio-card">
        <div class="relatorio-header">
          <div class="icon-container bg-blue-100">
            <i class="fas fa-user text-4xl" style="color: #2563eb;"></i>
          </div>
          <h3 class="text-xl font-semibold text-900 mb-2">Produtores Rurais</h3>
          <p class="text-600 text-sm">Relatório geral de produtores rurais cadastrados</p>
        </div>
        <div class="relatorio-actions">
          <Button label="Ver Relatório" icon="pi pi-eye" severity="info" outlined
            @click="visualizarRelatorio('produtores_rurais')" class="w-full mb-3" />
          <div class="export-buttons">
            <Button label="PDF" icon="pi pi-file-pdf" severity="danger" outlined size="small"
              @click="exportarRelatorio('produtores_rurais', 'pdf')" class="export-btn" />
            <Button label="Excel" icon="pi pi-file-excel" severity="success" outlined size="small"
              @click="exportarRelatorio('produtores_rurais', 'excel')" class="export-btn" />
            <Button label="CSV" icon="pi pi-file" severity="info" outlined size="small"
              @click="exportarRelatorio('produtores_rurais', 'csv')" class="export-btn" />
          </div>
        </div>
      </div>

      <!-- Relatório 2: Propriedades Rurais -->
      <div class="card relatorio-card">
        <div class="relatorio-header">
          <div class="icon-container bg-green-100">
            <i class="fas fa-home text-4xl" style="color: #16a34a;"></i>
          </div>
          <h3 class="text-xl font-semibold text-900 mb-2">Propriedades Rurais</h3>
          <p class="text-600 text-sm">Relatório geral de propriedades rurais cadastradas</p>
        </div>
        <div class="relatorio-actions">
          <Button label="Ver Relatório" icon="pi pi-eye" severity="info" outlined
            @click="visualizarRelatorio('propriedades_rurais')" class="w-full mb-3" />
          <div class="export-buttons">
            <Button label="PDF" icon="pi pi-file-pdf" severity="danger" outlined size="small"
              @click="exportarRelatorio('propriedades_rurais', 'pdf')" class="export-btn" />
            <Button label="Excel" icon="pi pi-file-excel" severity="success" outlined size="small"
              @click="exportarRelatorio('propriedades_rurais', 'excel')" class="export-btn" />
            <Button label="CSV" icon="pi pi-file" severity="info" outlined size="small"
              @click="exportarRelatorio('propriedades_rurais', 'csv')" class="export-btn" />
          </div>
        </div>
      </div>

      <!-- Relatório 3: Unidades de Produção -->
      <div class="card relatorio-card">
        <div class="relatorio-header">
          <div class="icon-container bg-orange-100">
            <i class="fas fa-seedling text-4xl" style="color: #f97316;"></i>
          </div>
          <h3 class="text-xl font-semibold text-900 mb-2">Unidades de Produção</h3>
          <p class="text-600 text-sm">Relatório geral de unidades de produção cadastradas</p>
        </div>
        <div class="relatorio-actions">
          <Button label="Ver Relatório" icon="pi pi-eye" severity="info" outlined
            @click="visualizarRelatorio('unidades_producao')" class="w-full mb-3" />
          <div class="export-buttons">
            <Button label="PDF" icon="pi pi-file-pdf" severity="danger" outlined size="small"
              @click="exportarRelatorio('unidades_producao', 'pdf')" class="export-btn" />
            <Button label="Excel" icon="pi pi-file-excel" severity="success" outlined size="small"
              @click="exportarRelatorio('unidades_producao', 'excel')" class="export-btn" />
            <Button label="CSV" icon="pi pi-file" severity="info" outlined size="small"
              @click="exportarRelatorio('unidades_producao', 'csv')" class="export-btn" />
          </div>
        </div>
      </div>

      <!-- Relatório 4: Rebanhos -->
      <div class="card relatorio-card">
        <div class="relatorio-header">
          <div class="icon-container bg-purple-100">
            <i class="fas fa-cow text-4xl" style="color: #a855f7;"></i>
          </div>
          <h3 class="text-xl font-semibold text-900 mb-2">Rebanhos</h3>
          <p class="text-600 text-sm">Relatório geral de rebanhos cadastrados</p>
        </div>
        <div class="relatorio-actions">
          <Button label="Ver Relatório" icon="pi pi-eye" severity="info" outlined
            @click="visualizarRelatorio('rebanhos')" class="w-full mb-3" />
          <div class="export-buttons">
            <Button label="PDF" icon="pi pi-file-pdf" severity="danger" outlined size="small"
              @click="exportarRelatorio('rebanhos', 'pdf')" class="export-btn" />
            <Button label="Excel" icon="pi pi-file-excel" severity="success" outlined size="small"
              @click="exportarRelatorio('rebanhos', 'excel')" class="export-btn" />
            <Button label="CSV" icon="pi pi-file" severity="info" outlined size="small"
              @click="exportarRelatorio('rebanhos', 'csv')" class="export-btn" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Visualização de Relatório -->
    <Dialog v-model="showRelatorioModal" :header="modalTitle" :modal="true"
      :style="{ width: '90vw', maxWidth: '1200px' }" :closable="true">
      <div v-if="relatorioData" class="relatorio-content">
        <!-- Gráfico -->
        <div v-if="relatorioData.chart" class="mb-6">
          <canvas ref="chartCanvas" width="400" height="200"></canvas>
        </div>

        <!-- Tabela de Dados -->
        <div v-if="relatorioData.table" class="mb-4">
          <DataTable :value="relatorioData.table" :paginator="true" :rows="10" responsive-layout="scroll"
            class="p-datatable-sm">
            <Column v-for="column in relatorioData.columns" :key="column.field" :field="column.field"
              :header="column.header" :sortable="true" />
          </DataTable>
        </div>

        <!-- Dados em Formato de Lista -->
        <div v-if="relatorioData.list" class="space-y-4">
          <div v-for="(item, index) in relatorioData.list" :key="index"
            class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
            <div>
              <h4 class="font-semibold text-gray-900">{{ item.label }}</h4>
              <p class="text-sm text-gray-600">{{ item.description }}</p>
            </div>
            <div class="text-right">
              <span class="text-2xl font-bold text-blue-600">{{ item.value }}</span>
              <p class="text-sm text-gray-500">{{ item.unit }}</p>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button label="Fechar" icon="pi pi-times" severity="secondary" outlined @click="showRelatorioModal = false" />
          <Button label="Exportar" icon="pi pi-download" @click="exportarRelatorioAtual" />
        </div>
      </template>
    </Dialog>

    <!-- Modal de Seleção de Produtor -->
    <Dialog v-model="showProdutorModal" header="Selecionar Produtor" :modal="true" :style="{ width: '600px' }">
      <div class="space-y-4">
        <div class="search-input-wrapper">
          <i class="fas fa-search search-icon"></i>
          <InputText v-model="produtorSearch" placeholder="Buscar produtor..." class="search-input" />
        </div>

        <DataTable :value="produtoresFiltrados" :paginator="true" :rows="10" responsive-layout="scroll"
          class="p-datatable-sm">
          <Column field="nome" header="Nome" sortable />
          <Column field="cpf_cnpj" header="CPF/CNPJ" sortable />
          <Column header="Ações" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
              <Button label="Selecionar" icon="pi pi-check" size="small" @click="selecionarProdutor(slotProps.data)" />
            </template>
          </Column>
        </DataTable>
      </div>
    </Dialog>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg">
        <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
        <p class="mt-3 text-gray-700">Gerando relatório...</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import InputText from 'primevue/inputtext'
import AutoComplete from 'primevue/autocomplete'
import api from '../../services/api'

// Interfaces
interface Produtor {
  id: number
  nome: string
  cpf_cnpj: string
}

interface RelatorioData {
  chart?: any
  table?: any[]
  list?: any[]
  columns?: any[]
}

// Composables
const toast = useToast()

// Estado reativo
const loading = ref(false)
const showRelatorioModal = ref(false)
const showProdutorModal = ref(false)
const relatorioData = ref<RelatorioData | null>(null)
const modalTitle = ref('')
const produtorSearch = ref('')
const produtores = ref<Produtor[]>([])
const produtorSelecionado = ref<Produtor | null>(null)
const propriedadesSugestoes = ref<any[]>([])

const filtros = reactive({
  tipoRelatorio: '',
  produtorId: null as number | null,
  formato: 'pdf',
  search: ''
})

// Opções para dropdowns
const tiposRelatorio = [
  { label: 'Produtores Rurais', value: 'produtores_rurais' },
  { label: 'Propriedades Rurais', value: 'propriedades_rurais' },
  { label: 'Unidades de Produção', value: 'unidades_producao' },
  { label: 'Rebanhos', value: 'rebanhos' },
  { label: 'Propriedades por Município', value: 'propriedades_por_municipio' },
  { label: 'Animais por Espécie', value: 'animais_por_especie' },
  { label: 'Hectares por Nome', value: 'hectares_por_cultura' },
  { label: 'Rebanhos por Produtor', value: 'rebanhos_por_produtor' },
]

const formatosExportacao = [
  { label: 'PDF', value: 'pdf' },
  { label: 'Excel', value: 'excel' },
  { label: 'CSV', value: 'csv' }
]

// Computed
const produtoresFiltrados = computed(() => {
  if (!produtorSearch.value) return produtores.value
  return produtores.value.filter(produtor =>
    produtor.nome.toLowerCase().includes(produtorSearch.value.toLowerCase()) ||
    produtor.cpf_cnpj.includes(produtorSearch.value)
  )
})

// Methods
const getColumnsForType = (tipo: string) => {
  const columnMaps: { [key: string]: any[] } = {
    'produtores_rurais': [
      { field: 'nome', header: 'Nome' },
      { field: 'cpf_cnpj', header: 'CPF/CNPJ' },
      { field: 'telefone', header: 'Telefone' },
      { field: 'email', header: 'Email' },
      { field: 'created_at', header: 'Data Cadastro' }
    ],
    'propriedades_rurais': [
      { field: 'nome', header: 'Nome' },
      { field: 'municipio', header: 'Município' },
      { field: 'uf', header: 'UF' },
      { field: 'area_total', header: 'Área Total (ha)' },
      { field: 'produtor.nome', header: 'Produtor' },
      { field: 'created_at', header: 'Data Cadastro' }
    ],
    'unidades_producao': [
      { field: 'nome_cultura', header: 'Cultura' },
      { field: 'area_total_ha', header: 'Área (ha)' },
      { field: 'coordenadas_geograficas', header: 'Coordenadas' },
      { field: 'propriedade.nome', header: 'Propriedade' },
      { field: 'propriedade.municipio', header: 'Município' },
      { field: 'created_at', header: 'Data Cadastro' }
    ],
    'rebanhos': [
      { field: 'especie', header: 'Espécie' },
      { field: 'quantidade', header: 'Quantidade' },
      { field: 'finalidade', header: 'Finalidade' },
      { field: 'propriedade.nome', header: 'Propriedade' },
      { field: 'propriedade.municipio', header: 'Município' },
      { field: 'data_atualizacao', header: 'Última Atualização' }
    ],
    'hectares_por_cultura': [
      { field: 'propriedade_nome', header: 'Nome da Propriedade' },
      { field: 'municipio', header: 'Município' },
      { field: 'uf', header: 'UF' },
      { field: 'total_hectares', header: 'Total de Hectares' },
      { field: 'total_unidades', header: 'Total de Unidades' },
      { field: 'total_culturas', header: 'Total de Culturas' },
      { field: 'culturas_lista', header: 'Culturas' }
    ]
  }

  return columnMaps[tipo] || []
}

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

const carregarPropriedadesIniciais = async () => {
  try {
    const response = await api.get('/v1/relatorios/propriedades', {
      params: { search: '', limit: 10 }
    })

    if (response.data.success) {
      propriedadesSugestoes.value = response.data.data
    }
  } catch (error) {
    console.error('Erro ao carregar propriedades iniciais:', error)
    propriedadesSugestoes.value = []
  }
}

const buscarPropriedades = async (event: any) => {
  try {
    const query = event.query
    if (query.length < 1) {
      propriedadesSugestoes.value = []
      return
    }

    const response = await api.get('/v1/relatorios/propriedades', {
      params: { search: query, limit: 10 }
    })

    if (response.data.success) {
      propriedadesSugestoes.value = response.data.data
    }
  } catch (error) {
    console.error('Erro ao buscar propriedades:', error)
    propriedadesSugestoes.value = []
  }
}

const onPropriedadeSelecionada = (event: any) => {
  if (event.value) {
    filtros.search = event.value.nome
  }
}

const visualizarRelatorio = async (tipo: string) => {
  try {
    loading.value = true
    modalTitle.value = tiposRelatorio.find(t => t.value === tipo)?.label || 'Relatório'

    // Mapear os tipos para as rotas corretas do backend
    const routeMap: { [key: string]: string } = {
      'produtores_rurais': '/v1/relatorios/produtores-rurais',
      'propriedades_rurais': '/v1/relatorios/propriedades-rurais',
      'unidades_producao': '/v1/relatorios/unidades-producao',
      'rebanhos': '/v1/relatorios/rebanhos',
      'propriedades_por_municipio': '/v1/relatorios/propriedades-municipio',
      'animais_por_especie': '/v1/relatorios/animais-especie',
      'hectares_por_cultura': '/v1/relatorios/hectares-cultura',
    }

    const route = routeMap[tipo]
    if (!route) {
      throw new Error('Tipo de relatório não encontrado')
    }

    const params: any = {}
    if (tipo === 'hectares_por_cultura' && filtros.search) {
      params.search = filtros.search
    }

    const response = await api.get(route, { params })

    if (response.data.success) {
      // Processar os dados para o formato esperado pelo modal
      const data = response.data.data
      relatorioData.value = {
        table: data.produtores || data.propriedades || data.unidades || data.rebanhos || data,
        columns: getColumnsForType(tipo)
      }
      showRelatorioModal.value = true
    } else {
      throw new Error(response.data.message || 'Erro ao carregar relatório')
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao carregar relatório',
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

const exportarRelatorio = async (tipo: string, formato: string) => {
  try {
    loading.value = true

    // Mapear os tipos para as rotas de exportação corretas do backend
    const exportRouteMap: { [key: string]: string } = {
      'produtores_rurais': '/v1/relatorios/exportar/produtores-rurais',
      'propriedades_rurais': '/v1/relatorios/exportar/propriedades-rurais',
      'unidades_producao': '/v1/relatorios/exportar/unidades-producao',
      'rebanhos': '/v1/relatorios/exportar/rebanhos',
      'propriedades_por_municipio': '/v1/relatorios/exportar/propriedades-municipio',
      'animais_por_especie': '/v1/relatorios/exportar/animais-especie',
      'hectares_por_cultura': '/v1/relatorios/exportar/hectares-cultura',
      'rebanhos_por_produtor': '/v1/relatorios/exportar/rebanhos-produtor',
    }

    const route = exportRouteMap[tipo]
    if (!route) {
      throw new Error('Tipo de relatório não encontrado')
    }

    const params: any = { formato }
    if (tipo === 'rebanhos_por_produtor') {
      if (produtorSelecionado.value) {
        params.produtor_id = produtorSelecionado.value.id
      } else if (filtros.produtorId) {
        params.produtor_id = filtros.produtorId
      }
    }

    if (tipo === 'hectares_por_cultura' && filtros.search) {
      params.search = filtros.search
    }

    const response = await api.get(route, {
      params,
      responseType: 'blob'
    })

    // Verificar se a resposta é válida
    if (!response.data || response.data.size === 0) {
      throw new Error('Arquivo vazio ou inválido')
    }

    // Criar link de download
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url

    const extensao = formato === 'excel' ? 'xlsx' : formato
    link.setAttribute('download', `relatorio_${tipo}.${extensao}`)

    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    toast.add({
      severity: 'success',
      summary: 'Sucesso',
      detail: 'Relatório exportado com sucesso',
      life: 3000
    })
  } catch (error: any) {
    console.error('Erro ao exportar relatório:', error)
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || error.message || 'Erro ao exportar relatório',
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

const exportarRelatorioAtual = () => {
  if (filtros.tipoRelatorio) {
    exportarRelatorio(filtros.tipoRelatorio, filtros.formato)
  }
}


const gerarRelatorio = () => {
  if (!filtros.tipoRelatorio) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Selecione um tipo de relatório',
      life: 3000
    })
    return
  }

  if (filtros.tipoRelatorio === 'rebanhos_por_produtor' && !filtros.produtorId) {
    toast.add({
      severity: 'warn',
      summary: 'Atenção',
      detail: 'Selecione um produtor primeiro',
      life: 3000
    })
    return
  }

  exportarRelatorio(filtros.tipoRelatorio, filtros.formato)
}


const selecionarProdutor = (produtor: Produtor) => {
  produtorSelecionado.value = produtor
  filtros.produtorId = produtor.id
  showProdutorModal.value = false

  toast.add({
    severity: 'success',
    summary: 'Sucesso',
    detail: `Produtor ${produtor.nome} selecionado`,
    life: 3000
  })
}

// Lifecycle
onMounted(() => {
  loadProdutores()
})
</script>

<style scoped>
.relatorios-container {
  padding: 1rem;
  width: 100%;
  overflow-x: auto;
  min-width: 0;
}

@media (max-width: 1023px) {
  .relatorios-container {
    padding: 0.75rem;
    min-width: 1024px;
  }
}

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
  padding: 1.5rem;
  width: 100%;
  min-width: 0;
  overflow-x: auto;
}

@media (max-width: 1023px) {
  .card {
    padding: 1rem;
    margin-bottom: 0.75rem;
    min-width: 1024px;
  }
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  width: 100%;
}

.header-content {
  flex: 1;
  min-width: 0;
}

/* Filtros */
.filtros-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  align-items: end;
  width: 100%;
  overflow-x: auto;
}

@media (max-width: 1023px) {
  .filtros-container {
    grid-template-columns: 1fr;
    gap: 0.75rem;
    min-width: 1024px;
  }

  .filtro-item {
    width: 100%;
  }

  .gerar-btn {
    width: 100%;
    min-width: auto;
  }
}

.filtro-item {
  display: flex;
  flex-direction: column;
  min-width: 0;
  flex: 1;
}

.gerar-btn {
  height: 40px;
  font-weight: 600;
  white-space: nowrap;
  min-width: 150px;
  flex-shrink: 0;
}

/* Garantir que botões não sejam cortados */
.p-button {
  flex-shrink: 0;
  min-width: fit-content;
}

/* Grid de relatórios */
.grid {
  width: 100%;
  min-width: 0;
}

/* Cards de Relatórios */
.relatorio-card {
  width: 100%;
  min-width: 0;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
  border: 1px solid #e5e7eb;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 1.25rem;
}

.relatorio-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-color: #10b981;
}

.relatorio-header {
  text-align: center;
  margin-bottom: 1.5rem;
  flex-grow: 1;
}

.icon-container {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.relatorio-card:hover .icon-container {
  transform: scale(1.1);
}

.relatorio-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: auto;
}

.export-buttons {
  display: flex;
  justify-content: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

@media (max-width: 480px) {
  .export-buttons {
    gap: 0.25rem;
  }

  .export-buttons .p-button {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
  }
}

.export-btn {
  min-width: 70px;
  flex: 0 0 auto;
  font-size: 0.8rem;
  padding: 0.4rem 0.6rem;
}

/* Modal de Relatório */
.relatorio-content {
  max-height: 70vh;
  overflow-y: auto;
}

/* Search Input */
.search-input-wrapper {
  position: relative;
  width: 100%;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #10b981;
  font-size: 16px;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 40px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
  outline: none;
}

.search-input::placeholder {
  color: #9ca3af;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
  .filtros-container {
    grid-template-columns: 1fr;
  }

  .relatorio-actions {
    flex-direction: column;
  }
}
</style>