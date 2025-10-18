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
              <Button icon="pi pi-eye" severity="info" outlined size="small" @click="viewPropriedade(slotProps.data)"
                v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" outlined size="small"
                @click="editPropriedade(slotProps.data)" v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" outlined size="small" @click="confirmDelete(slotProps.data)"
                v-tooltip="'Excluir'" />
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
      <div class="modal-container" style="max-width: 600px;">
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
            <!-- Nome da Propriedade -->
            <TextInput id="nome" label="Nome da Propriedade" v-model="form.nome"
              placeholder="Digite o nome da propriedade" :required="true" :disabled="dialogMode === 'view'"
              :error="errors.nome" />

            <!-- Município -->
            <TextInput id="municipio" label="Município" v-model="form.municipio" placeholder="Digite o município"
              :required="true" :disabled="dialogMode === 'view'" :error="errors.municipio" />

            <!-- Estado -->
            <div class="form-group">
              <label for="uf" class="form-label">
                Estado
                <span class="form-label-required">*</span>
              </label>
              <div class="form-dropdown">
                <Select id="uf" v-model="form.uf" :options="ufOptions" :class="{ 'error': errors.uf }"
                  :disabled="dialogMode === 'view'" placeholder="Selecione o estado" optionLabel="label"
                  optionValue="value" style="width: 100%;" />
              </div>
              <div v-if="errors.uf" class="form-error">{{ errors.uf }}</div>
            </div>

            <!-- Inscrição Estadual -->
            <TextInput id="inscricao_estadual" label="Inscrição Estadual" v-model="form.inscricao_estadual"
              placeholder="Digite a inscrição estadual" :disabled="dialogMode === 'view'"
              :error="errors.inscricao_estadual" />

            <!-- Área Total -->
            <NumberInput id="area_total" label="Área Total (hectares)" v-model="form.area_total"
              placeholder="Ex: 100.50" :required="true" :disabled="dialogMode === 'view'" :error="errors.area_total"
              size="small" :step="0.1" />

            <!-- Produtor -->
            <DropdownInput 
              id="produtor_id" 
              label="Produtor Rural" 
              v-model="form.produtor_id"
              :options="produtoresOptions" 
              :required="true"
              :disabled="dialogMode === 'view'" 
              placeholder="Selecione o produtor" 
              optionLabel="nome" 
              optionValue="id"
              :error="errors.produtor_id" 
            />
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
import api from '../../services/api'
import { TextInput, NumberInput, DropdownInput, CustomPagination } from '../../components/forms'

// Interfaces
interface Propriedade {
  id: number
  nome: string
  municipio: string
  uf: string
  inscricao_estadual?: string
  area_total: number
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
  municipio: '',
  uf: '',
  inscricao_estadual: '',
  area_total: '',
  produtor_id: null as number | null
})

const errors = reactive({
  nome: '',
  municipio: '',
  uf: '',
  inscricao_estadual: '',
  area_total: '',
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
    if (error.response?.status !== 404) {
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

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewPropriedade = async (propriedade: Propriedade) => {
  // Garantir que os produtores estejam carregados antes de abrir o modal
  if (produtoresOptions.value.length === 0) {
    await loadProdutores()
  }
  await fillForm(propriedade)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editPropriedade = async (propriedade: Propriedade) => {
  // Garantir que os produtores estejam carregados antes de abrir o modal
  if (produtoresOptions.value.length === 0) {
    await loadProdutores()
  }
  
  // Aguardar o próximo tick para garantir que a reatividade seja processada
  await nextTick()
  
  await fillForm(propriedade)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (propriedade: Propriedade) => {
  form.id = propriedade.id
  form.nome = propriedade.nome
  form.municipio = propriedade.municipio
  form.uf = propriedade.uf
  form.inscricao_estadual = propriedade.inscricao_estadual || ''
  form.area_total = propriedade.area_total?.toString() || ''
  form.produtor_id = propriedade.produtor_id
  
  // Aguardar o próximo tick para garantir que a reatividade seja processada
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.municipio = ''
  form.uf = ''
  form.inscricao_estadual = ''
  form.area_total = ''
  form.produtor_id = null
  clearErrors()
}

const clearErrors = () => {
  errors.nome = ''
  errors.municipio = ''
  errors.uf = ''
  errors.inscricao_estadual = ''
  errors.area_total = ''
  errors.produtor_id = ''
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
      municipio: form.municipio,
      uf: form.uf,
      inscricao_estadual: form.inscricao_estadual,
      area_total: parseFloat(form.area_total),
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
      loadPropriedades()
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
  loadPropriedades()
  loadProdutores()
})
</script>

<style scoped>
.propriedades-container {
  padding: 1rem;
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
</style>
