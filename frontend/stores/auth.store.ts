import { defineStore } from 'pinia'

type AuthUser = {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value && user.value))

  function setSession(nextToken: string, nextUser: AuthUser) {
    token.value = nextToken
    user.value = nextUser
  }

  function clearSession() {
    token.value = null
    user.value = null
  }

  return {
    token,
    user,
    isAuthenticated,
    setSession,
    clearSession,
  }
})
