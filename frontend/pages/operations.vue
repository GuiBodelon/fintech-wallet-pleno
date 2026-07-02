<template>
  <section class="flex flex-col gap-6">
    <div>
      <p class="text-sm font-semibold text-emerald-700">
        Carteira
      </p>
      <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
        Depositar / Sacar
      </h2>
      <p class="mt-2 text-sm text-slate-500">
        Gerencie o saldo da sua carteira.
      </p>
    </div>

    <AppAlert
      v-if="pageError"
      variant="error"
    >
      {{ pageError }}
    </AppAlert>

    <AppCard>
      <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
          <div class="flex size-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
            <UIcon
              name="i-lucide-wallet"
              class="size-8"
            />
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-600">
              Saldo atual
            </p>
            <p class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
              {{ formatCents(currentBalanceCents) }}
            </p>
            <p class="mt-1 text-sm text-slate-500">
              Disponível para uso
            </p>
          </div>
        </div>

        <AppButton
          variant="secondary"
          size="sm"
          :loading="walletStore.loading"
          @click="loadWalletState"
        >
          <span class="flex items-center gap-2">
            <UIcon
              name="i-lucide-refresh-cw"
              class="size-4"
            />
            Atualizar saldo
          </span>
        </AppButton>
      </div>
    </AppCard>

    <div class="grid gap-6 lg:grid-cols-2">
      <AppCard>
        <form
          ref="depositFormRef"
          class="flex flex-col gap-5"
          @submit.prevent="handleDeposit"
        >
          <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
            <div class="flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
              <UIcon
                name="i-lucide-arrow-down"
                class="size-7"
              />
            </div>
            <div>
              <h3 class="text-xl font-bold text-emerald-700">
                Depositar
              </h3>
              <p class="mt-1 text-sm text-slate-500">
                Adicione dinheiro à sua carteira.
              </p>
            </div>
          </div>

          <AppAlert
            v-if="depositSuccess"
            variant="success"
          >
            {{ depositSuccess }}
          </AppAlert>

          <AppAlert
            v-if="depositGeneralError"
            variant="error"
          >
            {{ depositGeneralError }}
          </AppAlert>

          <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Valor</span>
            <div :class="amountInputWrapperClasses(depositAmountError, 'deposit')">
              <span class="flex h-11 items-center border-r border-slate-200 px-4 text-sm font-semibold text-slate-500">
                R$
              </span>
              <input
                v-model="depositForm.amount"
                name="deposit_amount"
                inputmode="decimal"
                placeholder="100,00"
                :disabled="walletStore.operationLoading"
                :aria-invalid="Boolean(depositAmountError)"
                class="h-11 min-w-0 flex-1 bg-transparent px-4 text-sm text-slate-950 outline-none placeholder:text-slate-400 disabled:cursor-not-allowed disabled:text-slate-500"
              >
            </div>
            <span
              v-if="depositAmountError"
              class="text-sm font-medium text-rose-600"
            >
              {{ depositAmountError }}
            </span>
          </label>

          <p class="text-sm text-slate-500">
            Valor mínimo: R$ 0,01
          </p>

          <AppButton
            class="w-full"
            type="submit"
            size="lg"
            :loading="walletStore.operationLoading"
          >
            Depositar
          </AppButton>

          <p class="flex items-center justify-center gap-2 text-sm text-slate-400">
            <UIcon
              name="i-lucide-shield-check"
              class="size-4"
            />
            Operação segura e protegida
          </p>
        </form>
      </AppCard>

      <AppCard>
        <form
          ref="withdrawFormRef"
          class="flex flex-col gap-5"
          @submit.prevent="handleWithdraw"
        >
          <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
            <div class="flex size-14 items-center justify-center rounded-full bg-rose-50 text-rose-700">
              <UIcon
                name="i-lucide-arrow-up"
                class="size-7"
              />
            </div>
            <div>
              <h3 class="text-xl font-bold text-rose-700">
                Sacar
              </h3>
              <p class="mt-1 text-sm text-slate-500">
                Retire dinheiro da sua carteira.
              </p>
            </div>
          </div>

          <AppAlert
            v-if="withdrawSuccess"
            variant="success"
          >
            {{ withdrawSuccess }}
          </AppAlert>

          <AppAlert
            v-if="withdrawGeneralError"
            variant="error"
          >
            {{ withdrawGeneralError }}
          </AppAlert>

          <label class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-slate-700">Valor</span>
            <div :class="amountInputWrapperClasses(withdrawAmountError, 'withdraw')">
              <span class="flex h-11 items-center border-r border-slate-200 px-4 text-sm font-semibold text-slate-500">
                R$
              </span>
              <input
                v-model="withdrawForm.amount"
                name="withdraw_amount"
                inputmode="decimal"
                placeholder="49,90"
                :disabled="walletStore.operationLoading"
                :aria-invalid="Boolean(withdrawAmountError)"
                class="h-11 min-w-0 flex-1 bg-transparent px-4 text-sm text-slate-950 outline-none placeholder:text-slate-400 disabled:cursor-not-allowed disabled:text-slate-500"
              >
            </div>
            <span
              v-if="withdrawAmountError"
              class="text-sm font-medium text-rose-600"
            >
              {{ withdrawAmountError }}
            </span>
          </label>

          <p class="text-sm text-slate-500">
            Saldo disponível: {{ formatCents(currentBalanceCents) }}
          </p>

          <AppButton
            class="w-full"
            type="submit"
            variant="danger"
            size="lg"
            :loading="walletStore.operationLoading"
          >
            Sacar
          </AppButton>

          <p class="flex items-center justify-center gap-2 text-sm text-slate-400">
            <UIcon
              name="i-lucide-shield-check"
              class="size-4"
            />
            Operação segura e protegida
          </p>
        </form>
      </AppCard>
    </div>

    <AppAlert variant="info">
      Depósitos são processados imediatamente. Saques dependem da validação do saldo disponível.
    </AppAlert>
  </section>
</template>

<script setup lang="ts">
import AppAlert from '~/components/ui/AppAlert.vue'
import AppButton from '~/components/ui/AppButton.vue'
import AppCard from '~/components/ui/AppCard.vue'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth',
})

const route = useRoute()
const walletStore = useWalletStore()
const { formatCents, parseCurrencyToCents } = useMoney()

const depositFormRef = ref<HTMLFormElement | null>(null)
const withdrawFormRef = ref<HTMLFormElement | null>(null)

const depositForm = reactive({
  amount: '',
})
const withdrawForm = reactive({
  amount: '',
})

const pageError = ref<string | null>(null)
const depositSuccess = ref<string | null>(null)
const depositGeneralError = ref<string | null>(null)
const depositLocalAmountError = ref<string | null>(null)
const depositApiAmountError = ref<string | null>(null)
const withdrawSuccess = ref<string | null>(null)
const withdrawGeneralError = ref<string | null>(null)
const withdrawLocalAmountError = ref<string | null>(null)
const withdrawApiAmountError = ref<string | null>(null)

const currentBalanceCents = computed(() => (
  walletStore.wallet?.balance_cents
  ?? walletStore.dashboard?.wallet.balance_cents
  ?? 0
))

const depositAmountError = computed(() => depositLocalAmountError.value ?? depositApiAmountError.value ?? undefined)
const withdrawAmountError = computed(() => withdrawLocalAmountError.value ?? withdrawApiAmountError.value ?? undefined)

onMounted(async () => {
  await loadWalletState()
  focusOperationFromRoute()
})

watch(() => route.query.tab, () => {
  focusOperationFromRoute()
})

async function loadWalletState() {
  pageError.value = null

  try {
    await walletStore.fetchDashboard()
  } catch {
    pageError.value = walletStore.error
  }
}

async function handleDeposit() {
  resetDepositFeedback()

  const amountCents = parseCurrencyToCents(depositForm.amount)

  if (!amountCents || amountCents <= 0) {
    depositLocalAmountError.value = 'Informe um valor válido maior que zero.'
    return
  }

  try {
    await walletStore.deposit({ amount: depositForm.amount.trim() })
    depositSuccess.value = walletStore.successMessage ?? 'Depósito realizado com sucesso.'
    depositForm.amount = ''
    await walletStore.fetchDashboard()
  } catch {
    depositApiAmountError.value = walletStore.validationErrors?.amount?.[0] ?? null
    depositGeneralError.value = depositApiAmountError.value ? null : walletStore.error
  }
}

async function handleWithdraw() {
  resetWithdrawFeedback()

  const amountCents = parseCurrencyToCents(withdrawForm.amount)

  if (!amountCents || amountCents <= 0) {
    withdrawLocalAmountError.value = 'Informe um valor válido maior que zero.'
    return
  }

  if (amountCents > currentBalanceCents.value) {
    withdrawLocalAmountError.value = 'Saldo insuficiente para este saque.'
    return
  }

  try {
    await walletStore.withdraw({ amount: withdrawForm.amount.trim() })
    withdrawSuccess.value = walletStore.successMessage ?? 'Saque realizado com sucesso.'
    withdrawForm.amount = ''
    await walletStore.fetchDashboard()
  } catch {
    withdrawApiAmountError.value = walletStore.validationErrors?.amount?.[0] ?? null
    withdrawGeneralError.value = withdrawApiAmountError.value ? null : walletStore.error
  }
}

function resetDepositFeedback() {
  depositSuccess.value = null
  depositGeneralError.value = null
  depositLocalAmountError.value = null
  depositApiAmountError.value = null
}

function resetWithdrawFeedback() {
  withdrawSuccess.value = null
  withdrawGeneralError.value = null
  withdrawLocalAmountError.value = null
  withdrawApiAmountError.value = null
}

function focusOperationFromRoute() {
  const target = route.query.tab === 'withdraw' ? withdrawFormRef.value : depositFormRef.value

  target?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function amountInputWrapperClasses(error: string | undefined, operation: 'deposit' | 'withdraw'): string {
  const focusClasses = operation === 'withdraw'
    ? 'focus-within:border-rose-500 focus-within:ring-4 focus-within:ring-rose-100'
    : 'focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100'

  return [
    'flex overflow-hidden rounded-xl border bg-white shadow-sm transition disabled:bg-slate-100',
    error ? 'border-rose-300' : 'border-slate-200',
    focusClasses,
  ].join(' ')
}
</script>
