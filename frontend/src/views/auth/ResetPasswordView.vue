<template>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="card-header">
                <div class="logo-container">
                    <div class="logo-circle">
                        <i class="fas fa-key logo-icon"></i>
                        <i class="fas fa-shield-alt logo-badge"></i>
                    </div>
                </div>
                <h1 class="app-title">Redefinir Senha</h1>
                <p class="app-subtitle">Digite sua nova senha</p>
            </div>

            <div class="card-body">
                <form @submit.prevent="handleResetPassword" class="reset-form">
                    <!-- Campo de nova senha -->
                    <div class="form-group">
                        <label for="new-password" class="form-label">Nova Senha</label>
                        <div class="input-wrapper">
                            <i class="pi pi-lock input-icon"></i>
                            <InputText id="new-password" v-model="form.password"
                                :type="showPassword ? 'text' : 'password'" placeholder="Digite sua nova senha"
                                class="form-input" required @input="validatePassword" />
                            <button type="button" class="password-toggle" @click="showPassword = !showPassword">
                                <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                            </button>
                        </div>

                        <!-- Indicadores de força da senha -->
                        <div v-if="form.password" class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" :class="passwordStrengthClass"
                                    :style="{ width: passwordStrengthPercentage + '%' }"></div>
                            </div>
                            <div class="strength-label">
                                Força da senha: <span :class="passwordStrengthClass">{{ passwordStrengthText }}</span>
                            </div>
                        </div>

                        <!-- Critérios de senha -->
                        <div v-if="form.password" class="password-criteria">
                            <div class="criteria-item" :class="{ 'valid': passwordCriteria.length }">
                                <i :class="passwordCriteria.length ? 'pi pi-check' : 'pi pi-times'"></i>
                                Mínimo 8 caracteres
                            </div>
                            <div class="criteria-item" :class="{ 'valid': passwordCriteria.uppercase }">
                                <i :class="passwordCriteria.uppercase ? 'pi pi-check' : 'pi pi-times'"></i>
                                Pelo menos 1 letra maiúscula
                            </div>
                            <div class="criteria-item" :class="{ 'valid': passwordCriteria.lowercase }">
                                <i :class="passwordCriteria.lowercase ? 'pi pi-check' : 'pi pi-times'"></i>
                                Pelo menos 1 letra minúscula
                            </div>
                            <div class="criteria-item" :class="{ 'valid': passwordCriteria.number }">
                                <i :class="passwordCriteria.number ? 'pi pi-check' : 'pi pi-times'"></i>
                                Pelo menos 1 número
                            </div>
                            <div class="criteria-item" :class="{ 'valid': passwordCriteria.special }">
                                <i :class="passwordCriteria.special ? 'pi pi-check' : 'pi pi-times'"></i>
                                Pelo menos 1 caractere especial (!@#$%^&*)
                            </div>
                        </div>

                        <small v-if="errors.password" class="error-text">
                            <div v-for="error in errors.password" :key="error">{{ error }}</div>
                        </small>
                    </div>

                    <!-- Campo de confirmação de senha -->
                    <div class="form-group">
                        <label for="password-confirmation" class="form-label">Confirmar Nova Senha</label>
                        <div class="input-wrapper">
                            <i class="pi pi-lock input-icon"></i>
                            <InputText id="password-confirmation" v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirme sua nova senha"
                                class="form-input" required />
                            <button type="button" class="password-toggle"
                                @click="showConfirmPassword = !showConfirmPassword">
                                <i :class="showConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                            </button>
                        </div>

                        <!-- Validação de confirmação de senha -->
                        <div v-if="form.password_confirmation && form.password" class="password-match">
                            <div class="criteria-item" :class="{ 'valid': passwordsMatch }">
                                <i :class="passwordsMatch ? 'pi pi-check' : 'pi pi-times'"></i>
                                {{ passwordsMatch ? 'Senhas coincidem' : 'Senhas não coincidem' }}
                            </div>
                        </div>

                        <small v-if="errors.password_confirmation" class="error-text">{{ errors.password_confirmation[0]
                            }}</small>
                    </div>

                    <Button type="submit" label="Redefinir Senha" class="reset-btn" icon="pi pi-check"
                        :loading="loading" :disabled="!canSubmit" />
                </form>
            </div>

            <div class="card-footer">
                <span>Lembrou da senha? <router-link to="/login" class="login-link">Voltar ao Login</router-link></span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { api } from '../../services/api'

const router = useRouter()
const route = useRoute()
const toast = useToast()

const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
    token: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref<Record<string, string[]>>({})

// Critérios de validação de senha
const passwordCriteria = reactive({
    length: false,
    uppercase: false,
    lowercase: false,
    number: false,
    special: false
})

// Verificar se as senhas coincidem
const passwordsMatch = computed(() => {
    return form.password && form.password_confirmation && form.password === form.password_confirmation
})

// Calcular força da senha
const passwordStrength = computed(() => {
    const criteria = Object.values(passwordCriteria)
    const validCriteria = criteria.filter(Boolean).length
    return validCriteria
})

const passwordStrengthPercentage = computed(() => {
    return (passwordStrength.value / 5) * 100
})

const passwordStrengthClass = computed(() => {
    const strength = passwordStrength.value
    if (strength <= 1) return 'weak'
    if (strength <= 2) return 'fair'
    if (strength <= 3) return 'good'
    if (strength <= 4) return 'strong'
    return 'excellent'
})

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value
    if (strength <= 1) return 'Muito fraca'
    if (strength <= 2) return 'Fraca'
    if (strength <= 3) return 'Regular'
    if (strength <= 4) return 'Forte'
    return 'Muito forte'
})

const canSubmit = computed(() => {
    return passwordsMatch.value && passwordStrength.value >= 3 && form.token && form.email
})

// Função para validar senha
const validatePassword = () => {
    const password = form.password

    passwordCriteria.length = password.length >= 8
    passwordCriteria.uppercase = /[A-Z]/.test(password)
    passwordCriteria.lowercase = /[a-z]/.test(password)
    passwordCriteria.number = /\d/.test(password)
    passwordCriteria.special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
}

const handleResetPassword = async () => {
    errors.value = {}
    loading.value = true

    // Validações frontend
    if (!passwordsMatch.value) {
        errors.value.password_confirmation = ['As senhas não coincidem']
        loading.value = false
        return
    }

    if (passwordStrength.value < 3) {
        errors.value.password = ['A senha deve atender a pelo menos 3 critérios de segurança']
        loading.value = false
        return
    }

    try {
        const response = await api.post('/v1/auth/reset-password', {
            token: form.token,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation
        })

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: 'Senha redefinida',
                detail: 'Sua senha foi redefinida com sucesso!',
                life: 5000
            })

            // Redirecionar para login após 2 segundos
            setTimeout(() => {
                router.push('/login')
            }, 2000)
        } else {
            throw new Error(response.data.message || 'Erro ao redefinir senha')
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errorData = error.response.data?.errors || {}
            errors.value = errorData
        } else if (error.response?.status === 400) {
            toast.add({
                severity: 'error',
                summary: 'Token inválido',
                detail: 'O link de recuperação é inválido ou expirou. Solicite um novo.',
                life: 6000
            })
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erro no reset',
                detail: error.response?.data?.message || 'Erro interno do servidor',
                life: 5000
            })
        }
    } finally {
        loading.value = false
    }
}

// Extrair token e email da URL quando componente for montado
onMounted(() => {
    const token = route.query.token as string
    const email = route.query.email as string

    if (!token || !email) {
        toast.add({
            severity: 'error',
            summary: 'Link inválido',
            detail: 'O link de recuperação é inválido. Solicite um novo.',
            life: 5000
        })
        router.push('/login')
        return
    }

    form.token = token
    form.email = email
})
</script>

<style scoped>
/* Reutilizar estilos similares ao LoginView */
.reset-password-container {
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

.reset-password-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(16, 185, 129, 0.2);
    width: 480px;
    max-width: 90vw;
    overflow: hidden;
    position: relative;
    z-index: 10;
}

.card-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    padding: 2rem 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
    position: relative;
}

.logo-container {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

.logo-circle {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    backdrop-filter: blur(15px);
    border: 3px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.logo-icon {
    color: white;
    font-size: 2rem;
}

.logo-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    color: white;
    font-size: 1rem;
    background: rgba(0, 0, 0, 0.25);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    margin: 0 0 0.25rem 0;
    text-align: center;
}

.app-subtitle {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    text-align: center;
}

.card-body {
    padding: 2rem;
}

.reset-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    font-size: 1rem;
    z-index: 2;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    z-index: 2;
}

.password-toggle:hover {
    color: #6b7280;
    background: rgba(0, 0, 0, 0.05);
}

.form-input {
    width: 100% !important;
    padding: 0.875rem 2.75rem !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    font-size: 0.95rem !important;
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

/* Estilos de validação de senha */
.password-strength {
    margin-top: 0.75rem;
}

.strength-bar {
    width: 100%;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.weak {
    background: #ef4444;
}

.strength-fill.fair {
    background: #f97316;
}

.strength-fill.good {
    background: #eab308;
}

.strength-fill.strong {
    background: #22c55e;
}

.strength-fill.excellent {
    background: #10b981;
}

.strength-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.strength-label span.weak {
    color: #ef4444;
    font-weight: 600;
}

.strength-label span.fair {
    color: #f97316;
    font-weight: 600;
}

.strength-label span.good {
    color: #eab308;
    font-weight: 600;
}

.strength-label span.strong {
    color: #22c55e;
    font-weight: 600;
}

.strength-label span.excellent {
    color: #10b981;
    font-weight: 600;
}

.password-criteria,
.password-match {
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.criteria-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
}

.criteria-item:last-child {
    margin-bottom: 0;
}

.criteria-item.valid {
    color: #10b981;
}

.criteria-item.valid i {
    color: #10b981;
}

.criteria-item:not(.valid) i {
    color: #ef4444;
}

.criteria-item i {
    font-size: 0.75rem;
    width: 12px;
    height: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.error-text {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.reset-btn {
    width: 100% !important;
    padding: 1rem 2rem !important;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 1.1rem !important;
    font-weight: 600 !important;
    margin-top: 1rem !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35) !important;
}

.reset-btn:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.45) !important;
}

.reset-btn:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    transform: none !important;
}

.card-footer {
    padding: 1.5rem 2rem;
    text-align: center;
    border-top: 1px solid #e5e7eb;
    font-size: 0.9rem;
    color: #6b7280;
    background: #f9fafb;
}

.login-link {
    color: #10b981;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.login-link:hover {
    color: #059669;
    text-decoration: underline;
}
</style>