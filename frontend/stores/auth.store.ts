import { defineStore } from 'pinia'
import { normalizeApiError } from '~/composables/useApi'
import type {
  ApiClientError,
  AuthSessionData,
  LoginPayload,
  LoginResponse,
  MeResponse,
  RegisterPayload,
  RegisterResponse,
  User,
  ValidationErrors,
} from '~/types/api'
import { clearStoredAuthToken, getStoredAuthToken, setStoredAuthToken } from '~/utils/authToken'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)
  const loading = ref(false)
  const initialized = ref(false)
  const error = ref<string | null>(null)
  const validationErrors = ref<ValidationErrors | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value && user.value))

  async function login(payload: LoginPayload): Promise<AuthSessionData> {
    loading.value = true
    resetErrors()

    try {
      const response = await useApi()<LoginResponse>('/login', {
        method: 'POST',
        body: payload,
      })

      setSession(response.data.token, response.data.user)

      return response.data
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      loading.value = false
    }
  }

  async function register(payload: RegisterPayload): Promise<AuthSessionData> {
    loading.value = true
    resetErrors()

    try {
      const response = await useApi()<RegisterResponse>('/register', {
        method: 'POST',
        body: payload,
      })

      setSession(response.data.token, response.data.user)

      return response.data
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    loading.value = true
    resetErrors()

    let logoutError: ApiClientError | null = null

    try {
      if (token.value) {
        await useApi()('/logout', {
          method: 'POST',
        })
      }
    } catch (caughtError) {
      logoutError = setApiError(caughtError)
    } finally {
      clearSession()
      initialized.value = true
      loading.value = false
    }

    if (logoutError) {
      throw logoutError
    }
  }

  async function fetchMe(): Promise<User | null> {
    if (!token.value) {
      clearSession()
      initialized.value = true

      return null
    }

    loading.value = true
    resetErrors()

    try {
      const response = await useApi()<MeResponse>('/me')

      user.value = response.data.user
      initialized.value = true

      return response.data.user
    } catch (caughtError) {
      const apiError = setApiError(caughtError)

      if (apiError.status === 401) {
        clearSession()
        initialized.value = true
      }

      throw apiError
    } finally {
      loading.value = false
    }
  }

  async function initializeAuth(): Promise<void> {
    if (initialized.value) {
      return
    }

    const storedToken = getStoredAuthToken()

    if (!storedToken) {
      initialized.value = true
      return
    }

    token.value = storedToken

    try {
      await fetchMe()
    } catch (caughtError) {
      const apiError = normalizeApiError(caughtError)

      if (apiError.status !== 401) {
        throw apiError
      }
    } finally {
      initialized.value = true
    }
  }

  function setSession(nextToken: string, nextUser: User) {
    token.value = nextToken
    user.value = nextUser
    initialized.value = true
    setStoredAuthToken(nextToken)
    resetErrors()
  }

  function clearSession() {
    token.value = null
    user.value = null
    clearStoredAuthToken()
  }

  function resetErrors() {
    error.value = null
    validationErrors.value = null
  }

  function setApiError(caughtError: unknown): ApiClientError {
    const apiError = normalizeApiError(caughtError)

    error.value = apiError.message
    validationErrors.value = apiError.validationErrors ?? null

    return apiError
  }

  return {
    token,
    user,
    loading,
    initialized,
    error,
    validationErrors,
    isAuthenticated,
    login,
    register,
    logout,
    fetchMe,
    initializeAuth,
    setSession,
    clearSession,
  }
})
