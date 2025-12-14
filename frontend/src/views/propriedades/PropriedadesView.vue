<template>
  <div class="propriedades-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Propriedades Rurais</h2>
          <p class="text-600 mt-0 mb-0">Gerencie as propriedades rurais cadastradas no sistema</p>
        </div>
        <div class="header-actions">
          <Button label="Nova Propriedade" icon="pi pi-plus" @click="openCreateDialog" :loading="loading" size="large"
            class="nova-propriedade-btn" />
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por nome, município ou Estado..."
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="acoes-container">
          <Button label="Limpar Filtros" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters"
            :disabled="!searchTerm" class="limpar-btn" />
        </div>
      </div>
    </div>

    <!-- Tabela de Propriedades -->
    <div class="card">
      <DataTable :value="propriedades" :loading="loading" responsive-layout="scroll" class="p-datatable-sm">
        <Column field="nome" header="Nome" sortable>
          <template #body="slotProps">
            <strong>{{ slotProps.data.nome }}</strong>
          </template>
        </Column>

        <Column field="municipio" header="Município" sortable />

        <Column field="uf" header="Estado" sortable>
          <template #body="slotProps">
            <span class="font-bold text-blue-600">{{ slotProps.data.uf }}</span>
          </template>
        </Column>

        <Column field="inscricao_estadual" header="Inscrição Estadual">
          <template #body="slotProps">
            <span class="font-mono">{{ slotProps.data.inscricao_estadual || '-' }}</span>
          </template>
        </Column>

        <Column field="area_total" header="Área Total (ha)" sortable>
          <template #body="slotProps">
            <span class="font-mono">{{ formatArea(slotProps.data.area_total) }}</span>
          </template>
        </Column>

        <Column field="produtor.nome" header="Produtor" sortable>
          <template #body="slotProps">
            <span class="text-green-600 font-medium">{{ slotProps.data.produtor?.nome || '-' }}</span>
          </template>
        </Column>

        <Column field="created_at" header="Data Cadastro" sortable>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.created_at) }}
          </template>
        </Column>

        <Column header="Ações" :exportable="false" style="min-width: 10rem">
          <template #body="slotProps">
            <div class="flex gap-4 justify-content-start">
              <Button icon="pi pi-eye" severity="info" outlined size="small"
                @click.stop="viewPropriedade(slotProps.data)" v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" outlined size="small"
                @click.stop="editPropriedade(slotProps.data)" v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" outlined size="small"
                @click.stop="confirmDelete(slotProps.data)" v-tooltip="'Excluir'" />
            </div>
          </template>
        </Column>

        <template #empty>
          <div class="text-center p-4">
            <i class="pi pi-map text-4xl text-400 mb-3"></i>
            <p class="text-600 text-lg">Nenhuma propriedade encontrada</p>
            <p class="text-500">
              {{ searchTerm ? 'Tente ajustar os filtros de busca' : 'Clique em "Nova Propriedade" para começar' }}
            </p>
          </div>
        </template>
      </DataTable>

      <!-- Paginação Customizada -->
      <CustomPagination :current-page="pagination.current" :total-pages="pagination.totalPages"
        :total="pagination.total" :per-page="pagination.perPage" @page-change="onPageChange"
        @per-page-change="onPerPageChange" />
    </div>

    <!-- Modal Moderno de Propriedade -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 900px;">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="fas fa-home"></i>
            </div>
            {{ dialogMode === 'create' ? 'Nova Propriedade Rural' : dialogMode === 'edit' ? 'Editar Propriedade Rural' :
              'Visualizar Propriedade Rural' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <form @submit.prevent="savePropriedade" class="modal-form">
            <!-- Nome da Propriedade e Produtor-->
            <div class="form-row">
              <div class="form-col-65">
                <TextInput id="nome" label="Nome da Propriedade" v-model="form.nome"
                  placeholder="Digite o nome da propriedade" :required="true" :disabled="dialogMode === 'view'"
                  :error="errors.nome" />
              </div>
              <div class="form-col-35">
                <DropdownInput id="produtor_id" label="Produtor Rural" v-model="form.produtor_id"
                  :options="produtoresOptions" :required="true" :disabled="dialogMode === 'view'"
                  placeholder="Selecione" optionLabel="nome" optionValue="id" :error="errors.produtor_id" />
              </div>
            </div>

            <!-- Separador de Localização -->
            <div class="form-section-divider">
              <div class="divider-line"></div>
              <div class="divider-content">
                <i class="pi pi-map-marker"></i>
                <span>Localização</span>
              </div>
              <div class="divider-line"></div>
            </div>

            <!-- CEP com hint -->
            <div class="field-with-hint">
              <TextInput id="cep" label="CEP" v-model="form.cep" placeholder="00000-000" :required="false"
                :disabled="dialogMode === 'view'" :error="errors.cep" @blur="buscarCep" />
              <small class="field-hint">
                <i class="pi pi-info-circle"></i>
                Digite o CEP e pressione TAB para preencher automaticamente
              </small>
            </div>

            <!-- Logradouro e Número -->
            <div class="form-row">
              <div class="form-col-75">
                <TextInput id="logradouro" label="Logradouro" v-model="form.logradouro"
                  placeholder="Rua, Avenida, Estrada, etc." :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.logradouro" />
              </div>
              <div class="form-col-25">
                <TextInput id="numero" label="Número" v-model="form.numero" placeholder="Nº" :required="false"
                  :disabled="dialogMode === 'view'" :error="errors.numero" />
              </div>
            </div>

            <!-- Complemento e Bairro -->
            <div class="form-row">
              <div class="form-col-50">
                <TextInput id="complemento" label="Complemento" v-model="form.complemento"
                  placeholder="Km, Fazenda, Sítio" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.complemento" />
              </div>
              <div class="form-col-50">
                <TextInput id="bairro" label="Bairro/Distrito" v-model="form.bairro" placeholder="Nome do bairro"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.bairro" />
              </div>
            </div>

            <!-- Município e Estado -->
            <div class="form-row">
              <div class="form-col-75">
                <TextInput id="municipio" label="Município" v-model="form.municipio" placeholder="Nome do município"
                  :required="true" :disabled="dialogMode === 'view'" :error="errors.municipio" />
              </div>
              <div class="form-col-25">
                <DropdownInput id="uf" label="Estado (UF)" v-model="form.uf" :options="ufOptions" optionLabel="label"
                  optionValue="value" placeholder="UF" :required="true" :disabled="dialogMode === 'view'"
                  :error="errors.uf" />
              </div>
            </div>

            <!-- Coordenadas GPS -->
            <div class="form-row">
              <div class="form-col-50">
                <TextInput id="latitude" label="Latitude" v-model="form.latitude" placeholder="-5.123456"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.latitude" />
              </div>
              <div class="form-col-50">
                <TextInput id="longitude" label="Longitude" v-model="form.longitude" placeholder="-42.123456"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.longitude" />
              </div>
            </div>

            <!-- Separador de Documentação -->
            <div class="form-section-divider">
              <div class="divider-line"></div>
              <div class="divider-content">
                <i class="pi pi-file"></i>
                <span>Documentação e Registro</span>
              </div>
              <div class="divider-line"></div>
            </div>

            <!-- Inscrição Estadual e CAR -->
            <div class="form-row">
              <div class="form-col-50">
                <TextInput id="inscricao_estadual" label="Inscrição Estadual (IE)" v-model="form.inscricao_estadual"
                  placeholder="000.000.000.000" :disabled="dialogMode === 'view'" :error="errors.inscricao_estadual" />
              </div>
              <div class="form-col-50">
                <TextInput id="car" label="CAR" v-model="form.car" placeholder="XX-0000000-XXXX" :required="false"
                  :disabled="dialogMode === 'view'" :error="errors.car" />
              </div>
            </div>

            <!-- Matrícula e Cartório -->
            <div class="form-row">
              <div class="form-col-50">
                <TextInput id="matricula" label="Número da Matrícula" v-model="form.matricula"
                  placeholder="Matrícula do imóvel" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.matricula" />
              </div>
              <div class="form-col-50">
                <TextInput id="cartorio" label="Cartório de Registro" v-model="form.cartorio"
                  placeholder="Nome do cartório" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.cartorio" />
              </div>
            </div>

            <!-- Separador de Áreas -->
            <div class="form-section-divider">
              <div class="divider-line"></div>
              <div class="divider-content">
                <i class="pi pi-chart-bar"></i>
                <span>Áreas e Tipo de Exploração</span>
              </div>
              <div class="divider-line"></div>
            </div>

            <!-- Áreas e Data de Aquisição -->
            <div class="form-row">
              <div class="form-col-35">
                <NumberInput id="area_total" label="Área Total (ha)" v-model="form.area_total" placeholder="Ex: 100.50"
                  :required="true" :disabled="dialogMode === 'view'" :error="errors.area_total" size="small"
                  :step="0.01" />
              </div>
              <div class="form-col-35">
                <NumberInput id="area_preservada" label="Área Preservada (ha)" v-model="form.area_preservada"
                  placeholder="Ex: 20.00" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.area_preservada" size="small" :step="0.01" />
              </div>
              <div class="form-col-30">
                <CalendarInput id="data_aquisicao" label="Data Aquisição" v-model="form.data_aquisicao"
                  :disabled="dialogMode === 'view'" :error="errors.data_aquisicao" />
              </div>
            </div>

            <!-- Tipo de Exploração -->
            <div class="form-group">
              <label for="tipo_exploracao" class="form-label">Tipo de Exploração</label>
              <div class="form-dropdown">
                <select id="tipo_exploracao" v-model="form.tipo_exploracao" :disabled="dialogMode === 'view'"
                  class="form-select">
                  <option value="" disabled>Selecione o tipo de exploração</option>
                  <option v-for="tipo in tiposExploracao" :key="tipo.value" :value="tipo.value">
                    {{ tipo.label }}
                  </option>
                </select>
              </div>
              <div v-if="errors.tipo_exploracao" class="form-error">{{ errors.tipo_exploracao }}</div>
            </div>

            <!-- Observações -->
            <TextareaInput id="observacoes" label="Observações" v-model="form.observacoes"
              placeholder="Informações adicionais sobre a propriedade (benfeitorias, recursos hídricos, etc.)"
              :required="false" :disabled="dialogMode === 'view'" :error="errors.observacoes" :rows="5" />
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="savePropriedade"
            :class="{ 'btn-loading': dialogLoading }" :disabled="dialogLoading">
            <i :class="dialogMode === 'create' ? 'pi pi-plus' : 'pi pi-save'"></i>
            {{ dialogMode === 'create' ? 'Criar' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação Moderno -->
    <div v-if="showConfirmDialog" class="modal-overlay" @click.self="showConfirmDialog = false">
      <div class="modal-container" style="max-width: 450px;">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            Confirmar Exclusão
          </h2>
          <button class="modal-close" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <div style="padding: 1rem 0; text-align: center;">
            <p
              style="margin: 0 0 1rem 0; color: var(--text-primary); font-weight: 500; font-size: 1.1rem; line-height: 1.5;">
              Tem certeza que deseja excluir a propriedade <strong>{{ propriedadeToDelete?.nome }}</strong>?
            </p>
            <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">
              Esta ação não pode ser desfeita.
            </p>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false" :disabled="deleteLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deletePropriedade"
            :class="{ 'btn-loading': deleteLoading }" :disabled="deleteLoading">
            <i class="pi pi-trash"></i>
            Excluir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, nextTick } from 'vue'
import { useToast } from 'primevue/usetoast'
import Select from 'primevue/select'
import api from '../../services/api'
import { TextInput, NumberInput, DropdownInput, TextareaInput, CalendarInput, CustomPagination } from '../../components/forms'

// Interfaces
interface Propriedade {
  id: number
  nome: string
  cep?: string
  logradouro?: string
  numero?: string
  complemento?: string
  bairro?: string
  municipio: string
  uf: string
  inscricao_estadual?: string
  car?: string
  matricula?: string
  cartorio?: string
  latitude?: string
  longitude?: string
  area_total: number
  area_preservada?: number
  tipo_exploracao?: string
  data_aquisicao?: string
  observacoes?: string
  produtor_id: number
  produtor?: {
    id: number
    nome: string
  }
  created_at: string
  updated_at: string
}

interface Produtor {
  id: number
  nome: string
}

interface Pagination {
  current: number
  perPage: number
  total: number
  totalPages: number
}

// Composables
const toast = useToast()

// Estado reativo
const propriedades = ref<Propriedade[]>([])
const loading = ref(false)
const searchTerm = ref('')

const pagination = reactive<Pagination>({
  current: 1,
  perPage: 10,
  total: 0,
  totalPages: 0
})

// Dialog states
const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const deleteLoading = ref(false)
const propriedadeToDelete = ref<Propriedade | null>(null)

// Form data
const form = reactive({
  id: null as number | null,
  nome: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  municipio: '',
  uf: '',
  inscricao_estadual: '',
  car: '',
  matricula: '',
  cartorio: '',
  latitude: '',
  longitude: '',
  area_total: '',
  area_preservada: '',
  tipo_exploracao: '',
  data_aquisicao: null as Date | null,
  observacoes: '',
  produtor_id: null as number | null
})

const errors = reactive({
  nome: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  municipio: '',
  uf: '',
  inscricao_estadual: '',
  car: '',
  matricula: '',
  cartorio: '',
  latitude: '',
  longitude: '',
  area_total: '',
  area_preservada: '',
  tipo_exploracao: '',
  data_aquisicao: '',
  observacoes: '',
  produtor_id: ''
})

// Opções para dropdowns
const ufOptions = [
  { label: 'Acre', value: 'AC' },
  { label: 'Alagoas', value: 'AL' },
  { label: 'Amapá', value: 'AP' },
  { label: 'Amazonas', value: 'AM' },
  { label: 'Bahia', value: 'BA' },
  { label: 'Ceará', value: 'CE' },
  { label: 'Distrito Federal', value: 'DF' },
  { label: 'Espírito Santo', value: 'ES' },
  { label: 'Goiás', value: 'GO' },
  { label: 'Maranhão', value: 'MA' },
  { label: 'Mato Grosso', value: 'MT' },
  { label: 'Mato Grosso do Sul', value: 'MS' },
  { label: 'Minas Gerais', value: 'MG' },
  { label: 'Pará', value: 'PA' },
  { label: 'Paraíba', value: 'PB' },
  { label: 'Paraná', value: 'PR' },
  { label: 'Pernambuco', value: 'PE' },
  { label: 'Piauí', value: 'PI' },
  { label: 'Rio de Janeiro', value: 'RJ' },
  { label: 'Rio Grande do Norte', value: 'RN' },
  { label: 'Rio Grande do Sul', value: 'RS' },
  { label: 'Rondônia', value: 'RO' },
  { label: 'Roraima', value: 'RR' },
  { label: 'Santa Catarina', value: 'SC' },
  { label: 'São Paulo', value: 'SP' },
  { label: 'Sergipe', value: 'SE' },
  { label: 'Tocantins', value: 'TO' }
]

const produtoresOptions = ref<Produtor[]>([])

const tiposExploracao = [
  { label: 'Pecuária', value: 'pecuaria' },
  { label: 'Agricultura', value: 'agricultura' },
  { label: 'Mista', value: 'mista' },
  { label: 'Silvicultura', value: 'silvicultura' },
  { label: 'Outro', value: 'outro' }
]

// Debounce function
const createDebounce = (func: Function, delay: number) => {
  let timeout: ReturnType<typeof setTimeout>
  return (...args: any[]) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(null, args), delay)
  }
}

const debouncedSearch = createDebounce(() => {
  pagination.current = 1
  loadPropriedades()
}, 500)

// Methods
const loadPropriedades = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value && searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    const response = await api.get('/v1/propriedades', { params })

    if (response.data.success) {
      propriedades.value = Array.isArray(response.data.data.data) ? response.data.data.data : []

      // Extrair metadados de paginação
      if (response.data.data) {
        pagination.total = response.data.data.total || 0
        pagination.totalPages = response.data.data.last_page || 1
      }
    } else {
      throw new Error(response.data.message || 'Erro ao carregar propriedades')
    }
  } catch (error: any) {
    if (error.response?.status === 401) {
      toast.add({
        severity: 'warn',
        summary: 'Sessão Expirada',
        detail: 'Por favor, faça login novamente',
        life: 5000
      })
    } else if (error.response?.status !== 404) {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao carregar propriedades',
        life: 5000
      })
    }
  } finally {
    loading.value = false
  }
}

const loadProdutores = async () => {
  try {
    const response = await api.get('/v1/produtores-rurais', { params: { per_page: 1000 } })

    if (response.data.success) {
      produtoresOptions.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Erro ao carregar produtores:', error)
  }
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadPropriedades()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1 // Reset to first page when changing per page
  loadPropriedades()
}

const clearFilters = () => {
  searchTerm.value = ''
  pagination.current = 1
  loadPropriedades()
}

const openCreateDialog = async () => {
  // Garantir que os produtores estejam carregados antes de abrir o modal
  if (produtoresOptions.value.length === 0) {
    await loadProdutores()
  }
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewPropriedade = async (propriedade: Propriedade) => {
  // Garantir que os produtores estejam carregados antes de abrir o modal
  if (produtoresOptions.value.length === 0) {
    await loadProdutores()
  }
  fillForm(propriedade)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editPropriedade = async (propriedade: Propriedade) => {
  // Garantir que os produtores estejam carregados antes de abrir o modal
  if (produtoresOptions.value.length === 0) {
    await loadProdutores()
  }
  fillForm(propriedade)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = (propriedade: Propriedade) => {
  form.id = propriedade.id
  form.nome = propriedade.nome || ''
  form.cep = propriedade.cep || ''
  form.logradouro = propriedade.logradouro || ''
  form.numero = propriedade.numero || ''
  form.complemento = propriedade.complemento || ''
  form.bairro = propriedade.bairro || ''
  form.municipio = propriedade.municipio || ''
  form.uf = propriedade.uf || ''
  form.inscricao_estadual = propriedade.inscricao_estadual || ''
  form.car = propriedade.car || ''
  form.matricula = propriedade.matricula || ''
  form.cartorio = propriedade.cartorio || ''
  form.latitude = propriedade.latitude ? String(propriedade.latitude) : ''
  form.longitude = propriedade.longitude ? String(propriedade.longitude) : ''
  form.area_total = propriedade.area_total ? String(propriedade.area_total) : ''
  form.area_preservada = propriedade.area_preservada ? String(propriedade.area_preservada) : ''
  form.tipo_exploracao = propriedade.tipo_exploracao || ''
  // Converter data para objeto Date que o CalendarInput espera
  form.data_aquisicao = propriedade.data_aquisicao ? new Date(propriedade.data_aquisicao) : null
  form.observacoes = propriedade.observacoes || ''
  form.produtor_id = propriedade.produtor_id || null
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.cep = ''
  form.logradouro = ''
  form.numero = ''
  form.complemento = ''
  form.bairro = ''
  form.municipio = ''
  form.uf = ''
  form.inscricao_estadual = ''
  form.car = ''
  form.matricula = ''
  form.cartorio = ''
  form.latitude = ''
  form.longitude = ''
  form.area_total = ''
  form.area_preservada = ''
  form.tipo_exploracao = ''
  form.data_aquisicao = null
  form.observacoes = ''
  form.produtor_id = null
  clearErrors()
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    (errors as any)[key] = ''
  })
}

// Buscar CEP via ViaCEP API
const buscarCep = async () => {
  const cep = form.cep.replace(/\D/g, '')
  if (cep.length !== 8) return

  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await response.json()

    if (!data.erro) {
      form.logradouro = data.logradouro || ''
      form.bairro = data.bairro || ''
      form.municipio = data.localidade || ''
      form.uf = data.uf || ''

      toast.add({
        severity: 'success',
        summary: 'CEP encontrado',
        detail: 'Endereço preenchido automaticamente',
        life: 3000
      })
    } else {
      toast.add({
        severity: 'warn',
        summary: 'CEP não encontrado',
        detail: 'Preencha o endereço manualmente',
        life: 3000
      })
    }
  } catch (error) {
    console.error('Erro ao buscar CEP:', error)
  }
}


const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.nome.trim()) {
    errors.nome = 'Nome da propriedade é obrigatório'
    isValid = false
  }

  if (!form.municipio.trim()) {
    errors.municipio = 'Município é obrigatório'
    isValid = false
  }

  if (!form.uf) {
    errors.uf = 'UF é obrigatória'
    isValid = false
  }

  if (!form.area_total || parseFloat(form.area_total) <= 0) {
    errors.area_total = 'Área total deve ser maior que zero'
    isValid = false
  }

  if (!form.produtor_id) {
    errors.produtor_id = 'Produtor é obrigatório'
    isValid = false
  }

  return isValid
}

const savePropriedade = async () => {
  if (!validateForm()) return

  try {
    dialogLoading.value = true

    const payload = {
      nome: form.nome,
      cep: form.cep,
      logradouro: form.logradouro,
      numero: form.numero,
      complemento: form.complemento,
      bairro: form.bairro,
      municipio: form.municipio,
      uf: form.uf,
      inscricao_estadual: form.inscricao_estadual,
      car: form.car,
      matricula: form.matricula,
      cartorio: form.cartorio,
      latitude: form.latitude ? parseFloat(form.latitude) : null,
      longitude: form.longitude ? parseFloat(form.longitude) : null,
      area_total: parseFloat(form.area_total),
      area_preservada: form.area_preservada ? parseFloat(form.area_preservada) : null,
      tipo_exploracao: form.tipo_exploracao || null,
      data_aquisicao: form.data_aquisicao ? new Date(form.data_aquisicao).toISOString().split('T')[0] : null,
      observacoes: form.observacoes,
      produtor_id: form.produtor_id
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/propriedades', payload)
    } else {
      response = await api.put(`/v1/propriedades/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Propriedade ${dialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso`,
        life: 3000
      })

      closeDialog()
      await loadPropriedades()
    } else {
      throw new Error(response.data.message || 'Erro ao salvar propriedade')
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const backendErrors = error.response.data.errors
      Object.keys(backendErrors).forEach(key => {
        if (key in errors) {
          (errors as any)[key] = backendErrors[key][0]
        }
      })
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar propriedade',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (propriedade: Propriedade) => {
  propriedadeToDelete.value = propriedade
  showConfirmDialog.value = true
}

const deletePropriedade = async () => {
  if (!propriedadeToDelete.value) return

  try {
    deleteLoading.value = true

    const response = await api.delete(`/v1/propriedades/${propriedadeToDelete.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Propriedade excluída com sucesso',
        life: 3000
      })

      showConfirmDialog.value = false
      loadPropriedades()
    } else {
      throw new Error(response.data.message || 'Erro ao excluir propriedade')
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao excluir propriedade',
      life: 5000
    })
  } finally {
    deleteLoading.value = false
  }
}

const closeDialog = () => {
  showDialog.value = false
  resetForm()
}


// Utility functions
const formatArea = (value: number) => {
  if (!value) return '0,00'
  return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

// Lifecycle
onMounted(() => {
  const token = localStorage.getItem('token')
  console.log('Token presente:', !!token)
  if (!token) {
    console.warn('Usuário não autenticado!')
    toast.add({
      severity: 'warn',
      summary: 'Não autenticado',
      detail: 'Por favor, faça login',
      life: 3000
    })
  }
  loadPropriedades()
  loadProdutores()
})
</script>

<style scoped>
.propriedades-container {
  padding: 1rem;
}

.form-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 1rem;
  color: #1f2937;
  background-color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.25rem;
  padding-right: 2.5rem;
}

.form-select:hover:not(:disabled) {
  border-color: #16a34a;
}

.form-select:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.form-select:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
  opacity: 0.6;
}

.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
  padding: 1.5rem;
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

.header-actions {
  flex-shrink: 0;
  margin-left: auto;
}

.nova-propriedade-btn {
  min-width: 180px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
}

/* Filtros */
.filtros-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.busca-container {
  flex: 1;
  min-width: 300px;
  max-width: 500px;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
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

.acoes-container {
  flex-shrink: 0;
}

.limpar-btn {
  min-width: 140px;
  padding: 12px 20px;
  height: 44px;
  transition: border-color 0.10s ease, background-color 0.10s ease, color 0.10s ease;
  border: 2px solid #e5e7eb;
  color: #6b7280;
}

.limpar-btn:hover:not(:disabled) {
  border-color: #10b981;
  background-color: #f0fdf4;
  color: #10b981;
}

.limpar-btn:active:not(:disabled) {
  background-color: #dcfce7;
}

.limpar-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .filtros-container {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .busca-container {
    min-width: auto;
    max-width: none;
  }

  .acoes-container {
    width: 100%;
  }

  .limpar-btn {
    width: 100%;
  }
}

.font-mono {
  font-family: 'Courier New', monospace;
}

.p-dialog .p-dialog-content {
  padding: 2rem !important;
}

.p-dialog .p-dialog-header {
  padding-bottom: 0.5rem !important;
}

.p-dialog .grid>div {
  margin-bottom: 1.25rem;
}

/* Ajuste específico para modal de propriedades */
.propriedades-container .modal-overlay {
  z-index: 9999 !important;
  position: fixed !important;
  display: flex !important;
}

.propriedades-container .modal-container {
  position: relative !important;
  z-index: 10000 !important;
}

.propriedades-container .modal-content {
  max-height: 70vh !important;
  overflow-y: auto !important;
}

/* Divisor de seção do formulário */
.form-section-divider {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1.5rem 0 1rem 0;
  width: 100%;
}

.divider-line {
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, transparent, #d1d5db, transparent);
}

.divider-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 20px;
  color: #16a34a;
  font-weight: 600;
  font-size: 0.95rem;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(22, 163, 74, 0.1);
}

.divider-content i {
  font-size: 1rem;
}

/* Campo com hint */
.field-with-hint {
  width: 100%;
}

.field-hint {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 0.25rem;
  padding-left: 0.25rem;
  color: #6b7280;
  font-size: 0.75rem;
  font-style: italic;
}

.field-hint i {
  font-size: 0.7rem;
  color: #16a34a;
}

/* Layout de linha do formulário */
.form-row {
  display: flex;
  gap: 1rem;
  width: 100%;
}

.form-col-25 {
  flex: 0 0 calc(25% - 0.75rem);
  min-width: 0;
}

.form-col-30 {
  flex: 0 0 calc(30% - 0.7rem);
  min-width: 0;
}

.form-col-35 {
  flex: 0 0 calc(35% - 0.65rem);
  min-width: 0;
}

.form-col-50 {
  flex: 0 0 calc(50% - 0.5rem);
  min-width: 0;
}

.form-col-65 {
  flex: 0 0 calc(65% - 0.35rem);
  min-width: 0;
}

.form-col-75 {
  flex: 0 0 calc(75% - 0.25rem);
  min-width: 0;
}

/* Responsividade para form-row */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 0;
  }

  .form-col-25,
  .form-col-30,
  .form-col-35,
  .form-col-50,
  .form-col-65,
  .form-col-75 {
    flex: 1 1 100%;
    max-width: 100%;
  }

  .divider-content {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
  }
}
</style>
