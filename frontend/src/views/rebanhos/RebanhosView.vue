<template>
  <div class="rebanhos-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Rebanhos</h2>
          <p class="text-600 mt-0 mb-0">Gerencie os rebanhos cadastrados no sistema</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Rebanho" icon="pi pi-plus" @click="openCreateDialog" :loading="loading" size="large"
            class="novo-rebanho-btn" />
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por espécie, finalidade ou propriedade..."
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="acoes-container">
          <Button label="Limpar Filtros" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters"
            :disabled="!searchTerm" class="limpar-btn" />
        </div>
      </div>
    </div>

    <!-- Tabela de Rebanhos -->
    <div class="card">
      <DataTable :value="rebanhos" :loading="loading" responsive-layout="scroll" class="p-datatable-sm">
        <Column field="especie" header="Espécie" sortable>
          <template #body="slotProps">
            <strong>{{ getEspecieLabel(slotProps.data.especie) }}</strong>
          </template>
        </Column>

        <Column field="quantidade" header="Quantidade" sortable>
          <template #body="slotProps">
            <span class="font-mono font-bold text-blue-600">{{ slotProps.data.quantidade }}</span>
          </template>
        </Column>

        <Column field="finalidade" header="Finalidade" sortable>
          <template #body="slotProps">
            <span class="px-2 py-1 rounded-full text-xs font-medium"
              :class="getFinalidadeClass(slotProps.data.finalidade)">
              {{ getFinalidadeLabel(slotProps.data.finalidade) }}
            </span>
          </template>
        </Column>

        <Column field="data_atualizacao" header="Última Atualização" sortable>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.data_atualizacao) }}
          </template>
        </Column>

        <Column field="propriedade.nome" header="Propriedade" sortable>
          <template #body="slotProps">
            <span class="text-green-600 font-medium">{{ slotProps.data.propriedade?.nome || '-' }}</span>
          </template>
        </Column>

        <Column field="propriedade.municipio" header="Município" sortable>
          <template #body="slotProps">
            <span class="text-gray-600">{{ slotProps.data.propriedade?.municipio || '-' }}</span>
          </template>
        </Column>

        <Column header="Ações" :exportable="false" style="min-width: 10rem">
          <template #body="slotProps">
            <div class="flex gap-4 justify-content-start">
              <Button icon="pi pi-eye" severity="info" outlined size="small" @click="viewRebanho(slotProps.data)"
                v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" outlined size="small" @click="editRebanho(slotProps.data)"
                v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" outlined size="small" @click="confirmDelete(slotProps.data)"
                v-tooltip="'Excluir'" />
            </div>
          </template>
        </Column>

        <template #empty>
          <div class="text-center p-4">
            <i class="pi pi-paw text-4xl text-400 mb-3"></i>
            <p class="text-600 text-lg">Nenhum rebanho encontrado</p>
            <p class="text-500">
              {{ searchTerm ? 'Tente ajustar os filtros de busca' : 'Clique em "Novo Rebanho" para começar' }}
            </p>
          </div>
        </template>
      </DataTable>

      <!-- Paginação Customizada -->
      <CustomPagination :current-page="pagination.current" :total-pages="pagination.totalPages"
        :total="pagination.total" :per-page="pagination.perPage" @page-change="onPageChange"
        @per-page-change="onPerPageChange" />
    </div>

    <!-- Modal Moderno de Rebanho -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 600px;">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="fas fa-cow"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Rebanho' : dialogMode === 'edit' ? 'Editar Rebanho' : 'Visualizar Rebanho' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <form @submit.prevent="saveRebanho" class="modal-form">
            <!-- Espécie -->
            <DropdownInput id="especie" label="Espécie" v-model="form.especie" :options="especieOptions"
              placeholder="Selecione a espécie" :required="true" :disabled="dialogMode === 'view'"
              :error="errors.especie" optionLabel="label" optionValue="value" />

            <!-- Quantidade -->
            <NumberInput id="quantidade" label="Quantidade" v-model="form.quantidade" placeholder="Ex: 100"
              :required="true" :disabled="dialogMode === 'view'" :error="errors.quantidade" size="small" :step="1" />

            <!-- Propriedade -->
            <DropdownInput id="propriedade_id" label="Propriedade" v-model="form.propriedade_id"
              :options="propriedadesOptions" placeholder="Selecione a propriedade" :required="true"
              :disabled="dialogMode === 'view'" :error="errors.propriedade_id" optionLabel="nome" optionValue="id" />

            <!-- Finalidade e Data -->
            <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <DropdownInput id="finalidade" label="Finalidade" v-model="form.finalidade" :options="finalidadeOptions"
                placeholder="Selecione a finalidade" :required="true" :disabled="dialogMode === 'view'"
                :error="errors.finalidade" optionLabel="label" optionValue="value" />

              <CalendarInput id="data_atualizacao" label="Data de Atualização" v-model="form.data_atualizacao"
                placeholder="Selecione a data" :required="true" :disabled="dialogMode === 'view'"
                :error="errors.data_atualizacao" dateFormat="dd/mm/yy" />
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveRebanho"
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
              Tem certeza que deseja excluir o rebanho de <strong>{{ rebanhoToDelete?.especie }}</strong>?
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
          <button type="button" class="btn btn-danger" @click="deleteRebanho" :class="{ 'btn-loading': deleteLoading }"
            :disabled="deleteLoading">
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
import { NumberInput, DropdownInput, CalendarInput, CustomPagination } from '../../components/forms'

// Interfaces
interface Rebanho {
  id: number
  especie: string
  quantidade: number
  finalidade: string
  data_atualizacao: string
  propriedade_id: number
  propriedade?: {
    id: number
    nome: string
    municipio: string
  }
  created_at: string
  updated_at: string
}

interface Propriedade {
  id: number
  nome: string
  municipio: string
  uf: string
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
const rebanhos = ref<Rebanho[]>([])
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
const rebanhoToDelete = ref<Rebanho | null>(null)

// Form data
const form = reactive({
  id: null as number | null,
  especie: '',
  quantidade: '',
  finalidade: '',
  data_atualizacao: null as Date | null,
  propriedade_id: null as number | null
})

const errors = reactive({
  especie: '',
  quantidade: '',
  finalidade: '',
  data_atualizacao: '',
  propriedade_id: ''
})

// Opções para dropdowns
const especieOptions = [
  { value: 'bovinos', label: 'Bovinos' },
  { value: 'suinos', label: 'Suínos' },
  { value: 'caprinos', label: 'Caprinos' }
]

const finalidadeOptions = [
  { value: 'corte', label: 'Corte' },
  { value: 'leite', label: 'Leite' },
  { value: 'reproducao', label: 'Reprodução' },
  { value: 'misto', label: 'Misto' }
]

const propriedadesOptions = ref<Propriedade[]>([])

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
  loadRebanhos()
}, 500)

// Methods
const loadRebanhos = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value && searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    const response = await api.get('/v1/rebanhos', { params })

    if (response.data.success) {
      rebanhos.value = Array.isArray(response.data.data.data) ? response.data.data.data : []

      // Extrair metadados de paginação
      if (response.data.data) {
        pagination.total = response.data.data.total || 0
        pagination.totalPages = response.data.data.last_page || 1
      }
    } else {
      throw new Error(response.data.message || 'Erro ao carregar rebanhos')
    }
  } catch (error: any) {
    if (error.response?.status !== 404) {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao carregar rebanhos',
        life: 5000
      })
    }
  } finally {
    loading.value = false
  }
}

const loadPropriedades = async () => {
  try {
    const response = await api.get('/v1/propriedades', { params: { per_page: 1000 } })
    if (response.data.success) {
      propriedadesOptions.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Erro ao carregar propriedades:', error)
  }
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadRebanhos()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1 // Reset to first page when changing per page
  loadRebanhos()
}

const clearFilters = () => {
  searchTerm.value = ''
  pagination.current = 1
  loadRebanhos()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewRebanho = async (rebanho: Rebanho) => {
  // Garantir que as propriedades estejam carregadas antes de abrir o modal
  if (propriedadesOptions.value.length === 0) {
    await loadPropriedades()
  }
  fillForm(rebanho)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editRebanho = async (rebanho: Rebanho) => {
  // Garantir que as propriedades estejam carregadas antes de abrir o modal
  if (propriedadesOptions.value.length === 0) {
    await loadPropriedades()
  }
  fillForm(rebanho)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = (rebanho: Rebanho) => {
  form.id = rebanho.id
  form.especie = rebanho.especie
  form.quantidade = rebanho.quantidade.toString()
  form.finalidade = rebanho.finalidade
  form.data_atualizacao = new Date(rebanho.data_atualizacao)
  form.propriedade_id = rebanho.propriedade_id
}

const resetForm = () => {
  form.id = null
  form.especie = ''
  form.quantidade = ''
  form.finalidade = ''
  form.data_atualizacao = null
  form.propriedade_id = null
  clearErrors()
}

const clearErrors = () => {
  errors.especie = ''
  errors.quantidade = ''
  errors.finalidade = ''
  errors.data_atualizacao = ''
  errors.propriedade_id = ''
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.especie) {
    errors.especie = 'Espécie é obrigatória'
    isValid = false
  }

  if (!form.quantidade || parseInt(form.quantidade) <= 0) {
    errors.quantidade = 'Quantidade deve ser maior que zero'
    isValid = false
  }

  if (!form.finalidade) {
    errors.finalidade = 'Finalidade é obrigatória'
    isValid = false
  }

  if (!form.data_atualizacao) {
    errors.data_atualizacao = 'Data de atualização é obrigatória'
    isValid = false
  }

  if (!form.propriedade_id) {
    errors.propriedade_id = 'Propriedade é obrigatória'
    isValid = false
  }

  return isValid
}

const saveRebanho = async () => {
  if (!validateForm()) return

  try {
    dialogLoading.value = true

    const payload = {
      especie: form.especie,
      quantidade: parseInt(form.quantidade),
      finalidade: form.finalidade,
      data_atualizacao: form.data_atualizacao?.toISOString().split('T')[0],
      propriedade_id: form.propriedade_id
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/rebanhos', payload)
    } else {
      response = await api.put(`/v1/rebanhos/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Rebanho ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadRebanhos()
    } else {
      throw new Error(response.data.message || 'Erro ao salvar rebanho')
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
        detail: error.response?.data?.message || 'Erro ao salvar rebanho',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (rebanho: Rebanho) => {
  rebanhoToDelete.value = rebanho
  showConfirmDialog.value = true
}

const deleteRebanho = async () => {
  if (!rebanhoToDelete.value) return

  try {
    deleteLoading.value = true

    const response = await api.delete(`/v1/rebanhos/${rebanhoToDelete.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Rebanho excluído com sucesso',
        life: 3000
      })

      showConfirmDialog.value = false
      loadRebanhos()
    } else {
      throw new Error(response.data.message || 'Erro ao excluir rebanho')
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao excluir rebanho',
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
const getEspecieLabel = (especie: string) => {
  const especieMap: { [key: string]: string } = {
    'bovinos': 'Bovinos',
    'suinos': 'Suínos',
    'caprinos': 'Caprinos'
  }
  return especieMap[especie] || especie
}

const getFinalidadeLabel = (finalidade: string) => {
  const finalidadeMap: { [key: string]: string } = {
    'corte': 'Corte',
    'leite': 'Leite',
    'reproducao': 'Reprodução',
    'misto': 'Misto'
  }
  return finalidadeMap[finalidade] || finalidade
}

const getFinalidadeClass = (finalidade: string) => {
  const classes: { [key: string]: string } = {
    'corte': 'bg-red-100 text-red-800',
    'leite': 'bg-blue-100 text-blue-800',
    'reproducao': 'bg-green-100 text-green-800',
    'misto': 'bg-yellow-100 text-yellow-800'
  }
  return classes[finalidade] || 'bg-gray-100 text-gray-800'
}

const formatDate = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

// Lifecycle
onMounted(() => {
  loadRebanhos()
  loadPropriedades()
})
</script>

<style scoped>
.rebanhos-container {
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

.novo-rebanho-btn {
  min-width: 160px;
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
