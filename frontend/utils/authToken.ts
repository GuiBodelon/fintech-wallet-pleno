const AUTH_TOKEN_STORAGE_KEY = 'fintech_wallet_auth_token'

export function getStoredAuthToken(): string | null {
  if (!import.meta.client) {
    return null
  }

  return localStorage.getItem(AUTH_TOKEN_STORAGE_KEY)
}

export function setStoredAuthToken(token: string): void {
  if (!import.meta.client) {
    return
  }

  localStorage.setItem(AUTH_TOKEN_STORAGE_KEY, token)
}

export function clearStoredAuthToken(): void {
  if (!import.meta.client) {
    return
  }

  localStorage.removeItem(AUTH_TOKEN_STORAGE_KEY)
}
