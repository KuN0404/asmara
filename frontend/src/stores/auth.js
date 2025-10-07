import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)

  const hasRole = (role) => {
    if (!user.value?.roles) return false
    return user.value.roles.some((r) => r.name === role)
  }

  const setToken = (newToken) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
  }

  const setUser = (userData) => {
    user.value = userData
  }

  const login = async (credentials) => {
    try {
      const data = await authService.login(credentials)
      setToken(data.token)
      setUser(data.user)
      return data
    } catch (error) {
      throw error
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
    }
  }

  const checkAuth = async () => {
    if (!token.value) return

    try {
      const data = await authService.me()
      setUser(data.user)
    } catch (error) {
      logout()
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    hasRole,
    login,
    logout,
    checkAuth,
  }
})
