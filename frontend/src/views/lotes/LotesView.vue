<template>
  <div class="lotes-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Lotes</h2>
          <p class="text-600 mt-0 mb-0">Gerencie grupos de animais por lotes</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Lote" icon="pi pi-plus" @click="openCreateDialog" size="large" />
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
          <i class="pi pi-box"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.total_lotes || 0 }}</span>
          <span class="stat-label">Total de Lotes</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
          <i class="pi pi-users"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.total_animais || 0 }}</span>
          <span class="stat-label">Animais em Lotes</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
          <i class="pi pi-chart-line"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.media_animais || 0 }}</span>
          <span class="stat-label">Média por Lote</span>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar lotes..." 
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="filtros-dropdown">
          <Select v-model="filtroPropriedade" :options="propriedadesOptions" placeholder="Todas propriedades"
            optionLabel="nome" optionValue="id" @change="loadLotes" class="filtro-select" showClear />
        </div>
        <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters" />
      </div>
    </div>

    <!-- Grid de Lotes -->
    <div class="card">
      <h3 class="text-xl font-semibold mb-4">
        <i class="pi pi-th-large mr-2"></i>
        Lotes Cadastrados
      </h3>

      <div v-if="loading" class="loading-container">
        <ProgressSpinner />
      </div>

      <div v-else-if="lotes.length === 0" class="empty-state">
        <i class="pi pi-inbox text-6xl text-400 mb-4"></i>
        <p class="text-600 text-xl">Nenhum lote encontrado</p>
        <p class="text-500">Clique em "Novo Lote" para começar</p>
      </div>

      <div v-else class="lotes-grid">
        <div v-for="lote in lotes" :key="lote.id" class="lote-card">
          <div class="lote-header">
            <div class="lote-icon">
              <i class="pi pi-box"></i>
            </div>
            <div class="lote-badge">
              {{ lote.quantidade_atual || 0 }} animais
            </div>
          </div>

          <div class="lote-info">
            <h4 class="lote-nome">{{ lote.nome }}</h4>
            <p class="lote-descricao">{{ lote.descricao || 'Sem descrição' }}</p>

            <div class="lote-detalhes">
              <div class="detalhe-item">
                <i class="pi pi-map-marker"></i>
                <span>{{ lote.propriedade?.nome || 'N/A' }}</span>
              </div>
              <div v-if="lote.pasto" class="detalhe-item">
                <i class="pi pi-chart-bar"></i>
                <span>Pasto: {{ lote.pasto.nome }}</span>
              </div>
              <div class="detalhe-item">
                <i class="pi pi-calendar"></i>
                <span>Criado em {{ formatDateBR(lote.created_at) }}</span>
              </div>
            </div>

            <div class="lote-stats">
              <div class="stat-item">
                <span class="stat-label">Peso Médio</span>
                <span class="stat-value-small">{{ lote.peso_medio || 0 }} kg</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Idade Média</span>
                <span class="stat-value-small">{{ lote.idade_media || 0 }} meses</span>
              </div>
            </div>

            <div class="lote-actions">
              <Button icon="pi pi-eye" severity="info" text size="small" 
                @click="viewLote(lote)" v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" text size="small"
                @click="editLote(lote)" v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" text size="small" 
                @click="confirmDelete(lote)" v-tooltip="'Excluir'" />
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

    <!-- Modal de Lote -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 600px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dbeafe; color: #2563eb;">
              <i class="pi pi-box"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Lote' : dialogMode === 'edit' ? 'Editar Lote' : 'Visualizar Lote' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="saveLote" class="modal-form">
            <TextInput 
              id="nome" 
              label="Nome do Lote" 
              v-model="form.nome"
              placeholder="Ex: Lote 001 - Nelore 2025" 
              :required="true"
              :disabled="dialogMode === 'view'" 
              :error="errors.nome" 
            />

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
                id="pasto_id" 
                label="Pasto" 
                v-model="form.pasto_id"
                :options="pastosOptions" 
                :disabled="dialogMode === 'view'"
                :error="errors.pasto_id" 
                optionLabel="nome" 
                optionValue="id" 
              />
            </div>

            <div class="form-group">
              <label class="form-label">Descrição</label>
              <Textarea v-model="form.descricao" :disabled="dialogMode === 'view'" rows="3"
                placeholder="Descreva o lote, finalidade, características..."
                style="width: 100%;" />
            </div>

            <div class="form-group">
              <label class="form-label">Observações</label>
              <Textarea v-model="form.observacoes" :disabled="dialogMode === 'view'" rows="2"
                style="width: 100%;" />
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveLote"
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
          <p class="text-center">Tem certeza que deseja excluir o lote <strong>{{ loteToDelete?.nome }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deleteLote">
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
import { TextInput, DropdownInput, CustomPagination } from '../../components/forms'
import ProgressSpinner from 'primevue/progressspinner'
import Textarea from 'primevue/textarea'

const toast = useToast()

const lotes = ref([])
const loading = ref(false)
const searchTerm = ref('')
const filtroPropriedade = ref(null)

const estatisticas = reactive({
  total_lotes: 0,
  total_animais: 0,
  media_animais: 0,
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
const loteToDelete = ref(null)

const form = reactive({
  id: null as number | null,
  nome: '',
  descricao: '',
  propriedade_id: null as number | null,
  pasto_id: null as number | null,
  observacoes: '',
})

const errors = reactive({
  nome: '',
  descricao: '',
  propriedade_id: '',
  pasto_id: '',
})

const propriedadesOptions = ref([])
const pastosOptions = ref([])

const loadLotes = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value) params.search = searchTerm.value
    if (filtroPropriedade.value) params.propriedade_id = filtroPropriedade.value

    const response = await api.get('/v1/lotes', { params })

    if (response.data.success) {
      lotes.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao carregar lotes', life: 5000 })
  } finally {
    loading.value = false
  }
}

const loadEstatisticas = async () => {
  try {
    const response = await api.get('/v1/lotes/estatisticas')
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

const loadPastos = async () => {
  try {
    const response = await api.get('/v1/pastos', { params: { per_page: 1000 } })
    if (response.data.success) {
      pastosOptions.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erro ao carregar pastos:', error)
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
  loadLotes()
}, 500)

const clearFilters = () => {
  searchTerm.value = ''
  filtroPropriedade.value = null
  pagination.current = 1
  loadLotes()
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadLotes()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1
  loadLotes()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewLote = async (lote: any) => {
  await fillForm(lote)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editLote = async (lote: any) => {
  await fillForm(lote)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (lote: any) => {
  form.id = lote.id
  form.nome = lote.nome
  form.descricao = lote.descricao || ''
  form.propriedade_id = lote.propriedade_id
  form.pasto_id = lote.pasto_id
  form.observacoes = lote.observacoes || ''
  
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.descricao = ''
  form.propriedade_id = null
  form.pasto_id = null
  form.observacoes = ''
  Object.keys(errors).forEach(key => (errors as any)[key] = '')
}

const saveLote = async () => {
  try {
    dialogLoading.value = true

    const payload = {
      nome: form.nome,
      descricao: form.descricao,
      propriedade_id: form.propriedade_id,
      pasto_id: form.pasto_id,
      observacoes: form.observacoes,
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/lotes', payload)
    } else {
      response = await api.put(`/v1/lotes/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Lote ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadLotes()
      loadEstatisticas()
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar lote',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (lote: any) => {
  loteToDelete.value = lote
  showConfirmDialog.value = true
}

const deleteLote = async () => {
  if (!loteToDelete.value) return

  try {
    const response = await api.delete(`/v1/lotes/${(loteToDelete.value as any).id}`)

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Lote excluído', life: 3000 })
      showConfirmDialog.value = false
      loadLotes()
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

const formatDateBR = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

onMounted(() => {
  loadLotes()
  loadEstatisticas()
  loadPropriedades()
  loadPastos()
})
</script>

<style scoped>
.lotes-container {
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
  min-width: 180px;
}

.lotes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.lote-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.lote-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  border-color: #2563eb;
}

.lote-header {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.lote-icon {
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

.lote-badge {
  background: rgba(255, 255, 255, 0.95);
  color: #2563eb;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.lote-info {
  padding: 1.25rem;
}

.lote-nome {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.lote-descricao {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1rem 0;
  line-height: 1.5;
}

.lote-detalhes {
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
  color: #2563eb;
  font-size: 14px;
}

.lote-stats {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.stat-value-small {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.lote-actions {
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

