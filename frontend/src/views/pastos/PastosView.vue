<template>
  <div class="pastos-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Pastos</h2>
          <p class="text-600 mt-0 mb-0">Gerencie pastos e áreas de pastagem</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Pasto" icon="pi pi-plus" @click="openCreateDialog" size="large" />
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.total_pastos || 0 }}</span>
          <span class="stat-label">Total de Pastos</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
          <i class="pi pi-map"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.area_total || 0 }} ha</span>
          <span class="stat-label">Área Total</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
          <i class="pi pi-sun"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.pastos_ocupados || 0 }}</span>
          <span class="stat-label">Pastos Ocupados</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #f3e8ff; color: #9333ea;">
          <i class="pi pi-moon"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.pastos_descanso || 0 }}</span>
          <span class="stat-label">Em Descanso</span>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar pastos..." 
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="filtros-dropdown">
          <Select v-model="filtroSituacao" :options="situacaoOptions" placeholder="Todas as situações" 
            optionLabel="label" optionValue="value" @change="loadPastos" class="filtro-select" showClear />
          <Select v-model="filtroPropriedade" :options="propriedadesOptions" placeholder="Todas propriedades"
            optionLabel="nome" optionValue="id" @change="loadPastos" class="filtro-select" showClear />
        </div>
        <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters" />
      </div>
    </div>

    <!-- Grid de Pastos -->
    <div class="card">
      <h3 class="text-xl font-semibold mb-4">
        <i class="pi pi-th-large mr-2"></i>
        Pastos Cadastrados
      </h3>

      <div v-if="loading" class="loading-container">
        <ProgressSpinner />
      </div>

      <div v-else-if="pastos.length === 0" class="empty-state">
        <i class="pi pi-inbox text-6xl text-400 mb-4"></i>
        <p class="text-600 text-xl">Nenhum pasto encontrado</p>
        <p class="text-500">Clique em "Novo Pasto" para começar</p>
      </div>

      <div v-else class="pastos-grid">
        <div v-for="pasto in pastos" :key="pasto.id" class="pasto-card">
          <div class="pasto-header" :class="`header-${pasto.situacao}`">
            <div class="pasto-icon">
              <i class="pi pi-map"></i>
            </div>
            <div class="pasto-badge" :class="`badge-${pasto.situacao}`">
              {{ getSituacaoLabel(pasto.situacao) }}
            </div>
          </div>

          <div class="pasto-info">
            <h4 class="pasto-nome">{{ pasto.nome }}</h4>
            <p class="pasto-tipo">{{ pasto.tipo_pasto || 'Não especificado' }}</p>

            <div class="pasto-detalhes">
              <div class="detalhe-item">
                <i class="pi pi-map"></i>
                <span>{{ pasto.area_hectares }} ha</span>
              </div>
              <div class="detalhe-item">
                <i class="pi pi-map-marker"></i>
                <span>{{ pasto.propriedade?.nome || 'N/A' }}</span>
              </div>
              <div v-if="pasto.lote_atual" class="detalhe-item">
                <i class="pi pi-box"></i>
                <span>Lote: {{ pasto.lote_atual.nome }}</span>
              </div>
            </div>

            <div v-if="pasto.situacao === 'ocupado' && pasto.capacidade_animais" class="pasto-capacidade">
              <div class="capacidade-bar">
                <div class="capacidade-fill" :style="{ width: getCapacidadePercent(pasto) + '%' }"></div>
              </div>
              <span class="capacidade-label">
                {{ pasto.quantidade_animais || 0 }} / {{ pasto.capacidade_animais }} animais
              </span>
            </div>

            <div v-if="pasto.data_ultimo_descanso && pasto.situacao === 'descanso'" class="pasto-descanso-info">
              <i class="pi pi-calendar"></i>
              <span>Em descanso desde {{ formatDateBR(pasto.data_ultimo_descanso) }}</span>
            </div>

            <div class="pasto-actions">
              <Button icon="pi pi-eye" severity="info" text size="small" 
                @click="viewPasto(pasto)" v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" text size="small"
                @click="editPasto(pasto)" v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" text size="small" 
                @click="confirmDelete(pasto)" v-tooltip="'Excluir'" />
            </div>
          </div>
        </div>
      </div>

      <CustomPagination 
        :current-page="pagination.current" 
        :total-pages="pagination.totalPages"
        :total="pagination.total" 
        :per-page="pagination.perPage" 
        @page-change="onPageChange"
        @per-page-change="onPerPageChange" 
      />
    </div>

    <!-- Modal de Pasto -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 650px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="pi pi-map"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Pasto' : dialogMode === 'edit' ? 'Editar Pasto' : 'Visualizar Pasto' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="savePasto" class="modal-form">
            <div class="form-row">
              <TextInput 
                id="nome" 
                label="Nome do Pasto" 
                v-model="form.nome"
                placeholder="Ex: Pasto A" 
                :required="true"
                :disabled="dialogMode === 'view'" 
                :error="errors.nome" 
              />

              <TextInput 
                id="tipo_pasto" 
                label="Tipo de Pasto" 
                v-model="form.tipo_pasto"
                placeholder="Ex: Brachiaria, Tanzânia" 
                :disabled="dialogMode === 'view'"
                :error="errors.tipo_pasto" 
              />
            </div>

            <div class="form-row">
              <NumberInput 
                id="area_hectares" 
                label="Área (hectares)" 
                v-model="form.area_hectares"
                placeholder="Ex: 10.5" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.area_hectares" 
                :step="0.1" 
              />

              <NumberInput 
                id="capacidade_animais" 
                label="Capacidade (animais)" 
                v-model="form.capacidade_animais"
                placeholder="Ex: 50" 
                :disabled="dialogMode === 'view'"
                :error="errors.capacidade_animais" 
                :step="1" 
              />
            </div>

            <div class="form-row">
              <DropdownInput 
                id="propriedade_id" 
                label="Propriedade" 
                v-model="form.propriedade_id"
                :options="propriedadesOptions" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.propriedade_id" 
                optionLabel="nome" 
                optionValue="id" 
              />

              <DropdownInput 
                id="situacao" 
                label="Situação" 
                v-model="form.situacao"
                :options="situacaoOptions" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.situacao" 
                optionLabel="label" 
                optionValue="value" 
              />
            </div>

            <div v-if="form.situacao === 'ocupado'" class="form-row">
              <DropdownInput 
                id="lote_atual_id" 
                label="Lote Atual" 
                v-model="form.lote_atual_id"
                :options="lotesOptions" 
                :disabled="dialogMode === 'view'"
                :error="errors.lote_atual_id" 
                optionLabel="nome" 
                optionValue="id" 
              />
            </div>

            <div v-if="form.situacao === 'descanso'" class="form-row">
              <CalendarInput 
                id="data_ultimo_descanso" 
                label="Data Início Descanso" 
                v-model="form.data_ultimo_descanso"
                :disabled="dialogMode === 'view'" 
                :error="errors.data_ultimo_descanso"
                dateFormat="dd/mm/yy" 
              />
            </div>

            <div class="form-group">
              <label class="form-label">Observações</label>
              <Textarea v-model="form.observacoes" :disabled="dialogMode === 'view'" rows="3"
                style="width: 100%;" />
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="savePasto"
            :class="{ 'btn-loading': dialogLoading }" :disabled="dialogLoading">
            <i :class="dialogMode === 'create' ? 'pi pi-plus' : 'pi pi-save'"></i>
            {{ dialogMode === 'create' ? 'Criar' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <div v-if="showConfirmDialog" class="modal-overlay" @click.self="showConfirmDialog = false">
      <div class="modal-container" style="max-width: 450px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #fee2e2; color: #dc2626;">
              <i class="pi pi-exclamation-triangle"></i>
            </div>
            Confirmar Exclusão
          </h2>
          <button class="modal-close" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
          </button>
        </div>
        <div class="modal-content">
          <p class="text-center">Tem certeza que deseja excluir o pasto <strong>{{ pastoToDelete?.nome }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deletePasto">
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
import { TextInput, NumberInput, DropdownInput, CalendarInput, CustomPagination } from '../../components/forms'
import ProgressSpinner from 'primevue/progressspinner'
import Textarea from 'primevue/textarea'

const toast = useToast()

const pastos = ref([])
const loading = ref(false)
const searchTerm = ref('')
const filtroSituacao = ref(null)
const filtroPropriedade = ref(null)

const estatisticas = reactive({
  total_pastos: 0,
  area_total: 0,
  pastos_ocupados: 0,
  pastos_descanso: 0,
})

const pagination = reactive({
  current: 1,
  perPage: 12,
  total: 0,
  totalPages: 0
})

const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const pastoToDelete = ref(null)

const form = reactive({
  id: null as number | null,
  nome: '',
  tipo_pasto: '',
  area_hectares: '',
  capacidade_animais: '',
  situacao: 'disponivel',
  propriedade_id: null as number | null,
  lote_atual_id: null as number | null,
  data_ultimo_descanso: null as Date | null,
  observacoes: '',
})

const errors = reactive({
  nome: '',
  tipo_pasto: '',
  area_hectares: '',
  capacidade_animais: '',
  situacao: '',
  propriedade_id: '',
  lote_atual_id: '',
  data_ultimo_descanso: '',
})

const situacaoOptions = [
  { label: 'Disponível', value: 'disponivel' },
  { label: 'Ocupado', value: 'ocupado' },
  { label: 'Descanso', value: 'descanso' },
  { label: 'Manutenção', value: 'manutencao' }
]

const propriedadesOptions = ref([])
const lotesOptions = ref([])

const loadPastos = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value) params.search = searchTerm.value
    if (filtroSituacao.value) params.situacao = filtroSituacao.value
    if (filtroPropriedade.value) params.propriedade_id = filtroPropriedade.value

    const response = await api.get('/v1/pastos', { params })

    if (response.data.success) {
      pastos.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao carregar pastos', life: 5000 })
  } finally {
    loading.value = false
  }
}

const loadEstatisticas = async () => {
  try {
    const response = await api.get('/v1/pastos/estatisticas')
    if (response.data.success) {
      Object.assign(estatisticas, response.data.data)
    }
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error)
  }
}

const loadPropriedades = async () => {
  try {
    const response = await api.get('/v1/propriedades', { params: { per_page: 1000 } })
    if (response.data.success) {
      propriedadesOptions.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erro ao carregar propriedades:', error)
  }
}

const loadLotes = async () => {
  try {
    const response = await api.get('/v1/lotes', { params: { per_page: 1000 } })
    if (response.data.success) {
      lotesOptions.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erro ao carregar lotes:', error)
  }
}

const createDebounce = (func: Function, delay: number) => {
  let timeout: ReturnType<typeof setTimeout>
  return (...args: any[]) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(null, args), delay)
  }
}

const debouncedSearch = createDebounce(() => {
  pagination.current = 1
  loadPastos()
}, 500)

const clearFilters = () => {
  searchTerm.value = ''
  filtroSituacao.value = null
  filtroPropriedade.value = null
  pagination.current = 1
  loadPastos()
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadPastos()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1
  loadPastos()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewPasto = async (pasto: any) => {
  await fillForm(pasto)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editPasto = async (pasto: any) => {
  await fillForm(pasto)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (pasto: any) => {
  form.id = pasto.id
  form.nome = pasto.nome
  form.tipo_pasto = pasto.tipo_pasto || ''
  form.area_hectares = pasto.area_hectares.toString()
  form.capacidade_animais = pasto.capacidade_animais?.toString() || ''
  form.situacao = pasto.situacao
  form.propriedade_id = pasto.propriedade_id
  form.lote_atual_id = pasto.lote_atual_id
  form.data_ultimo_descanso = pasto.data_ultimo_descanso ? new Date(pasto.data_ultimo_descanso) : null
  form.observacoes = pasto.observacoes || ''
  
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.tipo_pasto = ''
  form.area_hectares = ''
  form.capacidade_animais = ''
  form.situacao = 'disponivel'
  form.propriedade_id = null
  form.lote_atual_id = null
  form.data_ultimo_descanso = null
  form.observacoes = ''
  Object.keys(errors).forEach(key => (errors as any)[key] = '')
}

const savePasto = async () => {
  try {
    dialogLoading.value = true

    const payload = {
      nome: form.nome,
      tipo_pasto: form.tipo_pasto,
      area_hectares: parseFloat(form.area_hectares),
      capacidade_animais: form.capacidade_animais ? parseInt(form.capacidade_animais) : null,
      situacao: form.situacao,
      propriedade_id: form.propriedade_id,
      lote_atual_id: form.lote_atual_id,
      data_ultimo_descanso: form.data_ultimo_descanso ? form.data_ultimo_descanso.toISOString().split('T')[0] : null,
      observacoes: form.observacoes,
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/pastos', payload)
    } else {
      response = await api.put(`/v1/pastos/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Pasto ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadPastos()
      loadEstatisticas()
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar pasto',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (pasto: any) => {
  pastoToDelete.value = pasto
  showConfirmDialog.value = true
}

const deletePasto = async () => {
  if (!pastoToDelete.value) return

  try {
    const response = await api.delete(`/v1/pastos/${(pastoToDelete.value as any).id}`)

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Pasto excluído', life: 3000 })
      showConfirmDialog.value = false
      loadPastos()
      loadEstatisticas()
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao excluir', life: 5000 })
  }
}

const closeDialog = () => {
  showDialog.value = false
  resetForm()
}

const getSituacaoLabel = (situacao: string) => {
  const labels: any = {
    'disponivel': 'Disponível',
    'ocupado': 'Ocupado',
    'descanso': 'Descanso',
    'manutencao': 'Manutenção'
  }
  return labels[situacao] || situacao
}

const getCapacidadePercent = (pasto: any) => {
  if (!pasto.capacidade_animais) return 0
  const percent = (pasto.quantidade_animais / pasto.capacidade_animais) * 100
  return Math.min(percent, 100)
}

const formatDateBR = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

onMounted(() => {
  loadPastos()
  loadEstatisticas()
  loadPropriedades()
  loadLotes()
})
</script>

<style scoped>
.pastos-container {
  padding: 1rem;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1rem;
  padding: 1.5rem;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
}

.filtros-container {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.busca-container {
  flex: 1;
  min-width: 280px;
}

.search-input-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #10b981;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 40px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
}

.filtros-dropdown {
  display: flex;
  gap: 0.75rem;
}

.filtro-select {
  min-width: 160px;
}

.pastos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.pasto-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.pasto-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  border-color: #16a34a;
}

.pasto-header {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.header-disponivel {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.header-ocupado {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.header-descanso {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.header-manutencao {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.pasto-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

.pasto-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.95);
}

.badge-disponivel {
  color: #16a34a;
}

.badge-ocupado {
  color: #f59e0b;
}

.badge-descanso {
  color: #8b5cf6;
}

.badge-manutencao {
  color: #6b7280;
}

.pasto-info {
  padding: 1.25rem;
}

.pasto-nome {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.pasto-tipo {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1rem 0;
}

.pasto-detalhes {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.detalhe-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #475569;
  font-size: 0.875rem;
}

.detalhe-item i {
  color: #16a34a;
  font-size: 14px;
}

.pasto-capacidade {
  margin-bottom: 1rem;
}

.capacidade-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.capacidade-fill {
  height: 100%;
  background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
  transition: width 0.3s ease;
}

.capacidade-label {
  font-size: 0.875rem;
  color: #64748b;
}

.pasto-descanso-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: #f3e8ff;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #7c3aed;
}

.pasto-actions {
  display: flex;
  justify-content: space-around;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.loading-container,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>

