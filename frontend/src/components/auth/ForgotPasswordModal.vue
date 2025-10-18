<template>
    <Dialog v-model:visible="isVisible" modal closable class="forgot-password-dialog" :style="{ width: '420px' }"
        @update:visible="$emit('update:visible', $event)">
        <template #header>
            <div class="dialog-header-content">
                <i class="fas fa-key"></i>
                <span>Recuperar Senha</span>
            </div>
        </template>

        <div v-if="!emailSent" class="forgot-password-form">
            <p class="description">
                Digite seu e-mail abaixo e enviaremos um link para redefinir sua senha.
            </p>

            <div class="form-group">
                <label for="forgot-email" class="form-label">E-mail</label>
                <div class="input-wrapper">
                    <i class="pi pi-envelope input-icon"></i>
                    <InputText id="forgot-email" v-model="email" type="email" placeholder="Digite seu e-mail cadastrado"
                        class="form-input"
                        :class="{ 'invalid': email && !emailIsValid, 'valid': email && emailIsValid }" required
                        @input="validateEmail" @blur="validateEmail" />
                    <div v-if="email" class="email-status">
                        <i :class="emailIsValid ? 'pi pi-check email-valid' : 'pi pi-times email-invalid'"></i>
                    </div>
                </div>

                <div v-if="email && !emailIsValid" class="email-feedback">
                    <div class="criteria-item invalid">
                        <i class="pi pi-times"></i>
                        Por favor, digite um email válido
                    </div>
                </div>

                <small v-if="errors.email" class="error-text">{{ errors.email[0] }}</small>
            </div>
        </div>

        <!-- Mensagem de sucesso -->
        <div v-else class="success-message">
            <div class="success-icon">
                <i class="pi pi-check-circle"></i>
            </div>
            <h3>E-mail enviado!</h3>
            <p>
                Enviamos um link de recuperação para <strong>{{ email }}</strong>.
                Verifique sua caixa de entrada e spam.
            </p>
            <div class="resend-info">
                <span>Não recebeu o e-mail? </span>
                <button type="button" class="resend-link" @click="resendEmail" :disabled="resendCooldown > 0">
                    {{ resendCooldown > 0 ? `Reenviar em ${resendCooldown}s` : 'Reenviar' }}
                </button>
            </div>
        </div>

        <template #footer>
            <div class="dialog-footer">
                <Button v-if="!emailSent" label="Cancelar" class="cancel-btn" @click="handleCancel" icon="pi pi-times"
                    severity="secondary" outlined />
                <Button v-if="!emailSent" label="Enviar Link" class="send-btn" @click="handleSendResetLink"
                    icon="pi pi-send" :loading="loading" :disabled="!emailIsValid" />
                <Button v-else label="Voltar ao Login" class="back-btn" @click="handleCancel" icon="pi pi-arrow-left" />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
import { api } from '../../services/api'

interface Props {
    visible: boolean
}

interface Emits {
    (e: 'update:visible', value: boolean): void
    (e: 'success'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const toast = useToast()

const isVisible = ref(props.visible)
const loading = ref(false)
const emailSent = ref(false)
const email = ref('')
const emailIsValid = ref(false)
const resendCooldown = ref(0)

const errors = ref<Record<string, string[]>>({})

// Sincronizar prop com ref local
watch(() => props.visible, (newValue) => {
    isVisible.value = newValue
    if (newValue) {
        // Reset state when opening
        emailSent.value = false
        email.value = ''
        emailIsValid.value = false
        errors.value = {}
    }
})

const validateEmail = () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    const emailValue = email.value.trim()

    if (!emailValue) {
        emailIsValid.value = false
        return
    }

    const isValidFormat = emailRegex.test(emailValue)
    const noConsecutiveDots = !emailValue.includes('..')
    const noStartEndDots = !emailValue.startsWith('.') && !emailValue.endsWith('.')
    const hasAtSymbol = emailValue.split('@').length === 2

    emailIsValid.value = isValidFormat && noConsecutiveDots && noStartEndDots && hasAtSymbol && emailValue.length >= 5
}

const handleCancel = () => {
    email.value = ''
    emailIsValid.value = false
    emailSent.value = false
    errors.value = {}
    emit('update:visible', false)
}

const handleSendResetLink = async () => {
    if (!emailIsValid.value) {
        errors.value.email = ['Por favor, digite um email válido']
        return
    }

    errors.value = {}
    loading.value = true

    try {
        const response = await api.post('/v1/auth/forgot-password', {
            email: email.value
        })

        if (response.data.success) {
            emailSent.value = true
            toast.add({
                severity: 'success',
                summary: 'E-mail enviado',
                detail: 'Link de recuperação enviado com sucesso!',
                life: 5000
            })
        } else {
            throw new Error(response.data.message || 'Erro ao enviar email')
        }
    } catch (error: any) {
        if (error.response?.status === 422) {
            const errorData = error.response.data?.errors || {}
            errors.value = errorData
        } else if (error.response?.status === 404) {
            errors.value.email = ['Este e-mail não está cadastrado no sistema']
        } else if (error.response?.status === 429) {
            toast.add({
                severity: 'error',
                summary: 'Muitas tentativas',
                detail: 'Aguarde alguns minutos antes de tentar novamente',
                life: 5000
            })
        } else {
            toast.add({
                severity: 'error',
                summary: 'Erro no envio',
                detail: error.response?.data?.message || 'Erro interno do servidor',
                life: 5000
            })
        }
    } finally {
        loading.value = false
    }
}

const resendEmail = async () => {
    // Cooldown de 60 segundos
    resendCooldown.value = 60
    const countdown = setInterval(() => {
        resendCooldown.value--
        if (resendCooldown.value <= 0) {
            clearInterval(countdown)
        }
    }, 1000)

    await handleSendResetLink()
}
</script>

<style scoped>
/* DIALOG DE RECUPERAÇÃO DE SENHA */
.forgot-password-dialog {
    border-radius: 20px !important;
}

.forgot-password-dialog .p-dialog-header {
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

.forgot-password-dialog .p-dialog-header-icon {
    color: white !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 6px !important;
    width: 32px !important;
    height: 32px !important;
    transition: all 0.2s ease !important;
}

.forgot-password-dialog .p-dialog-header-icon:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    transform: scale(1.05) !important;
}

.forgot-password-dialog .p-dialog-content {
    padding: 1.5rem !important;
}

.description {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
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

.form-input.valid {
    border-color: #10b981 !important;
    background: #f0fdf4 !important;
}

.form-input.invalid {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
}

.form-input:focus {
    border-color: #10b981 !important;
    background: white !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
    outline: none !important;
}

.email-feedback {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #fef2f2;
    border-radius: 6px;
    border: 1px solid #fecaca;
}

.criteria-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
    transition: all 0.2s ease;
}

.criteria-item.invalid {
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

/* Mensagem de Sucesso */
.success-message {
    text-align: center;
    padding: 1rem 0;
}

.success-icon {
    margin-bottom: 1rem;
}

.success-icon i {
    font-size: 3rem;
    color: #10b981;
}

.success-message h3 {
    color: #374151;
    margin-bottom: 1rem;
    font-size: 1.25rem;
    font-weight: 600;
}

.success-message p {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.resend-info {
    font-size: 0.875rem;
    color: #6b7280;
}

.resend-link {
    background: none;
    border: none;
    color: #10b981;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.2s;
}

.resend-link:hover:not(:disabled) {
    color: #059669;
}

.resend-link:disabled {
    color: #9ca3af;
    cursor: not-allowed;
    text-decoration: none;
}

/* Footer */
.dialog-footer {
    display: flex;
    gap: 1rem;
    justify-content: center;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
    margin-top: 1.5rem;
}

.cancel-btn,
.back-btn {
    padding: 0.875rem 1.5rem !important;
    border-radius: 12px !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    border: 2px solid #d1d5db !important;
    background: white !important;
    color: #6b7280 !important;
    transition: all 0.2s ease !important;
}

.cancel-btn:hover,
.back-btn:hover {
    border-color: #9ca3af !important;
    background: #f9fafb !important;
    color: #374151 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.send-btn {
    padding: 0.875rem 1.5rem !important;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
    transition: all 0.2s ease !important;
}

.send-btn:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
}

.send-btn:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    transform: none !important;
}
</style>