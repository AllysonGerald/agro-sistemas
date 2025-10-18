import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/services/api'

export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

export interface LoginResponse {
  success: boolean
  message: string
  data: {
    user: User
    access_token: string
    token_type: string
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  const setAuth = (userData: User, authToken: string) => {
    user.value = userData
    token.value = authToken
    localStorage.setItem('token', authToken)
    api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
    delete api.defaults.headers.common['Authorization']
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    try {
      const response = await api.post<LoginResponse>('/v1/auth/login', {
        email,
        password
      })

      if (response.data.success) {
        setAuth(response.data.data.user, response.data.data.access_token)
        return { success: true, message: response.data.message }
      } else {
        return { success: false, message: response.data.message }
      }
    } catch (error: any) {
      // Tratamento específico para diferentes tipos de erro
      if (error.response?.status === 422) {
        const errors = error.response.data?.errors || {}
        const message = error.response.data?.message || 'Dados inválidos'
        return { success: false, message, errors }
      } else if (error.response?.status === 429) {
        return { success: false, message: 'Muitas tentativas. Tente novamente em alguns minutos.' }
      } else if (error.response?.status === 401) {
        return { success: false, message: 'Credenciais inválidas' }
      } else if (error.code === 'NETWORK_ERROR' || !error.response) {
        return { success: false, message: 'Erro de conexão. Verifique sua internet.' }
      }
      
      const message = error.response?.data?.message || 'Erro interno do servidor'
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const register = async (userData: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) => {
    loading.value = true
    try {
      const response = await api.post<LoginResponse>('/v1/auth/register', userData)

      if (response.data.success) {
        setAuth(response.data.data.user, response.data.data.access_token)
        return { success: true, message: response.data.message }
      } else {
        return { success: false, message: response.data.message }
      }
    } catch (error: any) {
      // Tratamento específico para diferentes tipos de erro
      if (error.response?.status === 422) {
        const errors = error.response.data?.errors || {}
        const message = error.response.data?.message || 'Dados inválidos'
        return { success: false, message, errors }
      } else if (error.response?.status === 409) {
        return { success: false, message: 'E-mail já está em uso' }
      } else if (error.response?.status === 429) {
        return { success: false, message: 'Muitas tentativas. Tente novamente em alguns minutos.' }
      } else if (error.code === 'NETWORK_ERROR' || !error.response) {
        return { success: false, message: 'Erro de conexão. Verifique sua internet.' }
      }
      
      const message = error.response?.data?.message || 'Erro interno do servidor'
      const errors = error.response?.data?.errors || {}
      return { success: false, message, errors }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      await api.post('/v1/auth/logout')
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      clearAuth()
      loading.value = false
    }
  }

  const checkAuth = async () => {
    if (token.value && !user.value) {
      try {
        api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
        const response = await api.get('/v1/auth/user')
        if (response.data.success) {
          user.value = response.data.data
        } else {
          clearAuth()
        }
      } catch (error) {
        clearAuth()
      }
    }
  }

  return {
    user: computed(() => user.value),
    token: computed(() => token.value),
    loading: computed(() => loading.value),
    isAuthenticated,
    login,
    register,
    logout,
    checkAuth,
    setAuth,
    clearAuth
  }
})