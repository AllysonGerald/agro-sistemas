<template>
  <div class="manejo-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Manejo e Atividades</h2>
          <p class="text-600 mt-0 mb-0">Registre pesagens, vacinações, tratamentos e outras atividades</p>
        </div>
        <div class="header-actions">
          <Button label="Nova Atividade" icon="pi pi-plus" @click="openCreateDialog" size="large" />
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
      <div class="stat-card" style="border-left-color: #2563eb;">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
          <i class="pi pi-calendar"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.total_mes || 0 }}</span>
          <span class="stat-label">Atividades este mês</span>
        </div>
      </div>

      <div class="stat-card" style="border-left-color: #16a34a;">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
          <i class="pi pi-check-circle"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.vacinacoes || 0 }}</span>
          <span class="stat-label">Vacinações</span>
        </div>
      </div>

      <div class="stat-card" style="border-left-color: #f59e0b;">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
          <i class="pi pi-chart-line"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.pesagens || 0 }}</span>
          <span class="stat-label">Pesagens</span>
        </div>
      </div>

      <div class="stat-card" style="border-left-color: #dc2626;">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
          <i class="pi pi-heart"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.tratamentos || 0 }}</span>
          <span class="stat-label">Tratamentos</span>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar atividades..." 
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="filtros-dropdown">
          <Select v-model="filtroTipo" :options="tipoOptions" placeholder="Todos os tipos" 
            optionLabel="label" optionValue="value" @change="loadManejos" class="filtro-select" showClear />
          <Select v-model="filtroPropriedade" :options="propriedadesOptions" placeholder="Todas propriedades"
            optionLabel="nome" optionValue="id" @change="loadManejos" class="filtro-select" showClear />
        </div>
        <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters" />
      </div>
    </div>

    <!-- Timeline de Atividades -->
    <div class="card">
      <h3 class="text-xl font-semibold mb-4">
        <i class="pi pi-list mr-2"></i>
        Histórico de Atividades
      </h3>

      <div v-if="loading" class="loading-container">
        <ProgressSpinner />
      </div>

      <div v-else-if="manejos.length === 0" class="empty-state">
        <i class="pi pi-inbox text-6xl text-400 mb-4"></i>
        <p class="text-600 text-xl">Nenhuma atividade registrada</p>
        <p class="text-500">Clique em "Nova Atividade" para começar</p>
      </div>

      <div v-else class="timeline-container">
        <div v-for="manejo in manejos" :key="manejo.id" class="timeline-item">
          <div class="timeline-marker" :class="`marker-${manejo.tipo_atividade}`">
            <i :class="getIconeAtividade(manejo.tipo_atividade)"></i>
          </div>
          
          <div class="timeline-content">
            <div class="timeline-header">
              <div>
                <h4 class="timeline-title">{{ getTituloAtividade(manejo.tipo_atividade) }}</h4>
                <p class="timeline-date">{{ formatDateTimeBR(manejo.data_realizacao) }}</p>
              </div>
              <div class="timeline-actions">
                <Button icon="pi pi-eye" severity="info" text size="small" 
                  @click="viewManejo(manejo)" v-tooltip="'Visualizar'" />
                <Button icon="pi pi-pencil" severity="warning" text size="small"
                  @click="editManejo(manejo)" v-tooltip="'Editar'" />
                <Button icon="pi pi-trash" severity="danger" text size="small" 
                  @click="confirmDelete(manejo)" v-tooltip="'Excluir'" />
              </div>
            </div>

            <div class="timeline-details">
              <div v-if="manejo.animal" class="detail-badge">
                <i class="pi pi-tag"></i>
                <span>{{ manejo.animal.identificacao }}</span>
              </div>
              <div v-if="manejo.lote" class="detail-badge">
                <i class="pi pi-box"></i>
                <span>Lote: {{ manejo.lote.nome }}</span>
              </div>
              <div class="detail-badge">
                <i class="pi pi-map-marker"></i>
                <span>{{ manejo.propriedade?.nome || 'N/A' }}</span>
              </div>
            </div>

            <p v-if="manejo.descricao" class="timeline-description">{{ manejo.descricao }}</p>
            
            <div v-if="manejo.detalhes" class="timeline-extra-info">
              <span v-if="manejo.detalhes.peso">Peso: {{ manejo.detalhes.peso }} kg</span>
              <span v-if="manejo.detalhes.vacina">Vacina: {{ manejo.detalhes.vacina }}</span>
              <span v-if="manejo.detalhes.medicamento">Medicamento: {{ manejo.detalhes.medicamento }}</span>
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

    <!-- Modal de Manejo -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 700px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dbeafe; color: #2563eb;">
              <i class="pi pi-calendar-plus"></i>
            </div>
            {{ dialogMode === 'create' ? 'Nova Atividade' : dialogMode === 'edit' ? 'Editar Atividade' : 'Visualizar Atividade' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="saveManejo" class="modal-form">
            <!-- Tipo e Data -->
            <div class="form-row">
              <DropdownInput 
                id="tipo_atividade" 
                label="Tipo de Atividade" 
                v-model="form.tipo_atividade"
                :options="tipoOptions" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.tipo_atividade" 
                optionLabel="label" 
                optionValue="value" 
              />

              <CalendarInput 
                id="data_realizacao" 
                label="Data e Hora" 
                v-model="form.data_realizacao"
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.data_realizacao" 
                dateFormat="dd/mm/yy" 
              />
            </div>

            <!-- Animal ou Lote -->
            <div class="form-row">
              <SearchableDropdownInput 
                id="animal_id" 
                label="Animal" 
                v-model="form.animal_id"
                :options="animaisOptions" 
                :disabled="dialogMode === 'view' || form.lote_id"
                :error="errors.animal_id" 
                optionLabel="identificacao" 
                optionValue="id"
                placeholder="Selecione um animal" 
              />

              <DropdownInput 
                id="lote_id" 
                label="Lote" 
                v-model="form.lote_id"
                :options="lotesOptions" 
                :disabled="dialogMode === 'view' || form.animal_id"
                :error="errors.lote_id" 
                optionLabel="nome" 
                optionValue="id" 
              />
            </div>

            <!-- Propriedade e Responsável -->
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

              <TextInput 
                id="responsavel" 
                label="Responsável" 
                v-model="form.responsavel"
                placeholder="Nome do responsável" 
                :disabled="dialogMode === 'view'"
                :error="errors.responsavel" 
              />
            </div>

            <!-- Descrição -->
            <TextInput 
              id="descricao" 
              label="Descrição" 
              v-model="form.descricao"
              placeholder="Descreva a atividade" 
              :required="true" 
              :disabled="dialogMode === 'view'"
              :error="errors.descricao" 
            />

            <!-- Detalhes específicos baseados no tipo -->
            <div v-if="form.tipo_atividade === 'pesagem'" class="form-row">
              <NumberInput 
                id="peso" 
                label="Peso (kg)" 
                v-model="form.detalhes.peso"
                placeholder="Ex: 450" 
                :disabled="dialogMode === 'view'" 
                :step="0.1" 
              />
            </div>

            <div v-if="form.tipo_atividade === 'vacinacao'" class="form-row">
              <TextInput 
                id="vacina" 
                label="Nome da Vacina" 
                v-model="form.detalhes.vacina"
                placeholder="Ex: Aftosa" 
                :disabled="dialogMode === 'view'" 
              />
              <CalendarInput 
                id="proxima_dose" 
                label="Próxima Dose" 
                v-model="form.detalhes.proxima_dose"
                :disabled="dialogMode === 'view'" 
                dateFormat="dd/mm/yy" 
              />
            </div>

            <div v-if="form.tipo_atividade === 'tratamento'" class="form-row">
              <TextInput 
                id="medicamento" 
                label="Medicamento" 
                v-model="form.detalhes.medicamento"
                placeholder="Ex: Ivermectina" 
                :disabled="dialogMode === 'view'" 
              />
              <TextInput 
                id="dosagem" 
                label="Dosagem" 
                v-model="form.detalhes.dosagem"
                placeholder="Ex: 10ml" 
                :disabled="dialogMode === 'view'" 
              />
            </div>

            <!-- Observações -->
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
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveManejo"
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
          <p class="text-center">Tem certeza que deseja excluir esta atividade?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deleteManejo">
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
import { TextInput, NumberInput, DropdownInput, CalendarInput, SearchableDropdownInput, CustomPagination } from '../../components/forms'
import ProgressSpinner from 'primevue/progressspinner'
import Textarea from 'primevue/textarea'

const toast = useToast()

const manejos = ref([])
const loading = ref(false)
const searchTerm = ref('')
const filtroTipo = ref(null)
const filtroPropriedade = ref(null)

const estatisticas = reactive({
  total_mes: 0,
  vacinacoes: 0,
  pesagens: 0,
  tratamentos: 0,
})

const pagination = reactive({
  current: 1,
  perPage: 10,
  total: 0,
  totalPages: 0
})

const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const manejoToDelete = ref(null)

const form = reactive({
  id: null as number | null,
  tipo_atividade: '',
  data_realizacao: new Date(),
  animal_id: null as number | null,
  lote_id: null as number | null,
  propriedade_id: null as number | null,
  responsavel: '',
  descricao: '',
  observacoes: '',
  detalhes: {
    peso: '',
    vacina: '',
    proxima_dose: null as Date | null,
    medicamento: '',
    dosagem: '',
  }
})

const errors = reactive({
  tipo_atividade: '',
  data_realizacao: '',
  animal_id: '',
  lote_id: '',
  propriedade_id: '',
  responsavel: '',
  descricao: '',
})

const tipoOptions = [
  { label: 'Pesagem', value: 'pesagem' },
  { label: 'Vacinação', value: 'vacinacao' },
  { label: 'Tratamento', value: 'tratamento' },
  { label: 'Reprodução', value: 'reproducao' },
  { label: 'Movimentação', value: 'movimentacao' },
  { label: 'Nutrição', value: 'nutricao' },
  { label: 'Outros', value: 'outros' }
]

const animaisOptions = ref([])
const lotesOptions = ref([])
const propriedadesOptions = ref([])

const loadManejos = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value) params.search = searchTerm.value
    if (filtroTipo.value) params.tipo_atividade = filtroTipo.value
    if (filtroPropriedade.value) params.propriedade_id = filtroPropriedade.value

    const response = await api.get('/v1/manejos', { params })

    if (response.data.success) {
      manejos.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao carregar atividades', life: 5000 })
  } finally {
    loading.value = false
  }
}

const loadEstatisticas = async () => {
  try {
    const response = await api.get('/v1/manejos/estatisticas')
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

const loadAnimais = async () => {
  try {
    const response = await api.get('/v1/animais', { params: { per_page: 1000 } })
    if (response.data.success) {
      animaisOptions.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erro ao carregar animais:', error)
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
  loadManejos()
}, 500)

const clearFilters = () => {
  searchTerm.value = ''
  filtroTipo.value = null
  filtroPropriedade.value = null
  pagination.current = 1
  loadManejos()
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadManejos()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1
  loadManejos()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewManejo = async (manejo: any) => {
  await fillForm(manejo)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editManejo = async (manejo: any) => {
  await fillForm(manejo)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (manejo: any) => {
  form.id = manejo.id
  form.tipo_atividade = manejo.tipo_atividade
  form.data_realizacao = new Date(manejo.data_realizacao)
  form.animal_id = manejo.animal_id
  form.lote_id = manejo.lote_id
  form.propriedade_id = manejo.propriedade_id
  form.responsavel = manejo.responsavel || ''
  form.descricao = manejo.descricao || ''
  form.observacoes = manejo.observacoes || ''
  form.detalhes = manejo.detalhes || { peso: '', vacina: '', proxima_dose: null, medicamento: '', dosagem: '' }
  
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.tipo_atividade = ''
  form.data_realizacao = new Date()
  form.animal_id = null
  form.lote_id = null
  form.propriedade_id = null
  form.responsavel = ''
  form.descricao = ''
  form.observacoes = ''
  form.detalhes = { peso: '', vacina: '', proxima_dose: null, medicamento: '', dosagem: '' }
  Object.keys(errors).forEach(key => (errors as any)[key] = '')
}

const saveManejo = async () => {
  try {
    dialogLoading.value = true

    const payload = {
      tipo_atividade: form.tipo_atividade,
      data_realizacao: form.data_realizacao.toISOString(),
      animal_id: form.animal_id,
      lote_id: form.lote_id,
      propriedade_id: form.propriedade_id,
      responsavel: form.responsavel,
      descricao: form.descricao,
      observacoes: form.observacoes,
      detalhes: form.detalhes,
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/manejos', payload)
    } else {
      response = await api.put(`/v1/manejos/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Atividade ${dialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadManejos()
      loadEstatisticas()
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar atividade',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (manejo: any) => {
  manejoToDelete.value = manejo
  showConfirmDialog.value = true
}

const deleteManejo = async () => {
  if (!manejoToDelete.value) return

  try {
    const response = await api.delete(`/v1/manejos/${(manejoToDelete.value as any).id}`)

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Atividade excluída', life: 3000 })
      showConfirmDialog.value = false
      loadManejos()
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

const getIconeAtividade = (tipo: string) => {
  const icons: any = {
    'pesagem': 'pi pi-chart-line',
    'vacinacao': 'pi pi-check-circle',
    'tratamento': 'pi pi-heart',
    'reproducao': 'pi pi-heart',
    'movimentacao': 'pi pi-arrows-h',
    'nutricao': 'pi pi-shopping-cart',
    'outros': 'pi pi-ellipsis-h'
  }
  return icons[tipo] || 'pi pi-circle'
}

const getTituloAtividade = (tipo: string) => {
  const titulos: any = {
    'pesagem': 'Pesagem',
    'vacinacao': 'Vacinação',
    'tratamento': 'Tratamento',
    'reproducao': 'Reprodução',
    'movimentacao': 'Movimentação',
    'nutricao': 'Nutrição',
    'outros': 'Outros'
  }
  return titulos[tipo] || tipo
}

const formatDateTimeBR = (value: string) => {
  if (!value) return ''
  const date = new Date(value)
  return date.toLocaleString('pt-BR')
}

onMounted(() => {
  loadManejos()
  loadEstatisticas()
  loadPropriedades()
  loadAnimais()
  loadLotes()
})
</script>

<style scoped>
.manejo-container {
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
  border-left: 4px solid;
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

/* Timeline */
.timeline-container {
  position: relative;
  padding-left: 2rem;
}

.timeline-container::before {
  content: '';
  position: absolute;
  left: 18px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
  display: flex;
  gap: 1.5rem;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border: 3px solid;
  z-index: 1;
}

.marker-pesagem {
  border-color: #f59e0b;
  color: #f59e0b;
}

.marker-vacinacao {
  border-color: #16a34a;
  color: #16a34a;
}

.marker-tratamento {
  border-color: #dc2626;
  color: #dc2626;
}

.marker-reproducao {
  border-color: #db2777;
  color: #db2777;
}

.marker-movimentacao, .marker-nutricao, .marker-outros {
  border-color: #2563eb;
  color: #2563eb;
}

.timeline-content {
  flex: 1;
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.25rem;
  border-left: 3px solid #e5e7eb;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.timeline-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.timeline-date {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.timeline-actions {
  display: flex;
  gap: 0.5rem;
}

.timeline-details {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.detail-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 4px 12px;
  background: white;
  border-radius: 20px;
  font-size: 0.875rem;
  color: #475569;
}

.detail-badge i {
  color: #10b981;
}

.timeline-description {
  color: #475569;
  margin: 0.5rem 0 0 0;
  font-size: 0.9rem;
}

.timeline-extra-info {
  display: flex;
  gap: 1rem;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #64748b;
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

  .timeline-container {
    padding-left: 1.5rem;
  }

  .timeline-marker {
    left: -1.5rem;
    width: 32px;
    height: 32px;
  }
}
</style>

