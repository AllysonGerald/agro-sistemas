<template>
  <div class="configuracoes-container">
    <div class="header">
      <div>
        <h1 class="title">Configurações</h1>
        <p class="subtitle">Gerencie as configurações do sistema</p>
      </div>
    </div>

    <div class="configuracoes-layout">
      <!-- Menu Lateral -->
      <div class="configuracoes-menu">
        <div
          v-for="secao in secoes"
          :key="secao.id"
          class="menu-item"
          :class="{ active: secaoAtiva === secao.id }"
          @click="secaoAtiva = secao.id"
        >
          <i :class="secao.icon"></i>
          <span>{{ secao.label }}</span>
        </div>
      </div>

      <!-- Conteúdo -->
      <div class="configuracoes-content">
        <!-- Perfil do Usuário -->
        <div v-if="secaoAtiva === 'perfil'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-user"></i>
            Perfil do Usuário
          </h2>
          
          <div class="form-grid">
            <div class="form-group">
              <label>Nome Completo</label>
              <input v-model="perfil.nome" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>E-mail</label>
              <input v-model="perfil.email" type="email" class="form-input" />
            </div>

            <div class="form-group">
              <label>Telefone</label>
              <input v-model="perfil.telefone" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>Cargo/Função</label>
              <input v-model="perfil.cargo" type="text" class="form-input" />
            </div>
          </div>

          <div class="form-actions">
            <button @click="salvarPerfil" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Salvar Perfil
            </button>
          </div>
        </div>

        <!-- Segurança -->
        <div v-if="secaoAtiva === 'seguranca'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-lock"></i>
            Segurança
          </h2>

          <div class="form-grid">
            <div class="form-group full-width">
              <label>Senha Atual</label>
              <input v-model="senha.atual" type="password" class="form-input" />
            </div>

            <div class="form-group">
              <label>Nova Senha</label>
              <input v-model="senha.nova" type="password" class="form-input" />
            </div>

            <div class="form-group">
              <label>Confirmar Nova Senha</label>
              <input v-model="senha.confirmacao" type="password" class="form-input" />
            </div>
          </div>

          <div class="form-actions">
            <button @click="alterarSenha" class="btn btn-primary">
              <i class="fas fa-key"></i>
              Alterar Senha
            </button>
          </div>

          <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>A senha deve ter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas e números.</p>
          </div>
        </div>

        <!-- Empresa -->
        <div v-if="secaoAtiva === 'empresa'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-building"></i>
            Dados da Empresa
          </h2>

          <div class="form-grid">
            <div class="form-group full-width">
              <label>Razão Social</label>
              <input v-model="empresa.razao_social" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>CNPJ/CPF</label>
              <input v-model="empresa.cnpj" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>Inscrição Estadual</label>
              <input v-model="empresa.inscricao_estadual" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>Telefone</label>
              <input v-model="empresa.telefone" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label>E-mail</label>
              <input v-model="empresa.email" type="email" class="form-input" />
            </div>

            <div class="form-group full-width">
              <label>Endereço</label>
              <textarea v-model="empresa.endereco" class="form-input" rows="3"></textarea>
            </div>
          </div>

          <div class="form-actions">
            <button @click="salvarEmpresa" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Salvar Dados da Empresa
            </button>
          </div>
        </div>

        <!-- Sistema -->
        <div v-if="secaoAtiva === 'sistema'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-cog"></i>
            Preferências do Sistema
          </h2>

          <div class="preferencias-list">
            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Tema</h3>
                <p>Escolha o tema de cores do sistema</p>
              </div>
              <select v-model="sistema.tema" class="form-select">
                <option value="claro">Claro</option>
                <option value="escuro">Escuro</option>
                <option value="auto">Automático</option>
              </select>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Idioma</h3>
                <p>Idioma da interface</p>
              </div>
              <select v-model="sistema.idioma" class="form-select">
                <option value="pt-BR">Português (Brasil)</option>
                <option value="en">English</option>
                <option value="es">Español</option>
              </select>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Formato de Data</h3>
                <p>Como as datas serão exibidas</p>
              </div>
              <select v-model="sistema.formato_data" class="form-select">
                <option value="DD/MM/YYYY">DD/MM/AAAA</option>
                <option value="MM/DD/YYYY">MM/DD/AAAA</option>
                <option value="YYYY-MM-DD">AAAA-MM-DD</option>
              </select>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Moeda</h3>
                <p>Moeda padrão para valores monetários</p>
              </div>
              <select v-model="sistema.moeda" class="form-select">
                <option value="BRL">Real (R$)</option>
                <option value="USD">Dólar ($)</option>
                <option value="EUR">Euro (€)</option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button @click="salvarSistema" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Salvar Preferências
            </button>
          </div>
        </div>

        <!-- Notificações -->
        <div v-if="secaoAtiva === 'notificacoes'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-bell"></i>
            Notificações
          </h2>

          <div class="preferencias-list">
            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Notificações por E-mail</h3>
                <p>Receber notificações importantes por e-mail</p>
              </div>
              <label class="switch">
                <input v-model="notificacoes.email" type="checkbox" />
                <span class="slider"></span>
              </label>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Alertas de Estoque</h3>
                <p>Notificar quando estoque estiver baixo</p>
              </div>
              <label class="switch">
                <input v-model="notificacoes.estoque" type="checkbox" />
                <span class="slider"></span>
              </label>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Alertas de Manejo</h3>
                <p>Lembrete de atividades de manejo pendentes</p>
              </div>
              <label class="switch">
                <input v-model="notificacoes.manejo" type="checkbox" />
                <span class="slider"></span>
              </label>
            </div>

            <div class="preferencia-item">
              <div class="preferencia-info">
                <h3>Resumo Financeiro</h3>
                <p>Relatório financeiro mensal por e-mail</p>
              </div>
              <label class="switch">
                <input v-model="notificacoes.financeiro" type="checkbox" />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="form-actions">
            <button @click="salvarNotificacoes" class="btn btn-primary">
              <i class="fas fa-save"></i>
              Salvar Notificações
            </button>
          </div>
        </div>

        <!-- Backup -->
        <div v-if="secaoAtiva === 'backup'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-database"></i>
            Backup e Dados
          </h2>

          <div class="backup-cards">
            <div class="backup-card">
              <i class="fas fa-download"></i>
              <h3>Exportar Dados</h3>
              <p>Faça backup de todos os dados do sistema</p>
              <button @click="exportarDados" class="btn btn-secondary">
                <i class="fas fa-file-export"></i>
                Exportar
              </button>
            </div>

            <div class="backup-card">
              <i class="fas fa-upload"></i>
              <h3>Importar Dados</h3>
              <p>Restaurar dados de um backup anterior</p>
              <button @click="triggerImport" class="btn btn-secondary">
                <i class="fas fa-file-import"></i>
                Importar
              </button>
              <input ref="fileInput" type="file" @change="importarDados" style="display: none" accept=".json" />
            </div>

            <div class="backup-card">
              <i class="fas fa-trash-alt"></i>
              <h3>Limpar Cache</h3>
              <p>Limpar dados temporários e cache</p>
              <button @click="limparCache" class="btn btn-secondary">
                <i class="fas fa-broom"></i>
                Limpar
              </button>
            </div>
          </div>

          <div class="info-box warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Faça backups regulares dos seus dados. A importação de dados irá sobrescrever os dados existentes.</p>
          </div>
        </div>

        <!-- Sobre -->
        <div v-if="secaoAtiva === 'sobre'" class="secao">
          <h2 class="secao-title">
            <i class="fas fa-info-circle"></i>
            Sobre o Sistema
          </h2>

          <div class="sobre-content">
            <div class="logo-section">
              <SimpleLogo size="large" />
              <p class="version">Versão 1.0.0</p>
            </div>

            <div class="info-grid">
              <div class="info-item">
                <strong>Desenvolvido por:</strong>
                <span>Equipe AgroSistemas</span>
              </div>
              <div class="info-item">
                <strong>Última Atualização:</strong>
                <span>Dezembro 2025</span>
              </div>
              <div class="info-item">
                <strong>Licença:</strong>
                <span>Proprietária</span>
              </div>
              <div class="info-item">
                <strong>Suporte:</strong>
                <span>suporte@agrosistemas.com</span>
              </div>
            </div>

            <div class="links-section">
              <a href="#" class="link-button">
                <i class="fas fa-book"></i>
                Documentação
              </a>
              <a href="#" class="link-button">
                <i class="fas fa-life-ring"></i>
                Suporte Técnico
              </a>
              <a href="#" class="link-button">
                <i class="fas fa-shield-alt"></i>
                Política de Privacidade
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Toast />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'
import Toast from 'primevue/toast'
import SimpleLogo from '../../components/common/SimpleLogo.vue'

const toast = useToast()
const authStore = useAuthStore()

const secaoAtiva = ref('perfil')
const loading = ref(false)

const secoes = [
  { id: 'perfil', label: 'Perfil', icon: 'fas fa-user' },
  { id: 'seguranca', label: 'Segurança', icon: 'fas fa-lock' },
  { id: 'empresa', label: 'Empresa', icon: 'fas fa-building' },
  { id: 'sistema', label: 'Sistema', icon: 'fas fa-cog' },
  { id: 'notificacoes', label: 'Notificações', icon: 'fas fa-bell' },
  { id: 'backup', label: 'Backup', icon: 'fas fa-database' },
  { id: 'sobre', label: 'Sobre', icon: 'fas fa-info-circle' }
]

const perfil = reactive({
  nome: authStore.user?.name || '',
  email: authStore.user?.email || '',
  telefone: '',
  cargo: ''
})

const senha = reactive({
  atual: '',
  nova: '',
  confirmacao: ''
})

const empresa = reactive({
  razao_social: '',
  cnpj: '',
  inscricao_estadual: '',
  telefone: '',
  email: '',
  endereco: ''
})

const sistema = reactive({
  tema: 'claro',
  idioma: 'pt-BR',
  formato_data: 'DD/MM/YYYY',
  moeda: 'BRL'
})

const notificacoes = reactive({
  email: true,
  estoque: true,
  manejo: true,
  financeiro: false
})

const fileInput = ref<HTMLInputElement>()

const salvarPerfil = async () => {
  try {
    loading.value = true
    const response = await api.put('/v1/perfil', {
      name: perfil.nome,
      email: perfil.email
    })

    if (response.data.success) {
      // Atualizar store de autenticação
      authStore.user = {
        ...authStore.user,
        name: perfil.nome,
        email: perfil.email
      }

      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Perfil atualizado com sucesso',
        life: 3000
      })
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao atualizar perfil',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const alterarSenha = async () => {
  if (senha.nova !== senha.confirmacao) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'As senhas não coincidem',
      life: 3000
    })
    return
  }

  try {
    loading.value = true
    const response = await api.post('/v1/perfil/alterar-senha', {
      senha_atual: senha.atual,
      senha_nova: senha.nova,
      senha_nova_confirmation: senha.confirmacao
    })

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Senha alterada com sucesso',
        life: 3000
      })

      senha.atual = ''
      senha.nova = ''
      senha.confirmacao = ''
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao alterar senha',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const salvarEmpresa = async () => {
  try {
    loading.value = true
    const response = await api.post('/v1/empresa', {
      razao_social: empresa.razao_social,
      cnpj: empresa.cnpj,
      inscricao_estadual: empresa.inscricao_estadual,
      telefone: empresa.telefone,
      email: empresa.email,
      endereco: empresa.endereco
    })

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Dados da empresa atualizados',
        life: 3000
      })
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao atualizar empresa',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const salvarSistema = async () => {
  try {
    loading.value = true
    const response = await api.put('/v1/configuracoes/sistema', {
      tema: sistema.tema,
      idioma: sistema.idioma,
      formato_data: sistema.formato_data,
      moeda: sistema.moeda
    })

    if (response.data.success) {
      localStorage.setItem('preferencias', JSON.stringify(sistema))
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Preferências salvas com sucesso',
        life: 3000
      })
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao salvar preferências',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const salvarNotificacoes = async () => {
  try {
    loading.value = true
    const response = await api.put('/v1/configuracoes/notificacoes', {
      notificacao_email: notificacoes.email,
      notificacao_estoque: notificacoes.estoque,
      notificacao_manejo: notificacoes.manejo,
      notificacao_financeiro: notificacoes.financeiro
    })

    if (response.data.success) {
      localStorage.setItem('notificacoes', JSON.stringify(notificacoes))
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Configurações de notificação salvas',
        life: 3000
      })
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: error.response?.data?.message || 'Erro ao salvar notificações',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const exportarDados = () => {
  // TODO: Implementar exportação real
  toast.add({
    severity: 'info',
    summary: 'Exportação',
    detail: 'Iniciando exportação de dados...',
    life: 3000
  })
}

const triggerImport = () => {
  fileInput.value?.click()
}

const importarDados = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    // TODO: Implementar importação real
    toast.add({
      severity: 'info',
      summary: 'Importação',
      detail: `Arquivo ${file.name} selecionado`,
      life: 3000
    })
  }
}

const limparCache = () => {
  localStorage.removeItem('preferencias')
  localStorage.removeItem('notificacoes')
  toast.add({
    severity: 'success',
    summary: 'Sucesso',
    detail: 'Cache limpo com sucesso',
    life: 3000
  })
}

// Carregar configurações ao montar o componente
const carregarConfiguracoes = async () => {
  try {
    loading.value = true

    // Carregar configurações do sistema
    const configResponse = await api.get('/v1/configuracoes')
    if (configResponse.data.success && configResponse.data.data) {
      const config = configResponse.data.data
      sistema.tema = config.tema
      sistema.idioma = config.idioma
      sistema.formato_data = config.formato_data
      sistema.moeda = config.moeda
      notificacoes.email = config.notificacao_email
      notificacoes.estoque = config.notificacao_estoque
      notificacoes.manejo = config.notificacao_manejo
      notificacoes.financeiro = config.notificacao_financeiro
    }

    // Carregar dados da empresa
    const empresaResponse = await api.get('/v1/empresa')
    if (empresaResponse.data.success && empresaResponse.data.data) {
      const emp = empresaResponse.data.data
      empresa.razao_social = emp.razao_social || ''
      empresa.cnpj = emp.cnpj || ''
      empresa.inscricao_estadual = emp.inscricao_estadual || ''
      empresa.telefone = emp.telefone || ''
      empresa.email = emp.email || ''
      empresa.endereco = emp.endereco || ''
    }
  } catch (error) {
    console.error('Erro ao carregar configurações:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  carregarConfiguracoes()
})
</script>

<style scoped>
.configuracoes-container {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
}

.header {
  margin-bottom: 2rem;
}

.title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.subtitle {
  color: #64748b;
  margin: 0;
}

.configuracoes-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 2rem;
}

/* Menu Lateral */
.configuracoes-menu {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  height: fit-content;
  position: sticky;
  top: 1.5rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  color: #64748b;
  font-weight: 500;
}

.menu-item:hover {
  background: #f1f5f9;
  color: #16a34a;
}

.menu-item.active {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
}

.menu-item i {
  width: 20px;
  font-size: 1.1rem;
}

/* Conteúdo */
.configuracoes-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.secao {
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.secao-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 2rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.secao-title i {
  color: #16a34a;
}

/* Formulários */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: #475569;
  font-size: 0.875rem;
}

.form-input,
.form-select {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
  background: #f8fafc;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
  background: white;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-size: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

/* Preferências */
.preferencias-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.preferencia-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.preferencia-info h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.preferencia-info p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.form-select {
  min-width: 200px;
}

/* Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: 0.4s;
  border-radius: 28px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #16a34a;
}

input:checked + .slider:before {
  transform: translateX(22px);
}

/* Backup Cards */
.backup-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.backup-card {
  padding: 2rem;
  background: #f8fafc;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.backup-card i {
  font-size: 3rem;
  color: #16a34a;
}

.backup-card h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.backup-card p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

/* Info Box */
.info-box {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
  border-radius: 8px;
  margin-top: 2rem;
}

.info-box.warning {
  background: #fef3c7;
  border-left-color: #f59e0b;
}

.info-box i {
  color: #3b82f6;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.info-box.warning i {
  color: #f59e0b;
}

.info-box p {
  margin: 0;
  color: #1e293b;
  font-size: 0.875rem;
  line-height: 1.6;
}

/* Sobre */
.sobre-content {
  max-width: 600px;
  margin: 0 auto;
}

.logo-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 2rem 0;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 2rem;
}

.logo-icon {
  font-size: 4rem;
  color: #16a34a;
  margin-bottom: 1rem;
}

.logo-section h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.version {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0.75rem 0 0 0;
}

.info-grid {
  display: grid;
  gap: 1rem;
  margin-bottom: 2rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
}

.info-item strong {
  color: #475569;
}

.info-item span {
  color: #1e293b;
  font-weight: 500;
}

.links-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.link-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  color: #475569;
  text-decoration: none;
  transition: all 0.2s;
}

.link-button:hover {
  border-color: #16a34a;
  color: #16a34a;
  background: #f0fdf4;
}

.link-button i {
  color: #16a34a;
}

/* Responsive */
@media (max-width: 1024px) {
  .configuracoes-layout {
    grid-template-columns: 1fr;
  }

  .configuracoes-menu {
    position: static;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }

  .backup-cards {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full-width {
    grid-column: 1;
  }
}
</style>

