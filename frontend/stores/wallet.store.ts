import { defineStore } from 'pinia'
import { normalizeApiError } from '~/composables/useApi'
import type {
  ApiClientError,
  Dashboard,
  DashboardResponse,
  Transaction,
  TransactionFilters,
  TransactionHistoryResponse,
  ValidationErrors,
  Wallet,
  WalletOperationData,
  WalletOperationPayload,
  WalletOperationResponse,
  WalletResponse,
  PaginationMeta,
} from '~/types/api'

export const useWalletStore = defineStore('wallet', () => {
  const wallet = ref<Wallet | null>(null)
  const dashboard = ref<Dashboard | null>(null)
  const transactions = ref<Transaction[]>([])
  const pagination = ref<PaginationMeta | null>(null)
  const loading = ref(false)
  const operationLoading = ref(false)
  const error = ref<string | null>(null)
  const successMessage = ref<string | null>(null)
  const validationErrors = ref<ValidationErrors | null>(null)

  async function fetchWallet(): Promise<Wallet> {
    loading.value = true
    resetFeedback()

    try {
      const response = await useApi()<WalletResponse>('/wallet')

      wallet.value = response.data.wallet

      return response.data.wallet
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      loading.value = false
    }
  }

  async function fetchDashboard(): Promise<Dashboard> {
    loading.value = true
    resetFeedback()

    try {
      const response = await useApi()<DashboardResponse>('/dashboard')

      dashboard.value = response.data

      if (wallet.value) {
        wallet.value = {
          ...wallet.value,
          balance_cents: response.data.wallet.balance_cents,
        }
      }

      return response.data
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      loading.value = false
    }
  }

  async function deposit(payload: WalletOperationPayload): Promise<WalletOperationData> {
    return performWalletOperation('/wallet/deposit', payload)
  }

  async function withdraw(payload: WalletOperationPayload): Promise<WalletOperationData> {
    return performWalletOperation('/wallet/withdraw', payload)
  }

  async function fetchTransactions(filters: TransactionFilters = {}): Promise<Transaction[]> {
    loading.value = true
    resetFeedback()

    try {
      const response = await useApi()<TransactionHistoryResponse>('/transactions', {
        query: cleanFilters(filters),
      })

      transactions.value = response.data.transactions
      pagination.value = response.data.pagination

      return response.data.transactions
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      loading.value = false
    }
  }

  async function performWalletOperation(
    endpoint: '/wallet/deposit' | '/wallet/withdraw',
    payload: WalletOperationPayload,
  ): Promise<WalletOperationData> {
    operationLoading.value = true
    resetFeedback()

    try {
      const response = await useApi()<WalletOperationResponse>(endpoint, {
        method: 'POST',
        body: payload,
      })

      wallet.value = response.data.wallet
      successMessage.value = response.message ?? null

      return response.data
    } catch (caughtError) {
      throw setApiError(caughtError)
    } finally {
      operationLoading.value = false
    }
  }

  function resetState() {
    wallet.value = null
    dashboard.value = null
    transactions.value = []
    pagination.value = null
    resetFeedback()
  }

  function resetFeedback() {
    error.value = null
    successMessage.value = null
    validationErrors.value = null
  }

  function setApiError(caughtError: unknown): ApiClientError {
    const apiError = normalizeApiError(caughtError)

    error.value = apiError.message
    validationErrors.value = apiError.validationErrors ?? null

    return apiError
  }

  return {
    wallet,
    dashboard,
    transactions,
    pagination,
    loading,
    operationLoading,
    error,
    successMessage,
    validationErrors,
    fetchWallet,
    fetchDashboard,
    deposit,
    withdraw,
    fetchTransactions,
    resetState,
    resetFeedback,
  }
})

function cleanFilters(filters: TransactionFilters): Partial<TransactionFilters> {
  return Object.fromEntries(
    Object.entries(filters).filter(([, value]) => value !== undefined && value !== null && value !== ''),
  ) as Partial<TransactionFilters>
}
