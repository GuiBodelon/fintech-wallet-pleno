<template>
  <section class="flex flex-col gap-6">
    <div>
      <p class="text-sm font-medium text-emerald-700">
        Carteira
      </p>
      <h2 class="mt-1 text-2xl font-bold text-slate-950">
        Depositar / Sacar
      </h2>
      <p class="mt-1 text-sm text-slate-500">
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
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-medium text-slate-600">
            Saldo atual
          </p>
          <p class="mt-2 text-3xl font-bold text-slate-950">
            {{ formatCents(currentBalanceCents) }}
          </p>
          <p class="mt-1 text-sm text-slate-500">
            Disponível para uso
          </p>
        </div>

        <AppButton
          variant="secondary"
          size="sm"
          :loading="walletStore.loading"
          @click="loadWalletState"
        >
          Atualizar saldo
        </AppButton>
      </div>
    </AppCard>

    <div class="grid gap-6 lg:grid-cols-2">
      <AppCard
        title="Depositar"
        description="Adicione dinheiro a sua carteira."
      >
        <form
          class="flex flex-col gap-4"
          @submit.prevent="handleDeposit"
        >
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

          <AppInput
            v-model="depositForm.amount"
            label="Valor"
            name="deposit_amount"
            inputmode="decimal"
            placeholder="100,00"
            :disabled="walletStore.operationLoading"
            :error="depositAmountError"
          />

          <p class="text-sm text-slate-500">
            Valor mínimo: R$ 0,01
          </p>

          <AppButton
            type="submit"
            size="lg"
            :loading="walletStore.operationLoading"
          >
            Depositar
          </AppButton>
        </form>
      </AppCard>

      <AppCard
        title="Sacar"
        description="Retire dinheiro da sua carteira."
      >
        <form
          class="flex flex-col gap-4"
          @submit.prevent="handleWithdraw"
        >
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

          <AppInput
            v-model="withdrawForm.amount"
            label="Valor"
            name="withdraw_amount"
            inputmode="decimal"
            placeholder="49,90"
            :disabled="walletStore.operationLoading"
            :error="withdrawAmountError"
          />

          <p class="text-sm text-slate-500">
            Saldo disponível: {{ formatCents(currentBalanceCents) }}
          </p>

          <AppButton
            type="submit"
            variant="danger"
            size="lg"
            :loading="walletStore.operationLoading"
          >
            Sacar
          </AppButton>
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
import AppInput from '~/components/ui/AppInput.vue'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth',
})

const walletStore = useWalletStore()
const { formatCents, parseCurrencyToCents } = useMoney()

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

onMounted(() => {
  void loadWalletState()
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
</script>
