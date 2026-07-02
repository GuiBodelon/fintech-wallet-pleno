import type { ApiClientError, ApiErrorResponse } from '~/types/api'
import { getStoredAuthToken } from '~/utils/authToken'

type FetchErrorLike = {
  message?: string
  status?: number
  statusCode?: number
  data?: unknown
  response?: {
    status?: number
    _data?: unknown
  }
}

export function useApi() {
  const config = useRuntimeConfig()

  return $fetch.create({
    baseURL: config.public.apiBaseUrl,
    onRequest({ options }) {
      const headers = new Headers(options.headers as HeadersInit)
      const token = getStoredAuthToken()

      headers.set('Accept', 'application/json')

      if (token) {
        headers.set('Authorization', `Bearer ${token}`)
      }

      options.headers = headers
    },
    onResponseError({ response }) {
      throw normalizeApiError({ response })
    },
  })
}

export function normalizeApiError(error: unknown): ApiClientError {
  if (isApiClientError(error)) {
    return error
  }

  const fetchError = error as FetchErrorLike
  const responseData = fetchError.response?._data ?? fetchError.data
  const apiError = isApiErrorResponse(responseData) ? responseData : undefined
  const status = fetchError.response?.status ?? fetchError.status ?? fetchError.statusCode
  const message = apiError?.message ?? fetchError.message ?? 'Unexpected API error.'
  const normalizedError = new Error(message) as ApiClientError

  normalizedError.name = 'ApiClientError'
  normalizedError.status = status
  normalizedError.validationErrors = apiError?.errors
  normalizedError.response = apiError

  return normalizedError
}

function isApiClientError(error: unknown): error is ApiClientError {
  return error instanceof Error && error.name === 'ApiClientError'
}

function isApiErrorResponse(value: unknown): value is ApiErrorResponse {
  if (!value || typeof value !== 'object') {
    return false
  }

  const candidate = value as Partial<ApiErrorResponse>

  return candidate.success === false && typeof candidate.message === 'string'
}
