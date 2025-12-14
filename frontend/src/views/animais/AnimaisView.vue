<template>
  <div class="animais-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Rebanho</h2>
          <p class="text-600 mt-0 mb-0">Gerencie seu plantel completo</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Animal" icon="pi pi-plus" @click="openCreateDialog" :loading="loading" size="large"
            class="novo-animal-btn" />
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
          <i class="pi pi-chart-bar"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.total }}</span>
          <span class="stat-label">Total de animais</span>
          <span class="stat-change">+12% este mês</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
          <i class="pi pi-users"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.machos }}</span>
          <span class="stat-label">Machos</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #fce7f3; color: #db2777;">
          <i class="pi pi-users"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.femeas }}</span>
          <span class="stat-label">Fêmeas</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
          <i class="pi pi-chart-line"></i>
        </div>
        <div class="stat-content">
          <span class="stat-value">{{ estatisticas.peso_medio }} kg</span>
          <span class="stat-label">Peso Médio</span>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por nome ou identificação..."
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="filtros-dropdown">
          <Select v-model="filtroSexo" :options="sexoOptions" placeholder="Todos os Sexos" 
            optionLabel="label" optionValue="value" @change="loadAnimais" class="filtro-select" />
          <Select v-model="filtroSituacao" :options="situacaoOptions" placeholder="Todas as Raças" 
            optionLabel="label" optionValue="value" @change="loadAnimais" class="filtro-select" />
        </div>
        <div class="acoes-container">
          <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined 
            @click="clearFilters" class="limpar-btn" />
        </div>
      </div>
    </div>

    <!-- Grid de Cards (Plantel) -->
    <div class="card">
      <div class="plantel-header">
        <h3 class="text-xl font-semibold m-0">
          <i class="pi pi-th-large mr-2"></i>
          Plantel
        </h3>
        <span class="plantel-count">{{ animais.length }} animal(is) cadastrado(s)</span>
      </div>

      <div v-if="loading" class="loading-container">
        <ProgressSpinner />
      </div>

      <div v-else-if="animais.length === 0" class="empty-state">
        <i class="pi pi-inbox text-6xl text-400 mb-4"></i>
        <p class="text-600 text-xl">Nenhum animal encontrado</p>
        <p class="text-500">
          {{ searchTerm ? 'Tente ajustar os filtros de busca' : 'Clique em "Novo Animal" para começar' }}
        </p>
      </div>

      <div v-else class="animais-grid">
        <div v-for="animal in animais" :key="animal.id" class="animal-card">
          <!-- Foto do Animal -->
          <div class="animal-foto-container">
            <img v-if="animal.foto_url" :src="getImageUrl(animal.foto_url)" :alt="animal.identificacao" 
              class="animal-foto" />
            <div v-else class="animal-foto-placeholder">
              <i class="pi pi-image text-4xl text-400"></i>
            </div>
            
            <!-- Badge de Situação -->
            <div class="animal-badge" :class="`badge-${animal.situacao}`">
              {{ getSituacaoLabel(animal.situacao) }}
            </div>

            <!-- Badge de Sexo -->
            <div class="animal-sexo-badge" :class="`sexo-${animal.sexo}`">
              <i :class="animal.sexo === 'macho' ? 'pi pi-mars' : 'pi pi-venus'"></i>
            </div>
          </div>

          <!-- Informações do Animal -->
          <div class="animal-info">
            <h4 class="animal-nome">{{ animal.identificacao }}</h4>
            <p class="animal-numero">{{ animal.nome_numero || '-' }}</p>

            <div class="animal-detalhes">
              <div class="detalhe-item">
                <i class="pi pi-tag"></i>
                <span>{{ animal.raca || 'Nelore' }}</span>
              </div>
              <div class="detalhe-item">
                <i class="pi pi-calendar"></i>
                <span>{{ animal.idade_meses || 0 }} meses</span>
              </div>
              <div class="detalhe-item">
                <i class="pi pi-chart-line"></i>
                <span>{{ animal.peso_atual || 0 }} kg</span>
              </div>
              <div v-if="animal.lote" class="detalhe-item">
                <i class="pi pi-box"></i>
                <span>{{ animal.lote.nome }}</span>
              </div>
            </div>

            <div class="animal-actions">
              <Button icon="pi pi-eye" severity="info" text size="small" 
                @click="viewAnimal(animal)" v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" text size="small" 
                @click="editAnimal(animal)" v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" text size="small" 
                @click="confirmDelete(animal)" v-tooltip="'Excluir'" />
            </div>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <CustomPagination 
        :current-page="pagination.current" 
        :total-pages="pagination.totalPages"
        :total="pagination.total" 
        :per-page="pagination.perPage" 
        @page-change="onPageChange"
        @per-page-change="onPerPageChange" 
      />
    </div>

    <!-- Modal de Animal -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 700px;">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
              <i class="pi pi-github"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Animal' : dialogMode === 'edit' ? 'Editar Animal' : 'Visualizar Animal' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <form @submit.prevent="saveAnimal" class="modal-form">
            <!-- Foto -->
            <div class="form-group" v-if="dialogMode !== 'view'">
              <label class="form-label">Foto do Animal</label>
              <div class="foto-upload-container">
                <div class="foto-preview" v-if="fotoPreview || form.foto_url">
                  <img :src="fotoPreview || getImageUrl(form.foto_url)" alt="Preview" />
                </div>
                <div class="foto-upload-button">
                  <input type="file" @change="onFotoSelect" accept="image/*" id="foto-input" hidden />
                  <Button type="button" icon="pi pi-camera" label="Escolher Foto" 
                    @click="$refs.fotoInput.click()" outlined />
                </div>
              </div>
            </div>

            <!-- Identificação -->
            <div class="form-row">
              <TextInput 
                id="identificacao" 
                label="Identificação" 
                v-model="form.identificacao"
                placeholder="Ex: MT-8999" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.identificacao" 
              />
              
              <TextInput 
                id="nome_numero" 
                label="Nome/Número" 
                v-model="form.nome_numero"
                placeholder="Ex: 999" 
                :disabled="dialogMode === 'view'"
                :error="errors.nome_numero" 
              />
            </div>

            <!-- Sexo e Raça -->
            <div class="form-row">
              <DropdownInput 
                id="sexo" 
                label="Sexo" 
                v-model="form.sexo" 
                :options="sexoOptions"
                :required="true" 
                :disabled="dialogMode === 'view'" 
                :error="errors.sexo" 
                optionLabel="label"
                optionValue="value" 
              />

              <TextInput 
                id="raca" 
                label="Raça" 
                v-model="form.raca"
                placeholder="Ex: Nelore, Angus" 
                :disabled="dialogMode === 'view'"
                :error="errors.raca" 
              />
            </div>

            <!-- Categoria e Situação -->
            <div class="form-row">
              <DropdownInput 
                id="categoria_atual" 
                label="Categoria" 
                v-model="form.categoria_atual"
                :options="categoriaOptions" 
                :disabled="dialogMode === 'view'" 
                :error="errors.categoria_atual"
                optionLabel="label" 
                optionValue="value" 
              />

              <DropdownInput 
                id="situacao" 
                label="Situação" 
                v-model="form.situacao"
                :options="situacaoOptions" 
                :disabled="dialogMode === 'view'" 
                :error="errors.situacao"
                optionLabel="label" 
                optionValue="value" 
              />
            </div>

            <!-- Pesos -->
            <div class="form-row">
              <NumberInput 
                id="peso_entrada" 
                label="Peso Entrada (kg)" 
                v-model="form.peso_entrada"
                placeholder="Ex: 180" 
                :disabled="dialogMode === 'view'" 
                :error="errors.peso_entrada"
                :step="0.1" 
              />

              <NumberInput 
                id="peso_atual" 
                label="Peso Atual (kg)" 
                v-model="form.peso_atual"
                placeholder="Ex: 190" 
                :disabled="dialogMode === 'view'" 
                :error="errors.peso_atual"
                :step="0.1" 
              />
            </div>

            <!-- Datas -->
            <div class="form-row">
              <CalendarInput 
                id="data_nascimento" 
                label="Data de Nascimento"
                v-model="form.data_nascimento" 
                :disabled="dialogMode === 'view'"
                :error="errors.data_nascimento" 
                dateFormat="dd/mm/yy" 
              />

              <CalendarInput 
                id="data_entrada" 
                label="Data de Entrada"
                v-model="form.data_entrada" 
                :disabled="dialogMode === 'view'"
                :error="errors.data_entrada" 
                dateFormat="dd/mm/yy" 
              />
            </div>

            <!-- Propriedade e Lote -->
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
                id="lote_id" 
                label="Lote" 
                v-model="form.lote_id"
                :options="lotesOptions" 
                :disabled="dialogMode === 'view'"
                :error="errors.lote_id" 
                optionLabel="nome" 
                optionValue="id" 
              />
            </div>

            <!-- Observações -->
            <div class="form-group">
              <label class="form-label">Observações</label>
              <Textarea 
                v-model="form.observacoes" 
                :disabled="dialogMode === 'view'" 
                rows="3"
                style="width: 100%;" 
              />
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveAnimal"
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
          <p class="text-center">
            Tem certeza que deseja excluir o animal <strong>{{ animalToDelete?.identificacao }}</strong>?
          </p>
          <p class="text-center text-sm text-500">Esta ação não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false" :disabled="deleteLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deleteAnimal"
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
import { TextInput, NumberInput, DropdownInput, CalendarInput, CustomPagination } from '../../components/forms'
import ProgressSpinner from 'primevue/progressspinner'
import Textarea from 'primevue/textarea'

// Interfaces
interface Animal {
  id: number
  identificacao: string
  nome_numero?: string
  foto_url?: string
  sexo: string
  raca?: string
  categoria_atual?: string
  situacao: string
  data_nascimento?: string
  data_entrada?: string
  idade_meses?: number
  peso_entrada?: number
  peso_atual?: number
  propriedade_id: number
  lote_id?: number
  lote?: { id: number; nome: string }
  propriedade?: { id: number; nome: string }
}

// Composables
const toast = useToast()

// Estado
const animais = ref<Animal[]>([])
const loading = ref(false)
const searchTerm = ref('')
const filtroSexo = ref(null)
const filtroSituacao = ref(null)

const estatisticas = reactive({
  total: 0,
  machos: 0,
  femeas: 0,
  peso_medio: 0,
})

const pagination = reactive({
  current: 1,
  perPage: 12,
  total: 0,
  totalPages: 0
})

// Dialog states
const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const deleteLoading = ref(false)
const animalToDelete = ref<Animal | null>(null)

// Form
const form = reactive({
  id: null as number | null,
  identificacao: '',
  nome_numero: '',
  foto_url: '',
  sexo: 'macho',
  raca: '',
  categoria_atual: '',
  situacao: 'ativo',
  data_nascimento: null as Date | null,
  data_entrada: null as Date | null,
  peso_entrada: '',
  peso_atual: '',
  propriedade_id: null as number | null,
  lote_id: null as number | null,
  observacoes: '',
})

const errors = reactive({
  identificacao: '',
  nome_numero: '',
  sexo: '',
  raca: '',
  categoria_atual: '',
  situacao: '',
  data_nascimento: '',
  data_entrada: '',
  peso_entrada: '',
  peso_atual: '',
  propriedade_id: '',
  lote_id: '',
})

const fotoPreview = ref<string | null>(null)
const fotoFile = ref<File | null>(null)

// Opções
const sexoOptions = [
  { label: 'Macho', value: 'macho' },
  { label: 'Fêmea', value: 'femea' }
]

const categoriaOptions = [
  { label: 'Bezerro', value: 'bezerro' },
  { label: 'Bezerra', value: 'bezerra' },
  { label: 'Novilho', value: 'novilho' },
  { label: 'Novilha', value: 'novilha' },
  { label: 'Boi', value: 'boi' },
  { label: 'Vaca', value: 'vaca' },
  { label: 'Touro', value: 'touro' }
]

const situacaoOptions = [
  { label: 'Ativo', value: 'ativo' },
  { label: 'Vendido', value: 'vendido' },
  { label: 'Morto', value: 'morto' },
  { label: 'Transferido', value: 'transferido' }
]

const propriedadesOptions = ref([])
const lotesOptions = ref([])

// Methods
const loadAnimais = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value && searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    if (filtroSexo.value) {
      params.sexo = filtroSexo.value
    }

    if (filtroSituacao.value) {
      params.situacao = filtroSituacao.value
    }

    const response = await api.get('/v1/animais', { params })

    if (response.data.success) {
      animais.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao carregar animais',
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

const loadEstatisticas = async () => {
  try {
    const response = await api.get('/v1/animais/estatisticas/geral')
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
  loadAnimais()
}, 500)

const clearFilters = () => {
  searchTerm.value = ''
  filtroSexo.value = null
  filtroSituacao.value = null
  pagination.current = 1
  loadAnimais()
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadAnimais()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1
  loadAnimais()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewAnimal = async (animal: Animal) => {
  await fillForm(animal)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editAnimal = async (animal: Animal) => {
  if (propriedadesOptions.value.length === 0) await loadPropriedades()
  if (lotesOptions.value.length === 0) await loadLotes()
  await nextTick()
  await fillForm(animal)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (animal: Animal) => {
  form.id = animal.id
  form.identificacao = animal.identificacao
  form.nome_numero = animal.nome_numero || ''
  form.foto_url = animal.foto_url || ''
  form.sexo = animal.sexo
  form.raca = animal.raca || ''
  form.categoria_atual = animal.categoria_atual || ''
  form.situacao = animal.situacao
  form.data_nascimento = animal.data_nascimento ? new Date(animal.data_nascimento) : null
  form.data_entrada = animal.data_entrada ? new Date(animal.data_entrada) : null
  form.peso_entrada = animal.peso_entrada?.toString() || ''
  form.peso_atual = animal.peso_atual?.toString() || ''
  form.propriedade_id = animal.propriedade_id
  form.lote_id = animal.lote_id || null
  form.observacoes = ''
  
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.identificacao = ''
  form.nome_numero = ''
  form.foto_url = ''
  form.sexo = 'macho'
  form.raca = ''
  form.categoria_atual = ''
  form.situacao = 'ativo'
  form.data_nascimento = null
  form.data_entrada = null
  form.peso_entrada = ''
  form.peso_atual = ''
  form.propriedade_id = null
  form.lote_id = null
  form.observacoes = ''
  fotoPreview.value = null
  fotoFile.value = null
  clearErrors()
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    (errors as any)[key] = ''
  })
}

const onFotoSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    fotoFile.value = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      fotoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(target.files[0])
  }
}

const saveAnimal = async () => {
  clearErrors()
  
  try {
    dialogLoading.value = true

    const payload: any = {
      identificacao: form.identificacao,
      nome_numero: form.nome_numero,
      sexo: form.sexo,
      raca: form.raca,
      categoria_atual: form.categoria_atual,
      situacao: form.situacao,
      data_nascimento: form.data_nascimento ? formatDate(form.data_nascimento) : null,
      data_entrada: form.data_entrada ? formatDate(form.data_entrada) : null,
      peso_entrada: form.peso_entrada ? parseFloat(form.peso_entrada) : null,
      peso_atual: form.peso_atual ? parseFloat(form.peso_atual) : null,
      propriedade_id: form.propriedade_id,
      lote_id: form.lote_id,
      observacoes: form.observacoes,
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/animais', payload)
    } else {
      response = await api.put(`/v1/animais/${form.id}`, payload)
    }

    if (response.data.success) {
      // Upload foto se houver
      if (fotoFile.value && response.data.data.id) {
        const formData = new FormData()
        formData.append('foto', fotoFile.value)
        await api.post(`/v1/animais/${response.data.data.id}/upload-foto`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
      }

      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Animal ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadAnimais()
      loadEstatisticas()
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar animal',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (animal: Animal) => {
  animalToDelete.value = animal
  showConfirmDialog.value = true
}

const deleteAnimal = async () => {
  if (!animalToDelete.value) return

  try {
    deleteLoading.value = true

    const response = await api.delete(`/v1/animais/${animalToDelete.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Animal excluído com sucesso',
        life: 3000
      })

      showConfirmDialog.value = false
      loadAnimais()
      loadEstatisticas()
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao excluir animal',
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

const getImageUrl = (url: string) => {
  if (!url) return '/placeholder-animal.jpg'
  if (url.startsWith('http')) return url
  return `${import.meta.env.VITE_API_URL}/storage/${url}`
}

const getSituacaoLabel = (situacao: string) => {
  const map: any = {
    'ativo': 'Ativo',
    'vendido': 'Vendido',
    'morto': 'Morto',
    'transferido': 'Transferido'
  }
  return map[situacao] || situacao
}

const formatDate = (date: Date) => {
  return date.toISOString().split('T')[0]
}

// Lifecycle
onMounted(() => {
  loadAnimais()
  loadEstatisticas()
  loadPropriedades()
  loadLotes()
})
</script>

<style scoped>
.animais-container {
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
}

.header-content {
  flex: 1;
}

.novo-animal-btn {
  min-width: 160px;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
}

/* Estatísticas */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
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
  margin-top: 0.25rem;
}

.stat-change {
  font-size: 0.75rem;
  color: #16a34a;
  margin-top: 0.25rem;
}

/* Filtros */
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
  font-size: 16px;
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

/* Grid de Animais */
.plantel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.plantel-count {
  color: #64748b;
  font-size: 0.875rem;
}

.animais-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.animal-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.animal-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  border-color: #10b981;
}

.animal-foto-container {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: #f3f4f6;
}

.animal-foto {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.animal-foto-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
}

.animal-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
}

.badge-ativo {
  color: #16a34a;
}

.badge-vendido {
  color: #2563eb;
}

.animal-sexo-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: bold;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
}

.sexo-macho {
  color: #2563eb;
}

.sexo-femea {
  color: #db2777;
}

.animal-info {
  padding: 1.25rem;
}

.animal-nome {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.animal-numero {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1rem 0;
}

.animal-detalhes {
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
  color: #10b981;
  font-size: 14px;
}

.animal-actions {
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

/* Foto upload */
.foto-upload-container {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.foto-preview {
  width: 120px;
  height: 120px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
}

.foto-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Form rows */
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .filtros-dropdown {
    flex-direction: column;
    width: 100%;
  }

  .filtro-select {
    width: 100%;
  }
}
</style>

