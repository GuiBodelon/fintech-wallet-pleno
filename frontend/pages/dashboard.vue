<template>
  <section class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-medium text-emerald-700">
          Visao geral
        </p>
        <h2 class="mt-1 text-2xl font-bold text-slate-950">
          Dashboard
        </h2>
        <p
          v-if="authStore.user"
          class="mt-1 text-sm text-slate-500"
        >
          Ola, {{ authStore.user.name }}. Acompanhe o saldo e as movimentacoes recentes.
        </p>
      </div>

      <AppButton
        variant="secondary"
        size="sm"
        :loading="walletStore.loading"
        @click="loadDashboard"
      >
        Atualizar
      </AppButton>
    </div>

    <AppAlert
      v-if="walletStore.error"
      variant="error"
    >
      {{ walletStore.error }}
    </AppAlert>

    <div
      v-if="walletStore.loading && !walletStore.dashboard"
      class="grid gap-4 md:grid-cols-3"
    >
      <AppCard
        v-for="item in 3"
        :key="item"
      >
        <div class="h-4 w-24 animate-pulse rounded bg-slate-200" />
        <div class="mt-4 h-8 w-36 animate-pulse rounded bg-slate-200" />
      </AppCard>
    </div>

    <template v-else>
      <div class="grid gap-4 md:grid-cols-3">
        <AppCard title="Saldo atual">
          <p class="text-3xl font-bold text-slate-950">
            {{ formatCents(walletStore.dashboard?.wallet.balance_cents) }}
          </p>
          <p class="mt-2 text-sm text-slate-500">
            Saldo disponivel na carteira.
          </p>
        </AppCard>

        <AppCard title="Depositado no mes">
          <p class="text-3xl font-bold text-emerald-700">
            {{ formatCents(walletStore.dashboard?.current_month.deposited_cents) }}
          </p>
          <p class="mt-2 text-sm text-slate-500">
            Total de entradas no mes atual.
          </p>
        </AppCard>

        <AppCard title="Sacado no mes">
          <p class="text-3xl font-bold text-rose-700">
            {{ formatCents(walletStore.dashboard?.current_month.withdrawn_cents) }}
          </p>
          <p class="mt-2 text-sm text-slate-500">
            Total de saidas no mes atual.
          </p>
        </AppCard>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <AppCard
          title="Ultimas transacoes"
          description="As 5 movimentacoes mais recentes da carteira."
        >
          <div
            v-if="lastTransactions.length === 0"
            class="rounded-md border border-dashed border-slate-300 px-4 py-8 text-center"
          >
            <p class="text-sm font-medium text-slate-700">
              Nenhuma transacao encontrada.
            </p>
            <p class="mt-1 text-sm text-slate-500">
              Depositos e saques aparecerao aqui depois da primeira movimentacao.
            </p>
          </div>

          <ul
            v-else
            class="divide-y divide-slate-200"
          >
            <li
              v-for="transaction in lastTransactions"
              :key="transaction.id"
              class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p class="text-sm font-semibold text-slate-950">
                  {{ transactionTypeLabel(transaction.type) }}
                </p>
                <p class="mt-1 text-sm text-slate-500">
                  {{ formatDate(transaction.occurred_at) }}
                </p>
              </div>

              <div class="text-left sm:text-right">
                <p :class="transactionAmountClasses(transaction.type)">
                  {{ transactionSign(transaction.type) }}{{ formatCents(transaction.amount_cents) }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                  Saldo apos: {{ formatCents(transaction.balance_after_cents) }}
                </p>
              </div>
            </li>
          </ul>
        </AppCard>

        <AppCard
          title="Acoes rapidas"
          description="Acesse os fluxos principais da carteira."
        >
          <div class="flex flex-col gap-3">
            <AppButton
              size="lg"
              @click="navigateTo('/wallet')"
            >
              Depositar / Sacar
            </AppButton>
            <AppButton
              variant="ghost"
              size="lg"
              @click="navigateTo('/transactions')"
            >
              Ver historico
            </AppButton>
          </div>
        </AppCard>
      </div>
    </template>
  </section>
</template>

<script setup lang="ts">
import AppAlert from '~/components/ui/AppAlert.vue'
import AppButton from '~/components/ui/AppButton.vue'
import AppCard from '~/components/ui/AppCard.vue'
import type { Transaction, TransactionType } from '~/types/api'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth',
})

const authStore = useAuthStore()
const walletStore = useWalletStore()
const { formatCents } = useMoney()

const dateFormatter = new Intl.DateTimeFormat('pt-BR', {
  dateStyle: 'short',
  timeStyle: 'short',
})

const lastTransactions = computed<Transaction[]>(() => walletStore.dashboard?.last_transactions ?? [])

onMounted(() => {
  void loadDashboard()
})

async function loadDashboard() {
  try {
    await walletStore.fetchDashboard()
  } catch {
    // API feedback is stored in walletStore.error.
  }
}

function transactionTypeLabel(type: TransactionType): string {
  return type === 'credit' ? 'Deposito' : 'Saque'
}

function transactionSign(type: TransactionType): string {
  return type === 'credit' ? '+' : '-'
}

function transactionAmountClasses(type: TransactionType): string {
  return [
    'text-sm font-semibold',
    type === 'credit' ? 'text-emerald-700' : 'text-rose-700',
  ].join(' ')
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Data nao disponivel'
  }

  return dateFormatter.format(new Date(value))
}
</script>
