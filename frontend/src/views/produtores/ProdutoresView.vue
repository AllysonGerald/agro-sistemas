<template>
  <div class="produtores-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Produtores Rurais</h2>
          <p class="text-600 mt-0 mb-0">Gerencie os produtores rurais cadastrados no sistema</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Produtor" icon="pi pi-plus" @click="openCreateDialog" :loading="loading" size="large"
            class="novo-produtor-btn" />
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por nome, CPF/CNPJ ou email..." @input="debouncedSearch"
              class="search-input" />
          </div>
        </div>
        <div class="acoes-container">
          <Button label="Limpar Filtros" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters"
            :disabled="!searchTerm" class="limpar-btn" />
        </div>
      </div>
    </div>

    <!-- Tabela de Produtores -->
    <div class="card">
      <DataTable :value="produtores" :loading="loading" responsive-layout="scroll" class="p-datatable-sm">
        <Column field="nome" header="Nome" sortable>
          <template #body="slotProps">
            <strong>{{ slotProps.data.nome }}</strong>
          </template>
        </Column>

        <Column field="cpf_cnpj" header="CPF/CNPJ" sortable>
          <template #body="slotProps">
            <span class="font-mono">{{ formatCpfCnpj(slotProps.data.cpf_cnpj) }}</span>
          </template>
        </Column>

        <Column field="email" header="Email" sortable />

        <Column field="telefone" header="Telefone">
          <template #body="slotProps">
            <span class="font-mono">{{ formatTelefone(slotProps.data.telefone) }}</span>
          </template>
        </Column>

        <Column field="data_cadastro" header="Data Cadastro" sortable>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.data_cadastro) }}
          </template>
        </Column>

        <Column header="Ações" :exportable="false" style="min-width: 10rem">
          <template #body="slotProps">
            <div class="flex gap-4 justify-content-start">
              <Button icon="pi pi-eye" severity="info" outlined size="small" @click="viewProdutor(slotProps.data)"
                v-tooltip="'Visualizar'" />
              <Button icon="pi pi-pencil" severity="warning" outlined size="small" @click="editProdutor(slotProps.data)"
                v-tooltip="'Editar'" />
              <Button icon="pi pi-trash" severity="danger" outlined size="small" @click="confirmDelete(slotProps.data)"
                v-tooltip="'Excluir'" />
            </div>
          </template>
        </Column>

        <template #empty>
          <div class="text-center p-4">
            <i class="pi pi-users text-4xl text-400 mb-3"></i>
            <p class="text-600 text-lg">Nenhum produtor encontrado</p>
            <p class="text-500">
              {{ searchTerm ? 'Tente ajustar os filtros de busca' : 'Clique em "Novo Produtor" para começar' }}
            </p>
          </div>
        </template>
      </DataTable>

      <!-- Paginação Customizada -->
      <CustomPagination :current-page="pagination.current" :total-pages="pagination.totalPages"
        :total="pagination.total" :per-page="pagination.perPage" @page-change="onPageChange"
        @per-page-change="onPerPageChange" />
    </div>

    <!-- Modal Moderno de Produtor -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon">
              <i class="fas fa-user"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Produtor Rural' : dialogMode === 'edit' ? 'Editar Produtor Rural' :
              'Visualizar Produtor Rural' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <!-- Conteúdo -->
        <div class="modal-content">
          <form @submit.prevent="saveProdutor" class="modal-form">
            <!-- Nome Completo -->
            <TextInput id="nome" label="Nome Completo" v-model="form.nome" placeholder="Digite o nome completo"
              :required="true" :disabled="dialogMode === 'view'" :error="errors.nome" />

            <!-- CPF/CNPJ -->
            <TextInput id="cpf_cnpj" label="CPF/CNPJ" v-model="form.cpf_cnpj"
              placeholder="000.000.000-00 ou 00.000.000/0000-00" :required="true" :disabled="dialogMode === 'view'"
              :error="errors.cpf_cnpj" @input="formatCpfCnpj" />

            <!-- Telefone -->
            <TextInput id="telefone" label="Telefone" v-model="form.telefone" type="tel" placeholder="(00) 00000-0000"
              :required="true" :disabled="dialogMode === 'view'" :error="errors.telefone" @input="formatTelefone" />

            <!-- Email -->
            <TextInput id="email" label="Email" v-model="form.email" type="email" placeholder="email@exemplo.com"
              :required="true" :disabled="dialogMode === 'view'" :error="errors.email" />

            <!-- Separador de Endereço -->
            <div class="form-section-divider">
              <div class="divider-line"></div>
              <div class="divider-content">
                <i class="pi pi-map-marker"></i>
                <span>Endereço</span>
              </div>
              <div class="divider-line"></div>
            </div>

            <!-- CEP com hint -->
            <div class="field-with-hint">
              <TextInput id="cep" label="CEP" v-model="form.cep" placeholder="00000-000"
                :required="false" :disabled="dialogMode === 'view'" :error="errors.cep" @blur="buscarCep" />
              <small class="field-hint">
                <i class="pi pi-info-circle"></i>
                Digite o CEP e pressione TAB para preencher automaticamente
              </small>
            </div>

            <!-- Logradouro e Número (lado a lado) -->
            <div class="form-row">
              <div class="form-col-75">
                <TextInput id="logradouro" label="Logradouro" v-model="form.logradouro" 
                  placeholder="Rua, Avenida, Estrada, etc." :required="false" :disabled="dialogMode === 'view'" 
                  :error="errors.logradouro" />
              </div>
              <div class="form-col-25">
                <TextInput id="numero" label="Número" v-model="form.numero" placeholder="Nº"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.numero" />
              </div>
            </div>

            <!-- Complemento e Bairro (lado a lado) -->
            <div class="form-row">
              <div class="form-col-50">
                <TextInput id="complemento" label="Complemento" v-model="form.complemento" 
                  placeholder="Apto, Sala, Bloco, Lote" :required="false" :disabled="dialogMode === 'view'" 
                  :error="errors.complemento" />
              </div>
              <div class="form-col-50">
                <TextInput id="bairro" label="Bairro" v-model="form.bairro" placeholder="Nome do bairro"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.bairro" />
              </div>
            </div>

            <!-- Cidade e Estado (lado a lado) -->
            <div class="form-row">
              <div class="form-col-75">
                <TextInput id="cidade" label="Cidade" v-model="form.cidade" placeholder="Nome da cidade"
                  :required="false" :disabled="dialogMode === 'view'" :error="errors.cidade" />
              </div>
              <div class="form-col-25">
                <DropdownInput id="estado" label="Estado (UF)" v-model="form.estado"
                  :options="estadosBrasil" optionLabel="label" optionValue="value"
                  placeholder="UF" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.estado" />
              </div>
            </div>

            <!-- Separador de Documentos -->
            <div class="form-section-divider">
              <div class="divider-line"></div>
              <div class="divider-content">
                <i class="pi pi-file"></i>
                <span>Documentos e Informações Complementares</span>
              </div>
              <div class="divider-line"></div>
            </div>

            <!-- Tipo de Pessoa e IE (lado a lado) -->
            <div class="form-row">
              <div class="form-col-50">
                <DropdownInput id="tipo_pessoa" label="Tipo de Pessoa" v-model="form.tipo_pessoa"
                  :options="tiposPessoa" optionLabel="label" optionValue="value"
                  placeholder="Selecione o tipo" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.tipo_pessoa" />
              </div>
              <div class="form-col-50">
                <TextInput id="inscricao_estadual" label="Inscrição Estadual (IE)" v-model="form.inscricao_estadual"
                  placeholder="000.000.000.000" :required="false" :disabled="dialogMode === 'view'"
                  :error="errors.inscricao_estadual" />
              </div>
            </div>

            <!-- CAR (Cadastro Ambiental Rural) -->
            <div class="field-with-hint">
              <TextInput id="car" label="CAR (Cadastro Ambiental Rural)" v-model="form.car"
                placeholder="XX-0000000-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" :required="false" :disabled="dialogMode === 'view'"
                :error="errors.car" />
              <small class="field-hint">
                <i class="pi pi-info-circle"></i>
                Cadastro Ambiental Rural obrigatório para propriedades rurais
              </small>
            </div>

            <!-- Observações -->
            <TextareaInput id="observacoes" label="Observações" v-model="form.observacoes"
              placeholder="Informações adicionais sobre o produtor (atividades, certificações, etc.)" :required="false" :disabled="dialogMode === 'view'"
              :error="errors.observacoes" :rows="6" />
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeDialog" :disabled="dialogLoading">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveProdutor"
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
              Tem certeza que deseja excluir o produtor <strong>{{ produtorToDelete?.nome }}</strong>?
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
          <button type="button" class="btn btn-danger" @click="deleteProdutor" :class="{ 'btn-loading': deleteLoading }"
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
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import api from '../../services/api'
import { TextInput, TextareaInput, DropdownInput, CustomPagination } from '../../components/forms'

// Interfaces
interface ProdutorRural {
  id: number
  nome: string
  cpf_cnpj: string
  telefone: string
  email: string
  cep?: string
  logradouro?: string
  numero?: string
  complemento?: string
  bairro?: string
  cidade?: string
  estado?: string
  inscricao_estadual?: string
  car?: string
  tipo_pessoa?: string
  observacoes?: string
  data_cadastro: string
  created_at: string
  updated_at: string
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
const produtores = ref<ProdutorRural[]>([])
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
const produtorToDelete = ref<ProdutorRural | null>(null)

// Opções de dropdown
const tiposPessoa = [
  { label: 'Pessoa Física', value: 'fisica' },
  { label: 'Pessoa Jurídica', value: 'juridica' }
]

const estadosBrasil = [
  { label: 'AC', value: 'AC' }, { label: 'AL', value: 'AL' }, { label: 'AP', value: 'AP' },
  { label: 'AM', value: 'AM' }, { label: 'BA', value: 'BA' }, { label: 'CE', value: 'CE' },
  { label: 'DF', value: 'DF' }, { label: 'ES', value: 'ES' }, { label: 'GO', value: 'GO' },
  { label: 'MA', value: 'MA' }, { label: 'MT', value: 'MT' }, { label: 'MS', value: 'MS' },
  { label: 'MG', value: 'MG' }, { label: 'PA', value: 'PA' }, { label: 'PB', value: 'PB' },
  { label: 'PR', value: 'PR' }, { label: 'PE', value: 'PE' }, { label: 'PI', value: 'PI' },
  { label: 'RJ', value: 'RJ' }, { label: 'RN', value: 'RN' }, { label: 'RS', value: 'RS' },
  { label: 'RO', value: 'RO' }, { label: 'RR', value: 'RR' }, { label: 'SC', value: 'SC' },
  { label: 'SP', value: 'SP' }, { label: 'SE', value: 'SE' }, { label: 'TO', value: 'TO' }
]

// Form data
const form = reactive({
  id: null as number | null,
  nome: '',
  cpf_cnpj: '',
  telefone: '',
  email: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  cidade: '',
  estado: '',
  inscricao_estadual: '',
  car: '',
  tipo_pessoa: '',
  observacoes: ''
})

const errors = reactive({
  nome: '',
  cpf_cnpj: '',
  telefone: '',
  email: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  cidade: '',
  estado: '',
  inscricao_estadual: '',
  car: '',
  tipo_pessoa: '',
  observacoes: ''
})

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
  loadProdutores()
}, 300)

// Methods
const loadProdutores = async () => {
  try {
    console.log('loadProdutores chamado - página solicitada:', pagination.current)
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value && searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    console.log('Parâmetros enviados:', params)
    const response = await api.get('/v1/produtores-rurais', { params })

    if (response.data.success) {
      console.log('Resposta da API:', {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total,
        data_count: response.data.data.data?.length
      })

      produtores.value = Array.isArray(response.data.data.data) ? response.data.data.data : []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
      // Não sobrescrever pagination.current - manter o valor que foi definido pelo usuário

      console.log('Estado da paginação após atualização:', pagination)
    } else {
      throw new Error(response.data.message || 'Erro ao carregar produtores')
    }
  } catch (error: any) {
    console.error('Erro ao carregar produtores:', error)
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || error.message || 'Erro ao carregar produtores',
      life: 5000
    })
  } finally {
    loading.value = false
  }
}

const onPageChange = (page: number) => {
  console.log('Tentando mudar para página:', page, 'Página atual:', pagination.current)
  pagination.current = page
  loadProdutores()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1 // Reset to first page when changing per page
  loadProdutores()
}

const clearFilters = () => {
  searchTerm.value = ''
  pagination.current = 1
  loadProdutores()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewProdutor = (produtor: ProdutorRural) => {
  fillForm(produtor)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editProdutor = (produtor: ProdutorRural) => {
  fillForm(produtor)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = (produtor: ProdutorRural) => {
  form.id = produtor.id
  form.nome = produtor.nome
  form.cpf_cnpj = produtor.cpf_cnpj
  form.telefone = produtor.telefone
  form.email = produtor.email
  form.cep = produtor.cep || ''
  form.logradouro = produtor.logradouro || ''
  form.numero = produtor.numero || ''
  form.complemento = produtor.complemento || ''
  form.bairro = produtor.bairro || ''
  form.cidade = produtor.cidade || ''
  form.estado = produtor.estado || ''
  form.inscricao_estadual = produtor.inscricao_estadual || ''
  form.car = produtor.car || ''
  form.tipo_pessoa = produtor.tipo_pessoa || ''
  form.observacoes = produtor.observacoes || ''
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.cpf_cnpj = ''
  form.telefone = ''
  form.email = ''
  form.cep = ''
  form.logradouro = ''
  form.numero = ''
  form.complemento = ''
  form.bairro = ''
  form.cidade = ''
  form.estado = ''
  form.inscricao_estadual = ''
  form.car = ''
  form.tipo_pessoa = ''
  form.observacoes = ''
  clearErrors()
}

const clearErrors = () => {
  errors.nome = ''
  errors.cpf_cnpj = ''
  errors.telefone = ''
  errors.email = ''
  errors.cep = ''
  errors.logradouro = ''
  errors.numero = ''
  errors.complemento = ''
  errors.bairro = ''
  errors.cidade = ''
  errors.estado = ''
  errors.inscricao_estadual = ''
  errors.car = ''
  errors.tipo_pessoa = ''
  errors.observacoes = ''
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.nome.trim()) {
    errors.nome = 'Nome é obrigatório'
    isValid = false
  }

  if (!form.cpf_cnpj.trim()) {
    errors.cpf_cnpj = 'CPF/CNPJ é obrigatório'
    isValid = false
  }

  if (!form.telefone.trim()) {
    errors.telefone = 'Telefone é obrigatório'
    isValid = false
  }

  if (!form.email.trim()) {
    errors.email = 'Email é obrigatório'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Email deve ser válido'
    isValid = false
  }

  return isValid
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
      form.cidade = data.localidade || ''
      form.estado = data.uf || ''
      
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

const saveProdutor = async () => {
  if (!validateForm()) return

  try {
    dialogLoading.value = true

    const payload = {
      nome: form.nome,
      cpf_cnpj: form.cpf_cnpj,
      telefone: form.telefone,
      email: form.email,
      cep: form.cep,
      logradouro: form.logradouro,
      numero: form.numero,
      complemento: form.complemento,
      bairro: form.bairro,
      cidade: form.cidade,
      estado: form.estado,
      inscricao_estadual: form.inscricao_estadual,
      car: form.car,
      tipo_pessoa: form.tipo_pessoa,
      observacoes: form.observacoes
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/produtores-rurais', payload)
    } else {
      response = await api.put(`/v1/produtores-rurais/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Produtor ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadProdutores()
    } else {
      throw new Error(response.data.message || 'Erro ao salvar produtor')
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
        detail: error.response?.data?.message || 'Erro ao salvar produtor',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (produtor: ProdutorRural) => {
  produtorToDelete.value = produtor
  showConfirmDialog.value = true
}

const deleteProdutor = async () => {
  if (!produtorToDelete.value) return

  try {
    deleteLoading.value = true

    const response = await api.delete(`/v1/produtores-rurais/${produtorToDelete.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Produtor excluído com sucesso',
        life: 3000
      })

      showConfirmDialog.value = false
      loadProdutores()
    } else {
      throw new Error(response.data.message || 'Erro ao excluir produtor')
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao excluir produtor',
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
const formatCpfCnpj = (value: string) => {
  if (!value) return ''
  const numbers = value.replace(/\D/g, '')
  if (numbers.length === 11) {
    return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  } else if (numbers.length === 14) {
    return numbers.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
  return value
}

const formatTelefone = (value: string) => {
  if (!value) return ''
  const numbers = value.replace(/\D/g, '')
  if (numbers.length === 11) {
    return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  } else if (numbers.length === 10) {
    return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  }
  return value
}

const formatDate = (value: string) => {
  if (!value) return ''
  return new Date(value).toLocaleDateString('pt-BR')
}

// Lifecycle
onMounted(() => {
  loadProdutores()
})
</script>

<style scoped>
.produtores-container {
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

.novo-produtor-btn {
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

@media (max-width: 1023px) {
  .produtores-container {
    padding: 0.75rem;
    min-width: 1024px;
  }

  .card {
    padding: 1rem;
    margin-bottom: 0.75rem;
    min-width: 1024px;
  }

  .header-container {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-actions {
    width: 100%;
  }

  .novo-produtor-btn {
    width: 100%;
  }

  .filtros-container {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    min-width: 1024px;
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

  .p-datatable {
    min-width: 1024px;
  }

  .p-datatable .p-datatable-tbody>tr>td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }

  .p-datatable .p-datatable-thead>tr>th {
    padding: 0.5rem;
    font-size: 0.875rem;
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