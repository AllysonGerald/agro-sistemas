<template>
    <div class="financeiro-container">
        <!-- Header -->
        <div class="card">
            <div class="header-container">
                <div class="header-content">
                    <h2 class="text-2xl font-semibold text-900 m-0 mb-2">Financeiro</h2>
                    <p class="text-600 mt-0 mb-0">Gerencie suas transações financeiras</p>
                </div>
                <div class="header-actions">
                    <Button label="Nova Transação" icon="pi pi-plus" @click="openCreateDialog" size="large"
                        class="btn-primary" />
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="dashboard-card receitas">
                <div class="card-icon">
                    <i class="pi pi-arrow-up"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Receitas</span>
                    <span class="card-value">R$ {{ formatCurrency(dashboard.receitas) }}</span>
                    <span class="card-info">+18% este mês</span>
                </div>
            </div>

            <div class="dashboard-card despesas">
                <div class="card-icon">
                    <i class="pi pi-arrow-down"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Despesas</span>
                    <span class="card-value">R$ {{ formatCurrency(dashboard.despesas) }}</span>
                    <span class="card-info">-8% este mês</span>
                </div>
            </div>

            <div class="dashboard-card saldo">
                <div class="card-icon">
                    <i class="pi pi-wallet"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Saldo</span>
                    <span class="card-value">R$ {{ formatCurrency(dashboard.saldo) }}</span>
                    <span :class="dashboard.saldo >= 0 ? 'card-info-success' : 'card-info-danger'">
                        {{ dashboard.saldo >= 0 ? 'LUCRO' : 'PREJUÍZO' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="filtros-container">
                <div class="busca-container">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <InputText v-model="searchTerm" placeholder="Buscar..." @input="debouncedSearch"
                            class="search-input" />
                    </div>
                </div>
                <div class="filtros-dropdown">
                    <Select v-model="filtroTipo" :options="tipoOptions" placeholder="Todos os tipos" optionLabel="label"
                        optionValue="value" @change="loadTransacoes" class="filtro-select" showClear />
                    <Select v-model="filtroCategoria" :options="categoriasOptions" placeholder="Todas as categorias"
                        optionLabel="nome" optionValue="id" @change="loadTransacoes" class="filtro-select" showClear />
                </div>
                <Button label="Limpar" icon="pi pi-filter-slash" severity="secondary" outlined @click="clearFilters"
                    class="limpar-btn" />
            </div>
        </div>

        <!-- Tabela de Transações -->
        <div class="card">
            <DataTable :value="transacoes" :loading="loading" responsive-layout="scroll" class="p-datatable-sm">
                <Column field="data" header="Data" sortable>
                    <template #body="slotProps">
                        {{ formatDateBR(slotProps.data.data) }}
                    </template>
                </Column>

                <Column field="tipo" header="Tipo" sortable>
                    <template #body="slotProps">
                        <span class="badge-tipo" :class="`tipo-${slotProps.data.tipo}`">
                            {{ slotProps.data.tipo === 'receita' ? 'Receita' : 'Despesa' }}
                        </span>
                    </template>
                </Column>

                <Column field="categoria.nome" header="Categoria" sortable>
                    <template #body="slotProps">
                        <span class="badge-categoria">
                            {{ slotProps.data.categoria?.nome || '-' }}
                        </span>
                    </template>
                </Column>

                <Column field="descricao" header="Descrição" sortable />

                <Column field="animal" header="Animal" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.animal?.identificacao || '-' }}
                    </template>
                </Column>

                <Column field="valor" header="Valor" sortable>
                    <template #body="slotProps">
                        <span class="valor-cell"
                            :class="slotProps.data.tipo === 'receita' ? 'valor-receita' : 'valor-despesa'">
                            R$ {{ formatCurrency(slotProps.data.valor) }}
                        </span>
                    </template>
                </Column>

                <Column header="Ações" :exportable="false" style="min-width: 10rem">
                    <template #body="slotProps">
                        <div class="flex gap-4 justify-content-start">
                            <Button icon="pi pi-eye" severity="info" outlined size="small"
                                @click="viewTransacao(slotProps.data)" v-tooltip="'Visualizar'" />
                            <Button icon="pi pi-pencil" severity="warning" outlined size="small"
                                @click="editTransacao(slotProps.data)" v-tooltip="'Editar'" />
                            <Button icon="pi pi-trash" severity="danger" outlined size="small"
                                @click="confirmDelete(slotProps.data)" v-tooltip="'Excluir'" />
                        </div>
                    </template>
                </Column>

                <template #empty>
                    <div class="text-center p-4">
                        <i class="pi pi-wallet text-4xl text-400 mb-3"></i>
                        <p class="text-600 text-lg">Nenhuma transação encontrada</p>
                    </div>
                </template>
            </DataTable>

            <CustomPagination :current-page="pagination.current" :total-pages="pagination.totalPages"
                :total="pagination.total" :per-page="pagination.perPage" @page-change="onPageChange"
                @per-page-change="onPerPageChange" />
        </div>

        <!-- Modal de Transação -->
        <div v-if="showDialog" class="modal-overlay" @click.self="closeDialog">
            <div class="modal-container" style="max-width: 600px;">
                <div class="modal-header">
                    <h2 class="modal-title">
                        <div class="modal-title-icon" style="background: #dcfce7; color: #16a34a;">
                            <i class="pi pi-wallet"></i>
                        </div>
                        {{ dialogMode === 'create' ? 'Nova Transação' : dialogMode === 'edit' ? 'Editar Transação' : 'Visualizar Transação' }}
                    </h2>
                    <button class="modal-close" @click="closeDialog">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="modal-content">
                    <form @submit.prevent="saveTransacao" class="modal-form">
                        <!-- Tipo e Categoria -->
                        <div class="form-row">
                            <DropdownInput id="tipo" label="Tipo" v-model="form.tipo" :options="tipoOptions"
                                :required="true" :disabled="dialogMode === 'view'" :error="errors.tipo"
                                optionLabel="label" optionValue="value" />

                            <DropdownInput id="categoria_id" label="Categoria" v-model="form.categoria_id"
                                :options="categoriasFiltradas" :required="true" :disabled="dialogMode === 'view'"
                                :error="errors.categoria_id" optionLabel="nome" optionValue="id" />
                        </div>

                        <!-- Descrição -->
                        <TextInput id="descricao" label="Descrição" v-model="form.descricao"
                            placeholder="Ex: Venda de todo lote 009" :required="true" :disabled="dialogMode === 'view'"
                            :error="errors.descricao" />

                        <!-- Valor e Data -->
                        <div class="form-row">
                            <NumberInput id="valor" label="Valor (R$)" v-model="form.valor" placeholder="Ex: 1500.00"
                                :required="true" :disabled="dialogMode === 'view'" :error="errors.valor" :step="0.01" />

                            <CalendarInput id="data" label="Data" v-model="form.data" :required="true"
                                :disabled="dialogMode === 'view'" :error="errors.data" dateFormat="dd/mm/yy" />
                        </div>

                        <!-- Animal e Lote (opcionais) -->
                        <div class="form-row">
                            <DropdownInput id="animal_id" label="Animal (opcional)" v-model="form.animal_id"
                                :options="animaisOptions" :disabled="dialogMode === 'view'" :error="errors.animal_id"
                                optionLabel="identificacao" optionValue="id" />

                            <DropdownInput id="lote_id" label="Lote (opcional)" v-model="form.lote_id"
                                :options="lotesOptions" :disabled="dialogMode === 'view'" :error="errors.lote_id"
                                optionLabel="nome" optionValue="id" />
                        </div>

                        <!-- Propriedade e Forma de Pagamento -->
                        <div class="form-row">
                            <DropdownInput id="propriedade_id" label="Propriedade" v-model="form.propriedade_id"
                                :options="propriedadesOptions" :required="true" :disabled="dialogMode === 'view'"
                                :error="errors.propriedade_id" optionLabel="nome" optionValue="id" />

                            <TextInput id="forma_pagamento" label="Forma de Pagamento" v-model="form.forma_pagamento"
                                placeholder="Ex: PIX, Dinheiro" :disabled="dialogMode === 'view'" />
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
                    <button v-if="dialogMode !== 'view'" type="button" class="btn btn-primary" @click="saveTransacao"
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
                    <p class="text-center">Tem certeza que deseja excluir esta transação?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showConfirmDialog = false">
                        <i class="pi pi-times"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" @click="deleteTransacao">
                        <i class="pi pi-trash"></i>
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, nextTick } from 'vue'
import { useToast } from 'primevue/usetoast'
import api from '../../services/api'
import { TextInput, NumberInput, DropdownInput, CalendarInput, CustomPagination } from '../../components/forms'
import Textarea from 'primevue/textarea'

const toast = useToast()

// Estado
const transacoes = ref([])
const loading = ref(false)
const searchTerm = ref('')
const filtroTipo = ref(null)
const filtroCategoria = ref(null)

const dashboard = reactive({
    receitas: 0,
    despesas: 0,
    saldo: 0,
})

const pagination = reactive({
    current: 1,
    perPage: 15,
    total: 0,
    totalPages: 0
})

// Dialog states
const showDialog = ref(false)
const showConfirmDialog = ref(false)
const dialogMode = ref<'create' | 'edit' | 'view'>('create')
const dialogLoading = ref(false)
const transacaoToDelete = ref(null)

// Form
const form = reactive({
    id: null as number | null,
    tipo: 'receita',
    categoria_id: null as number | null,
    descricao: '',
    valor: '',
    data: new Date(),
    animal_id: null as number | null,
    lote_id: null as number | null,
    propriedade_id: null as number | null,
    forma_pagamento: '',
    observacoes: '',
})

const errors = reactive({
    tipo: '',
    categoria_id: '',
    descricao: '',
    valor: '',
    data: '',
    animal_id: '',
    lote_id: '',
    propriedade_id: '',
})

// Opções
const tipoOptions = [
    { label: 'Receita', value: 'receita' },
    { label: 'Despesa', value: 'despesa' }
]

const categoriasOptions = ref([])
const animaisOptions = ref([])
const lotesOptions = ref([])
const propriedadesOptions = ref([])

const categoriasFiltradas = computed(() => {
    if (!form.tipo) return categoriasOptions.value
    return categoriasOptions.value.filter((cat: any) => cat.tipo === form.tipo)
})

// Methods
const loadTransacoes = async () => {
    try {
        loading.value = true

        const params: any = {
            page: pagination.current,
            per_page: pagination.perPage
        }

        if (searchTerm.value) params.search = searchTerm.value
        if (filtroTipo.value) params.tipo = filtroTipo.value
        if (filtroCategoria.value) params.categoria_id = filtroCategoria.value

        const response = await api.get('/v1/transacoes', { params })

        if (response.data.success) {
            transacoes.value = response.data.data.data || []
            pagination.total = response.data.data.total || 0
            pagination.totalPages = response.data.data.last_page || 1
        }
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao carregar transações', life: 5000 })
    } finally {
        loading.value = false
    }
}

const loadDashboard = async () => {
    try {
        const response = await api.get('/v1/transacoes/dashboard')
        if (response.data.success) {
            Object.assign(dashboard, response.data.data)
        }
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error)
    }
}

const loadCategorias = async () => {
    try {
        const response = await api.get('/v1/categorias-financeiras')
        if (response.data.success) {
            categoriasOptions.value = response.data.data || []
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error)
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
    loadTransacoes()
}, 500)

const clearFilters = () => {
    searchTerm.value = ''
    filtroTipo.value = null
    filtroCategoria.value = null
    pagination.current = 1
    loadTransacoes()
}

const onPageChange = (page: number) => {
    pagination.current = page
    loadTransacoes()
}

const onPerPageChange = (perPage: number) => {
    pagination.perPage = perPage
    pagination.current = 1
    loadTransacoes()
}

const openCreateDialog = () => {
    resetForm()
    dialogMode.value = 'create'
    showDialog.value = true
}

const viewTransacao = async (transacao: any) => {
    await fillForm(transacao)
    dialogMode.value = 'view'
    showDialog.value = true
}

const editTransacao = async (transacao: any) => {
    await fillForm(transacao)
    dialogMode.value = 'edit'
    showDialog.value = true
}

const fillForm = async (transacao: any) => {
    form.id = transacao.id
    form.tipo = transacao.tipo
    form.categoria_id = transacao.categoria_id
    form.descricao = transacao.descricao
    form.valor = transacao.valor.toString()
    form.data = new Date(transacao.data)
    form.animal_id = transacao.animal_id
    form.lote_id = transacao.lote_id
    form.propriedade_id = transacao.propriedade_id
    form.forma_pagamento = transacao.forma_pagamento || ''
    form.observacoes = transacao.observacoes || ''

    await nextTick()
}

const resetForm = () => {
    form.id = null
    form.tipo = 'receita'
    form.categoria_id = null
    form.descricao = ''
    form.valor = ''
    form.data = new Date()
    form.animal_id = null
    form.lote_id = null
    form.propriedade_id = null
    form.forma_pagamento = ''
    form.observacoes = ''
    Object.keys(errors).forEach(key => (errors as any)[key] = '')
}

const saveTransacao = async () => {
    try {
        dialogLoading.value = true

        const payload = {
            tipo: form.tipo,
            categoria_id: form.categoria_id,
            descricao: form.descricao,
            valor: parseFloat(form.valor),
            data: form.data.toISOString().split('T')[0],
            animal_id: form.animal_id,
            lote_id: form.lote_id,
            propriedade_id: form.propriedade_id,
            forma_pagamento: form.forma_pagamento,
            observacoes: form.observacoes,
        }

        let response
        if (dialogMode.value === 'create') {
            response = await api.post('/v1/transacoes', payload)
        } else {
            response = await api.put(`/v1/transacoes/${form.id}`, payload)
        }

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: `Transação ${dialogMode.value === 'create' ? 'criada' : 'atualizada'} com sucesso`,
                life: 3000
            })

            closeDialog()
            loadTransacoes()
            loadDashboard()
        }
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.assign(errors, error.response.data.errors)
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: error.response?.data?.message || 'Erro ao salvar transação',
                life: 5000
            })
        }
    } finally {
        dialogLoading.value = false
    }
}

const confirmDelete = (transacao: any) => {
    transacaoToDelete.value = transacao
    showConfirmDialog.value = true
}

const deleteTransacao = async () => {
    if (!transacaoToDelete.value) return

    try {
        const response = await api.delete(`/v1/transacoes/${(transacaoToDelete.value as any).id}`)

        if (response.data.success) {
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Transação excluída', life: 3000 })
            showConfirmDialog.value = false
            loadTransacoes()
            loadDashboard()
        }
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao excluir', life: 5000 })
    }
}

const closeDialog = () => {
    showDialog.value = false
    resetForm()
}

const formatCurrency = (value: number) => {
    if (!value) return '0,00'
    return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDateBR = (value: string) => {
    if (!value) return ''
    return new Date(value).toLocaleDateString('pt-BR')
}

// Lifecycle
onMounted(() => {
    loadTransacoes()
    loadDashboard()
    loadCategorias()
    loadPropriedades()
    loadAnimais()
    loadLotes()
})
</script>

<style scoped>
.financeiro-container {
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

/* Dashboard Cards */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
    transition: transform 0.2s;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.dashboard-card.receitas {
    border-left: 4px solid #16a34a;
}

.dashboard-card.despesas {
    border-left: 4px solid #dc2626;
}

.dashboard-card.saldo {
    border-left: 4px solid #2563eb;
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

.receitas .card-icon {
    background: #dcfce7;
    color: #16a34a;
}

.despesas .card-icon {
    background: #fee2e2;
    color: #dc2626;
}

.saldo .card-icon {
    background: #dbeafe;
    color: #2563eb;
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

.card-info {
    font-size: 0.75rem;
    color: #16a34a;
    margin-top: 0.25rem;
}

.card-info-success {
    font-size: 0.75rem;
    color: #16a34a;
    font-weight: 600;
}

.card-info-danger {
    font-size: 0.75rem;
    color: #dc2626;
    font-weight: 600;
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

/* Badges */
.badge-tipo {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.tipo-receita {
    background: #dcfce7;
    color: #16a34a;
}

.tipo-despesa {
    background: #fee2e2;
    color: #dc2626;
}

.badge-categoria {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    background: #f1f5f9;
    color: #475569;
}

.valor-cell {
    font-weight: 700;
    font-size: 1rem;
}

.valor-receita {
    color: #16a34a;
}

.valor-despesa {
    color: #dc2626;
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
