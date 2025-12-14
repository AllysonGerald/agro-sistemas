<template>
  <div class="estoque-container">
    <!-- Header -->
    <div class="card">
      <div class="header-container">
        <div class="header-content">
          <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Estoque</h2>
          <p class="text-600 mt-0 mb-0">Controle de rações, medicamentos e insumos</p>
        </div>
        <div class="header-actions">
          <Button label="Novo Item" icon="pi pi-plus" @click="openCreateDialog" size="large" />
        </div>
      </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="dashboard-grid">
      <div class="dashboard-card">
        <div class="card-icon" style="background: #dbeafe; color: #2563eb;">
          <i class="pi pi-shopping-cart"></i>
        </div>
        <div class="card-content">
          <span class="card-label">Total de Itens</span>
          <span class="card-value">{{ estatisticas.total_itens || 0 }}</span>
        </div>
      </div>

      <div class="dashboard-card alert-card">
        <div class="card-icon" style="background: #fee2e2; color: #dc2626;">
          <i class="pi pi-exclamation-triangle"></i>
        </div>
        <div class="card-content">
          <span class="card-label">Estoque Baixo</span>
          <span class="card-value text-danger">{{ estatisticas.baixo_estoque || 0 }}</span>
          <span class="card-info">Requer atenção!</span>
        </div>
      </div>

      <div class="dashboard-card">
        <div class="card-icon" style="background: #dcfce7; color: #16a34a;">
          <i class="pi pi-check-circle"></i>
        </div>
        <div class="card-content">
          <span class="card-label">Estoque Adequado</span>
          <span class="card-value">{{ estatisticas.estoque_adequado || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="card">
      <div class="filtros-container">
        <div class="busca-container">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <InputText v-model="searchTerm" placeholder="Buscar por nome..." 
              @input="debouncedSearch" class="search-input" />
          </div>
        </div>
        <div class="filtros-dropdown">
          <Select v-model="filtroTipo" :options="tipoOptions" placeholder="Todos os tipos" 
            optionLabel="label" optionValue="value" @change="loadEstoque" class="filtro-select" showClear />
          <Select v-model="filtroPropriedade" :options="propriedadesOptions" placeholder="Todas propriedades"
            optionLabel="nome" optionValue="id" @change="loadEstoque" class="filtro-select" showClear />
        </div>
        <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters" />
      </div>
    </div>

    <!-- Tabela de Estoque -->
    <div class="card">
      <h3 class="text-xl font-semibold mb-4">
        <i class="pi pi-list mr-2"></i>
        Itens em Estoque
      </h3>

      <div v-if="loading" class="loading-container">
        <ProgressSpinner />
      </div>

      <div v-else-if="estoque.length === 0" class="empty-state">
        <i class="pi pi-inbox text-6xl text-400 mb-4"></i>
        <p class="text-600 text-xl">Nenhum item em estoque</p>
        <p class="text-500">Clique em "Novo Item" para começar</p>
      </div>

      <div v-else>
        <DataTable :value="estoque" responsive-layout="scroll" class="p-datatable-sm">
          <Column field="nome" header="Nome" sortable>
            <template #body="slotProps">
              <div class="item-name-cell">
                <i :class="getTipoIcon(slotProps.data.tipo)" class="mr-2"></i>
                <strong>{{ slotProps.data.nome }}</strong>
              </div>
            </template>
          </Column>

          <Column field="tipo" header="Tipo" sortable>
            <template #body="slotProps">
              <span class="badge-tipo" :class="`tipo-${slotProps.data.tipo}`">
                {{ getTipoLabel(slotProps.data.tipo) }}
              </span>
            </template>
          </Column>

          <Column field="quantidade_atual" header="Quantidade" sortable>
            <template #body="slotProps">
              <div class="quantidade-cell">
                <span :class="getQuantidadeClass(slotProps.data)">
                  {{ slotProps.data.quantidade_atual }}
                </span>
                <span class="unidade">{{ slotProps.data.unidade }}</span>
              </div>
            </template>
          </Column>

          <Column field="quantidade_minima" header="Mínimo" sortable>
            <template #body="slotProps">
              {{ slotProps.data.quantidade_minima }} {{ slotProps.data.unidade }}
            </template>
          </Column>

          <Column field="propriedade.nome" header="Propriedade" sortable />

          <Column field="localizacao" header="Localização" sortable />

          <Column field="valor_unitario" header="Valor Un." sortable>
            <template #body="slotProps">
              R$ {{ formatCurrency(slotProps.data.valor_unitario) }}
            </template>
          </Column>

          <Column header="Status" sortable>
            <template #body="slotProps">
              <span class="status-badge" :class="getStatusClass(slotProps.data)">
                {{ getStatusLabel(slotProps.data) }}
              </span>
            </template>
          </Column>

          <Column header="Ações" :exportable="false" style="min-width: 10rem">
            <template #body="slotProps">
              <div class="flex gap-2">
                <Button icon="pi pi-eye" severity="info" outlined size="small" 
                  @click="viewItem(slotProps.data)" v-tooltip="'Visualizar'" />
                <Button icon="pi pi-pencil" severity="warning" outlined size="small"
                  @click="editItem(slotProps.data)" v-tooltip="'Editar'" />
                <Button icon="pi pi-trash" severity="danger" outlined size="small" 
                  @click="confirmDelete(slotProps.data)" v-tooltip="'Excluir'" />
              </div>
            </template>
          </Column>
        </DataTable>

        <CustomPagination 
          :current-page="pagination.current" 
          :total-pages="pagination.totalPages"
          :total="pagination.total" 
          :per-page="pagination.perPage" 
          @page-change="onPageChange"
          @per-page-change="onPerPageChange" 
        />
      </div>
    </div>

    <!-- Modal de Item -->
    <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
      <div class="modal-container" style="max-width: 650px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <div class="modal-title-icon" style="background: #dbeafe; color: #2563eb;">
              <i class="pi pi-shopping-cart"></i>
            </div>
            {{ dialogMode === 'create' ? 'Novo Item' : dialogMode === 'edit' ? 'Editar Item' : 'Visualizar Item' }}
          </h2>
          <button class="modal-close" @click="closeDialog">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="saveItem" class="modal-form">
            <TextInput 
              id="nome" 
              label="Nome do Item" 
              v-model="form.nome"
              placeholder="Ex: Ração Premium Engorda" 
              :required="true"
              :disabled="dialogMode === 'view'" 
              :error="errors.nome" 
            />

            <div class="form-row">
              <DropdownInput 
                id="tipo" 
                label="Tipo" 
                v-model="form.tipo"
                :options="tipoOptions" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.tipo" 
                optionLabel="label" 
                optionValue="value" 
              />

              <TextInput 
                id="unidade" 
                label="Unidade" 
                v-model="form.unidade"
                placeholder="Ex: kg, L, un" 
                :required="true"
                :disabled="dialogMode === 'view'" 
                :error="errors.unidade" 
              />
            </div>

            <div class="form-row">
              <NumberInput 
                id="quantidade_atual" 
                label="Quantidade Atual" 
                v-model="form.quantidade_atual"
                placeholder="Ex: 500" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.quantidade_atual" 
                :step="0.1" 
              />

              <NumberInput 
                id="quantidade_minima" 
                label="Quantidade Mínima" 
                v-model="form.quantidade_minima"
                placeholder="Ex: 100" 
                :required="true" 
                :disabled="dialogMode === 'view'"
                :error="errors.quantidade_minima" 
                :step="0.1" 
              />
            </div>

            <div class="form-row">
              <NumberInput 
                id="valor_unitario" 
                label="Valor Unitário (R$)" 
                v-model="form.valor_unitario"
                placeholder="Ex: 2.50" 
                :disabled="dialogMode === 'view'"
                :error="errors.valor_unitario" 
                :step="0.01" 
              />

              <CalendarInput 
                id="data_validade" 
                label="Data de Validade" 
                v-model="form.data_validade"
                :disabled="dialogMode === 'view'" 
                :error="errors.data_validade"
                dateFormat="dd/mm/yy" 
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

              <TextInput 
                id="localizacao" 
                label="Localização" 
                v-model="form.localizacao"
                placeholder="Ex: Galpão A - Prateleira 3" 
                :disabled="dialogMode === 'view'"
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
          <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveItem"
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
          <p class="text-center">Tem certeza que deseja excluir o item <strong>{{ itemToDelete?.nome }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false">
            <i class="pi pi-times"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deleteItem">
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

const estoque = ref([])
const loading = ref(false)
const searchTerm = ref('')
const filtroTipo = ref(null)
const filtroPropriedade = ref(null)

const estatisticas = reactive({
  total_itens: 0,
  baixo_estoque: 0,
  estoque_adequado: 0,
})

const pagination = reactive({
  current: 1,
  perPage: 15,
  total: 0,
  totalPages: 0
})

const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const itemToDelete = ref(null)

const form = reactive({
  id: null as number | null,
  nome: '',
  tipo: '',
  unidade: '',
  quantidade_atual: '',
  quantidade_minima: '',
  valor_unitario: '',
  data_validade: null as Date | null,
  propriedade_id: null as number | null,
  localizacao: '',
  observacoes: '',
})

const errors = reactive({
  nome: '',
  tipo: '',
  unidade: '',
  quantidade_atual: '',
  quantidade_minima: '',
  valor_unitario: '',
  data_validade: '',
  propriedade_id: '',
})

const tipoOptions = [
  { label: 'Ração', value: 'racao' },
  { label: 'Medicamento', value: 'medicamento' },
  { label: 'Vacina', value: 'vacina' },
  { label: 'Suplemento', value: 'suplemento' },
  { label: 'Equipamento', value: 'equipamento' },
  { label: 'Outros', value: 'outros' }
]

const propriedadesOptions = ref([])

const loadEstoque = async () => {
  try {
    loading.value = true

    const params: any = {
      page: pagination.current,
      per_page: pagination.perPage
    }

    if (searchTerm.value) params.search = searchTerm.value
    if (filtroTipo.value) params.tipo = filtroTipo.value
    if (filtroPropriedade.value) params.propriedade_id = filtroPropriedade.value

    const response = await api.get('/v1/estoque', { params })

    if (response.data.success) {
      estoque.value = response.data.data.data || []
      pagination.total = response.data.data.total || 0
      pagination.totalPages = response.data.data.last_page || 1
    }
  } catch (error: any) {
    toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao carregar estoque', life: 5000 })
  } finally {
    loading.value = false
  }
}

const loadEstatisticas = async () => {
  try {
    const response = await api.get('/v1/estoque/estatisticas')
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

const createDebounce = (func: Function, delay: number) => {
  let timeout: ReturnType<typeof setTimeout>
  return (...args: any[]) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(null, args), delay)
  }
}

const debouncedSearch = createDebounce(() => {
  pagination.current = 1
  loadEstoque()
}, 500)

const clearFilters = () => {
  searchTerm.value = ''
  filtroTipo.value = null
  filtroPropriedade.value = null
  pagination.current = 1
  loadEstoque()
}

const onPageChange = (page: number) => {
  pagination.current = page
  loadEstoque()
}

const onPerPageChange = (perPage: number) => {
  pagination.perPage = perPage
  pagination.current = 1
  loadEstoque()
}

const openCreateDialog = () => {
  resetForm()
  dialogMode.value = 'create'
  showDialog.value = true
}

const viewItem = async (item: any) => {
  await fillForm(item)
  dialogMode.value = 'view'
  showDialog.value = true
}

const editItem = async (item: any) => {
  await fillForm(item)
  dialogMode.value = 'edit'
  showDialog.value = true
}

const fillForm = async (item: any) => {
  form.id = item.id
  form.nome = item.nome
  form.tipo = item.tipo
  form.unidade = item.unidade
  form.quantidade_atual = item.quantidade_atual.toString()
  form.quantidade_minima = item.quantidade_minima.toString()
  form.valor_unitario = item.valor_unitario?.toString() || ''
  form.data_validade = item.data_validade ? new Date(item.data_validade) : null
  form.propriedade_id = item.propriedade_id
  form.localizacao = item.localizacao || ''
  form.observacoes = item.observacoes || ''
  
  await nextTick()
}

const resetForm = () => {
  form.id = null
  form.nome = ''
  form.tipo = ''
  form.unidade = ''
  form.quantidade_atual = ''
  form.quantidade_minima = ''
  form.valor_unitario = ''
  form.data_validade = null
  form.propriedade_id = null
  form.localizacao = ''
  form.observacoes = ''
  Object.keys(errors).forEach(key => (errors as any)[key] = '')
}

const saveItem = async () => {
  try {
    dialogLoading.value = true

    const payload = {
      nome: form.nome,
      tipo: form.tipo,
      unidade: form.unidade,
      quantidade_atual: parseFloat(form.quantidade_atual),
      quantidade_minima: parseFloat(form.quantidade_minima),
      valor_unitario: form.valor_unitario ? parseFloat(form.valor_unitario) : null,
      data_validade: form.data_validade ? form.data_validade.toISOString().split('T')[0] : null,
      propriedade_id: form.propriedade_id,
      localizacao: form.localizacao,
      observacoes: form.observacoes,
    }

    let response
    if (dialogMode.value === 'create') {
      response = await api.post('/v1/estoque', payload)
    } else {
      response = await api.put(`/v1/estoque/${form.id}`, payload)
    }

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: `Item ${dialogMode.value === 'create' ? 'criado' : 'atualizado'} com sucesso`,
        life: 3000
      })

      closeDialog()
      loadEstoque()
      loadEstatisticas()
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: error.response?.data?.message || 'Erro ao salvar item',
        life: 5000
      })
    }
  } finally {
    dialogLoading.value = false
  }
}

const confirmDelete = (item: any) => {
  itemToDelete.value = item
  showConfirmDialog.value = true
}

const deleteItem = async () => {
  if (!itemToDelete.value) return

  try {
    const response = await api.delete(`/v1/estoque/${(itemToDelete.value as any).id}`)

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Item excluído', life: 3000 })
      showConfirmDialog.value = false
      loadEstoque()
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

const getTipoIcon = (tipo: string) => {
  const icons: any = {
    'racao': 'pi pi-shopping-cart',
    'medicamento': 'pi pi-heart',
    'vacina': 'pi pi-check-circle',
    'suplemento': 'pi pi-plus-circle',
    'equipamento': 'pi pi-wrench',
    'outros': 'pi pi-box'
  }
  return icons[tipo] || 'pi pi-box'
}

const getTipoLabel = (tipo: string) => {
  const labels: any = {
    'racao': 'Ração',
    'medicamento': 'Medicamento',
    'vacina': 'Vacina',
    'suplemento': 'Suplemento',
    'equipamento': 'Equipamento',
    'outros': 'Outros'
  }
  return labels[tipo] || tipo
}

const getQuantidadeClass = (item: any) => {
  if (item.quantidade_atual <= item.quantidade_minima) {
    return 'quantidade-critico'
  } else if (item.quantidade_atual <= item.quantidade_minima * 1.5) {
    return 'quantidade-alerta'
  }
  return 'quantidade-normal'
}

const getStatusClass = (item: any) => {
  if (item.quantidade_atual <= item.quantidade_minima) {
    return 'status-critico'
  } else if (item.quantidade_atual <= item.quantidade_minima * 1.5) {
    return 'status-alerta'
  }
  return 'status-adequado'
}

const getStatusLabel = (item: any) => {
  if (item.quantidade_atual <= item.quantidade_minima) {
    return 'CRÍTICO'
  } else if (item.quantidade_atual <= item.quantidade_minima * 1.5) {
    return 'BAIXO'
  }
  return 'ADEQUADO'
}

const formatCurrency = (value: number) => {
  if (!value) return '0,00'
  return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  loadEstoque()
  loadEstatisticas()
  loadPropriedades()
})
</script>

<style scoped>
.estoque-container {
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

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.dashboard-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.alert-card {
  border: 2px solid #fecaca;
  background: #fef2f2;
}

.card-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
}

.card-content {
  display: flex;
  flex-direction: column;
}

.card-label {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.card-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.text-danger {
  color: #dc2626 !important;
}

.card-info {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 600;
  margin-top: 0.25rem;
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

.item-name-cell {
  display: flex;
  align-items: center;
}

.quantidade-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantidade-critico {
  color: #dc2626;
  font-weight: 700;
}

.quantidade-alerta {
  color: #f59e0b;
  font-weight: 600;
}

.quantidade-normal {
  color: #16a34a;
  font-weight: 600;
}

.unidade {
  color: #64748b;
  font-size: 0.875rem;
}

.badge-tipo {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  background: #f1f5f9;
  color: #475569;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-critico {
  background: #fee2e2;
  color: #dc2626;
}

.status-alerta {
  background: #fef3c7;
  color: #f59e0b;
}

.status-adequado {
  background: #dcfce7;
  color: #16a34a;
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

  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}
</style>

