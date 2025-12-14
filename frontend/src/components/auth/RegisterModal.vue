<template>
    <Dialog v-model:visible="isVisible" modal closable class="register-dialog" :style="{ width: '450px' }"
        @update:visible="$emit('update:visible', $event)">
        <template #header>
            <div class="dialog-header-content">
                <i class="fas fa-user-plus"></i>
                <span>Criar Nova Conta</span>
            </div>
        </template>

        <div class="register-form">
            <div class="form-group">
                <label for="register-name" class="form-label">Nome Completo</label>
                <div class="input-wrapper">
                    <i class="pi pi-user input-icon"></i>
                    <InputText id="register-name" v-model="form.name" placeholder="Digite seu nome completo"
                        class="form-input" required />
                </div>
                <small v-if="errors.name" class="error-text">{{ errors.name[0] }}</small>
            </div>

            <div class="form-group">
                <label for="register-email" class="form-label">E-mail</label>
                <div class="input-wrapper">
                    <i class="pi pi-envelope input-icon"></i>
                    <InputText id="register-email" v-model="form.email" type="email" placeholder="Digite seu e-mail"
                        class="form-input"
                        :class="{ 'invalid': form.email && !emailIsValid, 'valid': form.email && emailIsValid }"
                        required @input="validateEmail" @blur="validateEmail" />
                    <div v-if="form.email" class="email-status">
                        <i :class="emailIsValid ? 'pi pi-check email-valid' : 'pi pi-times email-invalid'"></i>
                    </div>
                </div>

                <!-- Feedback de validação de email -->
                <div v-if="form.email && !emailIsValid" class="email-feedback">
                    <div class="criteria-item invalid">
                        <i class="pi pi-times"></i>
                        Por favor, digite um email válido (exemplo@dominio.com)
                    </div>
                </div>

                <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
            </div>

            <div class="form-group">
                <label for="register-password" class="form-label">Senha</label>
                <div class="input-wrapper">
                    <i class="pi pi-lock input-icon"></i>
                    <InputText id="register-password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        placeholder="Digite sua senha (mín. 8 caracteres)" class="form-input" required
                        @input="validatePassword" />
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

            <div class="form-group">
                <label for="register-password-confirmation" class="form-label">Confirmar Senha</label>
                <div class="input-wrapper">
                    <i class="pi pi-lock input-icon"></i>
                    <InputText id="register-password-confirmation" v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirme sua senha"
                        class="form-input" required @input="validatePasswordMatch" />
                    <button type="button" class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
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
        </div>

        <template #footer>
            <div class="dialog-footer">
                <Button label="Cancelar" class="cancel-btn" @click="handleCancel" icon="pi pi-times"
                    severity="secondary" outlined />
                <Button label="Criar Conta" class="register-btn" @click="handleRegister" icon="pi pi-user-plus"
                    :loading="loading" />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import type { RegisterForm, ValidationErrors } from '@/types/auth'

interface Props {
    visible: boolean
}

interface Emits {
    (e: 'update:visible', value: boolean): void
    (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const toast = useToast()

const isVisible = ref(props.visible)
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive<RegisterForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const errors = ref<ValidationErrors>({})

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

// Validação de email
const emailIsValid = ref(false)

const validateEmail = () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    const email = form.email.trim()

    if (!email) {
        emailIsValid.value = false
        return
    }

    // Validação básica mas eficaz
    const isValidFormat = emailRegex.test(email)
    const noConsecutiveDots = !email.includes('..')
    const noStartEndDots = !email.startsWith('.') && !email.endsWith('.')
    const hasAtSymbol = email.split('@').length === 2

    emailIsValid.value = isValidFormat && noConsecutiveDots && noStartEndDots && hasAtSymbol && email.length >= 5
}

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

// Função para validar senha
const validatePassword = () => {
    const password = form.password

    passwordCriteria.length = password.length >= 8
    passwordCriteria.uppercase = /[A-Z]/.test(password)
    passwordCriteria.lowercase = /[a-z]/.test(password)
    passwordCriteria.number = /\d/.test(password)
    passwordCriteria.special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
}

// Função para validar confirmação de senha
const validatePasswordMatch = () => {
    // Esta função será chamada automaticamente pelo computed passwordsMatch
}

// Sincronizar prop com ref local
watch(() => props.visible, (newValue) => {
    isVisible.value = newValue
})

const clearForm = () => {
    form.name = ''
    form.email = ''
    form.password = ''
    form.password_confirmation = ''
    errors.value = {}

    // Resetar critérios
    passwordCriteria.length = false
    passwordCriteria.uppercase = false
    passwordCriteria.lowercase = false
    passwordCriteria.number = false
    passwordCriteria.special = false
    emailIsValid.value = false
}

const handleCancel = () => {
    clearForm()
    emit('update:visible', false)
}

const handleRegister = async () => {
    errors.value = {}
    loading.value = true

    // Validação adicional no frontend
    if (!emailIsValid.value) {
        errors.value.email = ['Por favor, digite um email válido']
        loading.value = false
        return
    }

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
        const result = await authStore.register(form)

        if (result.success) {
            toast.add({
                severity: 'success',
                summary: 'Conta criada',
                detail: 'Conta criada com sucesso! Faça login.',
                life: 3000
            })

            clearForm()
            emit('update:visible', false)
            emit('success')
        } else {
            if (result.errors) {
                errors.value = result.errors
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Erro no Registro',
                    detail: result.message || 'Erro ao criar conta',
                    life: 3000
                })
            }
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Erro interno do servidor',
            life: 3000
        })
    } finally {
        loading.value = false
    }
}
</script>
<style scoped>
/* DIALOG DE REGISTRO */
.register-dialog {
    border-radius: 20px !important;
}

.register-dialog .p-dialog-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
    border-radius: 20px 20px 0 0 !important;
    padding: 1.5rem 2rem !important;
}

.dialog-header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: white;
}

.dialog-header-content i {
    font-size: 1.4rem;
    color: white;
}

.dialog-header-content span {
    font-size: 1.4rem;
    font-weight: 600;
    color: white;
}

.register-dialog .p-dialog-header-icon {
    color: white !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 6px !important;
    width: 32px !important;
    height: 32px !important;
    transition: all 0.2s ease !important;
}

.register-dialog .p-dialog-header-icon:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    transform: scale(1.05) !important;
}

.register-dialog .p-dialog-header-icon svg {
    color: white !important;
    fill: white !important;
}

.register-dialog .p-dialog-content {
    padding: 1.5rem !important;
}

.register-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
    display: block;
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
    padding: 0.875rem 2.75rem 0.875rem 2.75rem !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    font-size: 0.95rem !important;
    transition: all 0.3s ease !important;
    background: #f9fafb !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.form-input.valid {
    border-color: #10b981 !important;
    background: #f0fdf4 !important;
}

.form-input.invalid {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
}

.email-status {
    position: absolute;
    right: 1rem;
    z-index: 2;
}

.email-valid {
    color: #10b981;
    font-size: 1rem;
}

.email-invalid {
    color: #ef4444;
    font-size: 1rem;
}

.email-feedback {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #fef2f2;
    border-radius: 6px;
    border: 1px solid #fecaca;
}

.criteria-item.invalid {
    color: #ef4444;
}

/* Validação de Senha */
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

/* Critérios de Senha */
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

.criteria-item i {
    font-size: 0.75rem;
    width: 12px;
    height: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
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

.dialog-footer {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
    margin-top: 1.5rem;
}

.cancel-btn {
    padding: 0.875rem 1.75rem !important;
    border-radius: 12px !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    border: 2px solid #d1d5db !important;
    background: white !important;
    color: #6b7280 !important;
    transition: all 0.2s ease !important;
}

.cancel-btn:hover {
    border-color: #9ca3af !important;
    background: #f9fafb !important;
    color: #374151 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.register-btn {
    padding: 0.875rem 1.75rem !important;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s ease !important;
}

.register-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
}
</style>