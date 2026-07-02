export type ValidationErrors = Record<string, string[]>

export type ApiSuccessResponse<T> = {
  success: true
  message?: string
  data: T
}

export type ApiErrorResponse = {
  success: false
  message: string
  errors?: ValidationErrors
}

export type ApiResponse<T> = ApiSuccessResponse<T> | ApiErrorResponse

export type ApiClientError = Error & {
  status?: number
  validationErrors?: ValidationErrors
  response?: ApiErrorResponse
}

export type User = {
  id: number
  name: string
  email: string
}

export type Wallet = {
  id: number
  balance_cents: number
}

export type TransactionType = 'credit' | 'debit'

export type Transaction = {
  id: number
  occurred_at: string | null
  type: TransactionType
  amount_cents: number
  balance_after_cents: number
}

export type Dashboard = {
  wallet: {
    balance_cents: number
  }
  last_transactions: Transaction[]
  current_month: {
    deposited_cents: number
    withdrawn_cents: number
  }
}

export type PaginationMeta = {
  current_page: number
  per_page: number
  total: number
  last_page: number
  from: number | null
  to: number | null
}

export type PaginatedResponse<T> = {
  items: T[]
  pagination: PaginationMeta
}

export type TransactionHistoryData = {
  transactions: Transaction[]
  pagination: PaginationMeta
}

export type TransactionFilters = {
  type?: TransactionType
  from?: string
  to?: string
  page?: number
  per_page?: number
}

export type WalletData = {
  wallet: Wallet
}

export type WalletOperationPayload = {
  amount: string
}

export type WalletOperationData = {
  wallet: Wallet
  transaction: Transaction
}

export type LoginPayload = {
  email: string
  password: string
}

export type RegisterPayload = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type AuthSessionData = {
  user: User
  token: string
}

export type MeData = {
  user: User
}

export type LoginResponse = ApiSuccessResponse<AuthSessionData>
export type RegisterResponse = ApiSuccessResponse<AuthSessionData>
export type LogoutResponse = ApiSuccessResponse<null>
export type MeResponse = ApiSuccessResponse<MeData>
export type WalletResponse = ApiSuccessResponse<WalletData>
export type WalletOperationResponse = ApiSuccessResponse<WalletOperationData>
export type DashboardResponse = ApiSuccessResponse<Dashboard>
export type TransactionHistoryResponse = ApiSuccessResponse<TransactionHistoryData>
