import axios from 'axios'

export const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
})

// Interceptor para adicionar token automaticamente
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para tratar respostas
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Só redireciona se não estivermos na página de login
      if (!window.location.pathname.includes('/login')) {
        localStorage.removeItem('token')
        // Usar router em vez de window.location para evitar reload
        import('@/router').then(({ default: router }) => {
          router.push('/login')
        })
      }
    }
    return Promise.reject(error)
  }
)

export default api