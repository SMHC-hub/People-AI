import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'
import * as authApi from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const response = await authApi.login({ email, password })
      const tokenVal = response.access_token || (response as any).token || (response as any)?.data?.token
      const userVal = response.user || (response as any)?.data?.user
      token.value = tokenVal
      user.value = userVal
      if (tokenVal) {
        localStorage.setItem('auth_token', tokenVal)
      }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function fetchUser() {
    if (!token.value) return
    loading.value = true
    try {
      user.value = await authApi.getMe()
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    login,
    logout,
    fetchUser,
  }
})
