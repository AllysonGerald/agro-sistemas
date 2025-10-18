<template>
  <div class="unidades-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Unidades de Produção</h2>
          <p class="text-600 mt-0 mb-0">Gerencie as unidades de produção cadastradas no sistema</p>
        </div>
        <div class="header-actions">
          <Button label="Nova Unidade" icon="pi pi-plus" @click="openCreateDialog" :loading="loading" size="large"
            class="nova-unidade-btn" />
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por cultura, propriedade ou município..." @input="debouncedSearch"
              class="search-input" />
          </div>
        </div>
        <div class="acoes-container">
          <Button label="Limpar Filtros" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters"
            :disabled="!searchTerm" class="limpar-btn" />
        </div>
      </div>
    </div>

    <!-- Tabela de Unidades -->
    <div class="card">
      <DataTable :value="unidades" :loading="loading" responsive-layout="scroll" class="p-datatable-sm">
        <Column field="nome_cultura" header="Nome" sortable>
          <template #body="slotProps">
            <strong>{{ slotProps.data.cultura_label || slotProps.data.nome_cultura }}</strong>
          </template>
        </Column>

        <Column field="area_total_ha" header="Área (ha)" sortable>
          <template #body="slotProps">
            <span class="font-mono">{{ formatArea(slotProps.data.area_total_ha) }}</span>
          </template>
        </Column>

        <Column field="coordenadas_geograficas" header="Coordenadas">
          <template #body="slotProps">
            <span class="font-mono text-sm">
              {{ formatCoordenadas(slotProps.data.coordenadas_geograficas) }}
            </span>
          </template>
        </Column>

        <Column field="propriedade.nome" header="Propriedade" sortable>
          <template #body="slotProps">
            <span class="text-blue-600 font-medium">{{ slotProps.data.propriedade?.nome || '-' }}</span>
          </template>
        </Column>

        <Column field="propriedade.municipio" header="Município" sortable>
          <template #body="slotProps">
            <span class="text-gray-600">{{ slotProps.data.propriedade?.municipio || '-' }}</span>
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
              <Button icon="pi pi-eye" severity="info" outlined size="small" @click="viewUnidade(slotProps.data)"
                v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" outlined size="small" @click="editUnidade(slotProps.data)"
                v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" outlined size="small" @click="confirmDelete(slotProps.data)"
                v-tooltip="'Excluir'" />
            </div>
          </template>
        </Column>

        <template #empty>
          <div class="text-center p-4">
            <i class="pi pi-seedling text-4xl text-400 mb-3"></i>
            <p class="text-600 text-lg">Nenhuma unidade encontrada</p>
            <p class="text-500">
              {{ searchTerm ? 'Tente ajustar os filtros de busca' : 'Clique em "Nova Unidade" para começar' }}
            </p>
          </div>
        </template>
      </DataTable>

      <!-- Paginação Customizada -->
      <CustomPagination :current-page="pagination.current" :total-pages="pagination.totalPages"
        :total="pagination.total" :per-page="pagination.perPage" @page-change="onPageChange"
        @per-page-change="onPerPageChange" />
    </div>

    <!-- Modal Moderno de Unidade de Produção -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 600px;">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="fas fa-seedling"></i>
            </div>
            {{ dialogMode === 'create' ? 'Nova Unidade de Produção' : dialogMode === 'edit' ? 'Editar Unidade de Produção' : 'Visualizar Unidade de Produção' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <form @submit.prevent="saveUnidade" class="modal-form">
            <!-- Nome -->
            <SearchableDropdownInput id="nome_cultura" label="Nome" v-model="form.nome_cultura" :options="culturaOptions"
              placeholder="Selecione o nome da cultura" :required="true" :disabled="dialogMode === 'view'"
              :error="errors.nome_cultura" optionLabel="label" optionValue="value" 
              filterPlaceholder="Buscar cultura..." />

            <!-- Área Total -->
            <NumberInput id="area_total_ha" label="Área Total (hectares)" v-model="form.area_total_ha"
              placeholder="Ex: 10.50" :required="true" :disabled="dialogMode === 'view'" :error="errors.area_total_ha"
              size="small" :step="0.1" />

            <!-- Coordenadas Geográficas -->
            <div class="form-group">
              <label class="form-label">Coordenadas Geográficas</label>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <NumberInput id="latitude" label="Latitude" v-model="form.latitude" placeholder="Ex: -23.550520"
                  :disabled="dialogMode === 'view'" :error="errors.latitude" size="small" :step="0.000001" />
                <NumberInput id="longitude" label="Longitude" v-model="form.longitude" placeholder="Ex: -46.633308"
                  :disabled="dialogMode === 'view'" :error="errors.longitude" size="small" :step="0.000001" />
              </div>
            </div>

            <!-- Propriedade -->
            <DropdownInput id="propriedade_id" label="Propriedade" v-model="form.propriedade_id"
              :options="propriedadesOptions" placeholder="Selecione a propriedade" :required="true"
              :disabled="dialogMode === 'view'" :error="errors.propriedade_id" optionLabel="nome" optionValue="id" />
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveUnidade"
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
              Tem certeza que deseja excluir a unidade de produção <strong>{{ unidadeToDelete?.nome_cultura }}</strong>?
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
          <button type="button" class="btn btn-danger" @click="deleteUnidade" :class="{ 'btn-loading': deleteLoading }"
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
import { NumberInput, DropdownInput, SearchableDropdownInput, CustomPagination } from '../../components/forms'

// Interfaces
interface UnidadeProducao {
  id: number
  nome_cultura: string
  area_total_ha: number
  coordenadas_geograficas?: string
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
const unidades = ref<UnidadeProducao[]>([])
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
const unidadeToDelete = ref<UnidadeProducao | null>(null)

// Form data
const form = reactive({
  id: null as number | null,
  nome_cultura: '',
  area_total_ha: '',
  latitude: '',
  longitude: '',
  propriedade_id: null as number | null
})

const errors = reactive({
  nome_cultura: '',
  area_total_ha: '',
  latitude: '',
  longitude: '',
  propriedade_id: ''
})

// Opções para dropdowns
const propriedadesOptions = ref<Propriedade[]>([])

const culturaOptions = [
  { value: 'laranja_pera', label: 'Laranja Pera' },
  { value: 'laranja_lima', label: 'Laranja Lima' },
  { value: 'limao', label: 'Limão' },
  { value: 'caju', label: 'Caju' },
  { value: 'manga', label: 'Manga' },
  { value: 'coco', label: 'Coco' },
  { value: 'mamao', label: 'Mamão' },
  { value: 'banana', label: 'Banana' },
  { value: 'abacaxi', label: 'Abacaxi' },
  { value: 'maracuja', label: 'Maracujá' },
  { value: 'acerola', label: 'Acerola' },
  { value: 'graviola', label: 'Graviola' },
  { value: 'jaca', label: 'Jaca' },
  { value: 'abacate', label: 'Abacate' },
  { value: 'melancia_crimson_sweet', label: 'Melancia Crimson Sweet' },
  { value: 'melancia', label: 'Melancia' },
  { value: 'melao', label: 'Melão' },
  { value: 'goiaba_paluma', label: 'Goiaba Paluma' },
  { value: 'goiaba', label: 'Goiaba' },
  { value: 'siriguela', label: 'Siriguela' },
  { value: 'pitanga', label: 'Pitanga' },
  { value: 'umbu', label: 'Umbu' },
  { value: 'caja', label: 'Cajá' },
  { value: 'milho', label: 'Milho' },
  { value: 'feijao_caupi', label: 'Feijão Caupi' },
  { value: 'feijao_comum', label: 'Feijão Comum' },
  { value: 'soja', label: 'Soja' },
  { value: 'arroz', label: 'Arroz' },
  { value: 'trigo', label: 'Trigo' },
  { value: 'sorgo', label: 'Sorgo' },
  { value: 'girassol', label: 'Girassol' },
  { value: 'gergelim', label: 'Gergelim' },
  { value: 'cana_de_acucar', label: 'Cana-de-açúcar' },
  { value: 'cafe', label: 'Café' },
  { value: 'mandioca', label: 'Mandioca' },
  { value: 'batata_doce', label: 'Batata Doce' },
  { value: 'inhame', label: 'Inhame' },
  { value: 'batata_inglesa', label: 'Batata Inglesa' },
  { value: 'tomate', label: 'Tomate' },
  { value: 'cebola', label: 'Cebola' },
  { value: 'cenoura', label: 'Cenoura' },
  { value: 'alface', label: 'Alface' },
  { value: 'repolho', label: 'Repolho' },
  { value: 'pimentao', label: 'Pimentão' },
  { value: 'pepino', label: 'Pepino' },
  { value: 'quiabo', label: 'Quiabo' },
  { value: 'berinjela', label: 'Berinjela' },
  { value: 'abobora', label: 'Abóbora' },
  { value: 'capim_braquiaria', label: 'Capim Braquiária' },
  { value: 'capim_tanzania', label: 'Capim Tanzânia' },
  { value: 'capim_elefante', label: 'Capim Elefante' },
  { value: 'alfafa', label: 'Alfafa' },
  { value: 'palma_forrageira', label: 'Palma Forrageira' },
  { value: 'leucena', label: 'Leucena' },
  { value: 'algodao', label: 'Algodão' },
  { value: 'sisal', label: 'Sisal' },
  { value: 'hortela', label: 'Hortelã' },
  { value: 'manjericao', label: 'Manjericão' },
  { value: 'capim_santo', label: 'Capim Santo' },
  { value: 'camomila', label: 'Camomila' }
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
  loadUnidades()
}, 500)

// Methods
const loadUnidades = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value && searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    const response = await api.get('/v1/unidades-producao', { params })

    if (response.data.success) {
      unidades.value = Array.isArray(response.data.data.data) ? response.data.data.data : []

      // Extrair metadados de paginação
      if (response.data.data) {
        pagination.total = response.data.data.total || 0
        pagination.totalPages = response.data.data.last_page || 1
      }

    } else {
      throw new Error(response.data.message || 'Erro ao carregar unidades')
    }
  } catch (error: any) {
    if (error.response?.status !== 404) {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao carregar unidades',
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
  loadUnidades()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1 // Reset to first page when changing per page
  loadUnidades()
}

const clearFilters = () => {
  searchTerm.value = ''
  pagination.current = 1
  loadUnidades()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewUnidade = async (unidade: UnidadeProducao) => {
  // Garantir que as propriedades estejam carregadas antes de abrir o modal
  if (propriedadesOptions.value.length === 0) {
    await loadPropriedades()
  }
  
  // Aguardar o próximo tick para garantir que a reatividade seja processada
  await nextTick()
  
  await fillForm(unidade)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editUnidade = async (unidade: UnidadeProducao) => {
  // Garantir que as propriedades estejam carregadas antes de abrir o modal
  if (propriedadesOptions.value.length === 0) {
    await loadPropriedades()
  }
  
  // Aguardar o próximo tick para garantir que a reatividade seja processada
  await nextTick()
  
  await fillForm(unidade)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (unidade: UnidadeProducao) => {
  form.id = unidade.id
  form.nome_cultura = unidade.nome_cultura
  form.area_total_ha = unidade.area_total_ha?.toString() || ''

  // Extrair latitude e longitude das coordenadas
  if (unidade.coordenadas_geograficas) {
    const coords = typeof unidade.coordenadas_geograficas === 'string'
      ? JSON.parse(unidade.coordenadas_geograficas)
      : unidade.coordenadas_geograficas
    form.latitude = coords?.lat?.toString() || ''
    form.longitude = coords?.lng?.toString() || ''
  } else {
    form.latitude = ''
    form.longitude = ''
  }

  form.propriedade_id = unidade.propriedade_id
  
  // Aguardar o próximo tick para garantir que a reatividade seja processada
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.nome_cultura = ''
  form.area_total_ha = ''
  form.latitude = ''
  form.longitude = ''
  form.propriedade_id = null
  clearErrors()
}

const clearErrors = () => {
  errors.nome_cultura = ''
  errors.area_total_ha = ''
  errors.latitude = ''
  errors.longitude = ''
  errors.propriedade_id = ''
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.nome_cultura.trim()) {
    errors.nome_cultura = 'Nome da cultura é obrigatório'
    isValid = false
  }

  if (!form.area_total_ha || parseFloat(form.area_total_ha) <= 0) {
    errors.area_total_ha = 'Área total deve ser maior que zero'
    isValid = false
  }

  if (!form.propriedade_id) {
    errors.propriedade_id = 'Propriedade é obrigatória'
    isValid = false
  }

  return isValid
}

const saveUnidade = async () => {
  if (!validateForm()) return

  try {
    dialogLoading.value = true

    const payload = {
      nome_cultura: form.nome_cultura,
      area_total_ha: parseFloat(form.area_total_ha),
      coordenadas_geograficas: form.latitude && form.longitude ? {
        lat: parseFloat(form.latitude),
        lng: parseFloat(form.longitude)
      } : null,
      propriedade_id: form.propriedade_id
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/unidades-producao', payload)
    } else {
      response = await api.put(`/v1/unidades-producao/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Unidade ${dialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadUnidades()
    } else {
      throw new Error(response.data.message || 'Erro ao salvar unidade')
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
        detail: error.response?.data?.message || 'Erro ao salvar unidade',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (unidade: UnidadeProducao) => {
  unidadeToDelete.value = unidade
  showConfirmDialog.value = true
}

const deleteUnidade = async () => {
  if (!unidadeToDelete.value) return

  try {
    deleteLoading.value = true

    const response = await api.delete(`/v1/unidades-producao/${unidadeToDelete.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Unidade excluída com sucesso',
        life: 3000
      })

      showConfirmDialog.value = false
      loadUnidades()
    } else {
      throw new Error(response.data.message || 'Erro ao excluir unidade')
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao excluir unidade',
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

const formatCoordenadas = (coordenadas: any) => {
  if (!coordenadas) return '-'

  try {
    const coords = typeof coordenadas === 'string' ? JSON.parse(coordenadas) : coordenadas
    if (coords?.lat && coords?.lng) {
      return `${coords.lat.toFixed(6)}, ${coords.lng.toFixed(6)}`
    }
  } catch (e) {
    // Se não conseguir fazer parse, retorna como string
    return coordenadas
  }

  return '-'
}

const formatDate = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

// Lifecycle
onMounted(() => {
  loadUnidades()
  loadPropriedades()
})
</script>

<style scoped>
.unidades-container {
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

.nova-unidade-btn {
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
