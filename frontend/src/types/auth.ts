export interface LoginForm {
  email: string
  password: string
}

export interface RegisterForm {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  success: boolean
  message?: string
  errors?: Record<string, string[]>
  data?: {
    user: User
    token: string
  }
}

export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface ValidationErrors {
  [key: string]: string[]
}