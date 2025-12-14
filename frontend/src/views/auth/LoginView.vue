<template>
  <div class="login-container">
    <div class="login-card">
      <div class="card-header">
        <div class="logo-container">
          <SimpleLogo size="large" />
        </div>
        <h1 class="app-title">Sistema de Gestão Agropecuária</h1>
      </div>

      <div class="card-body">
        <div class="login-form">
          <div class="form-group">
            <label for="email" class="form-label">E-mail</label>
            <div class="input-wrapper">
              <i class="pi pi-envelope input-icon"></i>
              <InputText id="email" v-model="form.email" type="email" placeholder="Digite seu e-mail" class="form-input"
                required @keyup.enter="handleLogin" />
            </div>
            <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Senha</label>
            <div class="input-wrapper">
              <i class="pi pi-lock input-icon"></i>
              <InputText id="password" v-model="form.password" type="password" placeholder="Digite sua senha"
                class="form-input" required @keyup.enter="handleLogin" />
            </div>
            <div class="password-field-footer">
              <a href="#" @click.prevent="showForgotPassword = true" class="forgot-password-link">Esqueceu a senha?</a>
            </div>
            <small v-if="errors.password" class="error-text">{{ errors.password[0] }}</small>
          </div>

          <Button @click="handleLogin" label="Entrar" class="login-btn" icon="pi pi-sign-in" :loading="loading" />
        </div>
      </div>

      <div class="card-footer">
        <span>Não tem uma conta? <a href="#" class="register-link"
            @click.prevent="showRegister = true">Registre-se</a></span>
        <router-link to="/" class="back-to-home-link">
          Voltar para página inicial
        </router-link>
      </div>
    </div>

    <!-- Modal de Registro -->
    <RegisterModal v-model:visible="showRegister" @success="handleRegisterSuccess" />

    <!-- Modal de Recuperação de Senha -->
    <ForgotPasswordModal v-model:visible="showForgotPassword" @success="handlePasswordResetSent" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import RegisterModal from '@/components/auth/RegisterModal.vue'
import ForgotPasswordModal from '@/components/auth/ForgotPasswordModal.vue'
import SimpleLogo from '@/components/common/SimpleLogo.vue'
import type { LoginForm, ValidationErrors } from '@/types/auth'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const showRegister = ref(false)
const showForgotPassword = ref(false)
const loading = ref(false)

const form = reactive<LoginForm>({
  email: '',
  password: ''
})

const errors = ref<ValidationErrors>({})

const handleLogin = async () => {
  errors.value = {}
  loading.value = true

  try {
    const result = await authStore.login(form.email, form.password)

    if (result.success) {
      toast.add({
        severity: 'success',
        summary: 'Login realizado',
        detail: 'Bem-vindo ao AgroSistemas!',
        life: 3000
      })
      router.push('/')
    } else {
      // Limpa apenas a senha, mantém o email
      form.password = ''

      if (result.errors) {
        errors.value = result.errors
      }

      // Sempre mostra notificação de erro
      toast.add({
        severity: 'error',
        summary: 'Erro de Login',
        detail: result.message || 'Credenciais inválidas',
        life: 4000
      })
    }
  } catch (error) {
    // Limpa apenas a senha em caso de erro
    form.password = ''

    toast.add({
      severity: 'error',
      summary: 'Erro',
      detail: 'Erro interno do servidor',
      life: 4000
    })
  } finally {
    loading.value = false
  }
}
const handleRegisterSuccess = () => {
  // Pode adicionar aqui lógica adicional após registro bem-sucedido
  // Por exemplo, focar no campo de email para login
  setTimeout(() => {
    const emailInput = document.getElementById('email')
    emailInput?.focus()
  }, 100)
}

const handlePasswordResetSent = () => {
  // Callback quando email de recuperação é enviado
  showForgotPassword.value = false
  toast.add({
    severity: 'info',
    summary: 'Verifique seu email',
    detail: 'Se o email estiver cadastrado, você receberá as instruções de recuperação.',
    life: 6000
  })
}
</script>
<style scoped>
/* CONTAINER PRINCIPAL */
.login-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #10b981 0%, #059669 25%, #047857 50%, #065f46 75%, #064e3b 100%);
  overflow: hidden;
}

/* CARD DE LOGIN */
.login-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(16, 185, 129, 0.2);
  width: 420px;
  max-width: 90vw;
  overflow: hidden;
  position: relative;
  z-index: 10;
}

/* HEADER DO CARD */
.card-header {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  padding: 2.5rem 2rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  color: white;
  position: relative;
}

.logo-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

/* Logo já estilizado pelo componente SimpleLogo */

.app-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
  margin: 0;
  text-align: center;
  letter-spacing: -0.025em;
}

/* BODY DO CARD */
.card-body {
  padding: 2.5rem;
}

.login-form {
  width: 100%;
}

.form-group {
  margin-bottom: 1.75rem;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
  font-size: 1rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1.25rem;
  color: #9ca3af;
  font-size: 1.2rem;
  z-index: 2;
}

.form-input {
  width: 100% !important;
  padding: 1.25rem 1.25rem 1.25rem 3.5rem !important;
  border: 2px solid #e5e7eb !important;
  border-radius: 16px !important;
  font-size: 1.1rem !important;
  transition: all 0.3s ease !important;
  background: #f9fafb !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.form-input:focus {
  border-color: #10b981 !important;
  background: white !important;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
  outline: none !important;
}

.error-text {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.login-btn {
  width: 100% !important;
  padding: 1.25rem 2rem !important;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  border: none !important;
  border-radius: 16px !important;
  font-size: 1.2rem !important;
  font-weight: 600 !important;
  margin-top: 1.5rem !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35) !important;
  letter-spacing: 0.025em !important;
}

.login-btn:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 12px 30px rgba(16, 185, 129, 0.45) !important;
}

/* FOOTER DO CAMPO SENHA */
.password-field-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 0.5rem;
  min-height: 20px;
}

/* LINK DE RECUPERAÇÃO DE SENHA */
.forgot-password-link {
  color: #6b7280;
  font-size: 0.875rem;
  text-decoration: none;
  transition: color 0.2s;
  font-weight: 500;
}

.forgot-password-link:hover {
  color: #10b981;
  text-decoration: underline;
}

/* FOOTER DO CARD */
.card-footer {
  padding: 2rem 2.5rem;
  text-align: center;
  border-top: 1px solid #e5e7eb;
  font-size: 1rem;
  color: #6b7280;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.register-link {
  color: #10b981;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.register-link:hover {
  color: #059669;
  text-decoration: underline;
}

.back-to-home-link {
  color: #6b7280;
  font-size: 0.9rem;
  text-decoration: none;
  transition: color 0.2s;
  font-weight: 500;
}

.back-to-home-link:hover {
  color: #10b981;
  text-decoration: underline;
}
</style>