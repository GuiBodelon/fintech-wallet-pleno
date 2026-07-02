<template>
  <section class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-semibold text-emerald-700">
          Visão geral
        </p>
        <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
          Dashboard
        </h2>
        <p
          v-if="authStore.user"
          class="mt-2 text-sm text-slate-500"
        >
          Olá, {{ authStore.user.name }}. Aqui está o resumo da sua carteira.
        </p>
      </div>

      <AppButton
        variant="secondary"
        size="sm"
        :loading="walletStore.loading"
        @click="loadDashboard"
      >
        <span class="flex items-center gap-2">
          <UIcon
            name="i-lucide-refresh-cw"
            class="size-4"
          />
          Atualizar
        </span>
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
      class="grid gap-4 lg:grid-cols-3"
    >
      <AppCard
        v-for="item in 3"
        :key="item"
      >
        <div class="h-4 w-24 animate-pulse rounded bg-slate-200" />
        <div class="mt-5 h-9 w-40 animate-pulse rounded bg-slate-200" />
        <div class="mt-3 h-4 w-32 animate-pulse rounded bg-slate-100" />
      </AppCard>
    </div>

    <template v-else>
      <div class="grid gap-4 lg:grid-cols-[1.45fr_1fr_1fr]">
        <section class="relative overflow-hidden rounded-2xl bg-emerald-600 p-6 text-white shadow-[0_18px_40px_rgba(16,185,129,0.22)]">
          <UIcon
            name="i-lucide-wallet"
            class="absolute -right-4 bottom-0 size-32 text-white/10"
          />
          <div class="relative">
            <div class="flex items-center justify-between gap-4">
              <p class="text-sm font-semibold text-emerald-50">
                Saldo atual
              </p>
              <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-emerald-50">
                Disponível
              </span>
            </div>
            <p class="mt-7 text-4xl font-bold tracking-tight">
              {{ formatCents(walletStore.dashboard?.wallet.balance_cents) }}
            </p>
            <p class="mt-4 flex items-center gap-2 text-sm text-emerald-50">
              <UIcon
                name="i-lucide-shield-check"
                class="size-4"
              />
              Saldo disponível na carteira.
            </p>
          </div>
        </section>

        <AppCard>
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-slate-500">
                Total depositado
              </p>
              <p class="mt-1 text-xs font-medium text-slate-400">
                Este mês
              </p>
            </div>
            <div class="flex size-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
              <UIcon
                name="i-lucide-arrow-down"
                class="size-5"
              />
            </div>
          </div>
          <p class="mt-8 text-3xl font-bold text-emerald-700">
            {{ formatCents(walletStore.dashboard?.current_month.deposited_cents) }}
          </p>
        </AppCard>

        <AppCard>
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-slate-500">
                Total sacado
              </p>
              <p class="mt-1 text-xs font-medium text-slate-400">
                Este mês
              </p>
            </div>
            <div class="flex size-11 items-center justify-center rounded-full bg-rose-50 text-rose-700">
              <UIcon
                name="i-lucide-arrow-up"
                class="size-5"
              />
            </div>
          </div>
          <p class="mt-8 text-3xl font-bold text-rose-700">
            {{ formatCents(walletStore.dashboard?.current_month.withdrawn_cents) }}
          </p>
        </AppCard>
      </div>

      <AppCard
        title="Ações rápidas"
        description="Acesse os fluxos principais da carteira."
      >
        <div class="grid gap-3 md:grid-cols-3">
          <button
            v-for="action in quickActions"
            :key="action.label"
            type="button"
            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-100 hover:bg-emerald-50/40 focus-visible:outline-emerald-300"
            @click="navigateTo(action.to)"
          >
            <span class="flex items-center gap-3">
              <span :class="action.iconClasses">
                <UIcon
                  :name="action.icon"
                  class="size-5"
                />
              </span>
              <span>
                <span class="block text-sm font-semibold text-slate-950">{{ action.label }}</span>
                <span class="mt-1 block text-xs text-slate-500">{{ action.description }}</span>
              </span>
            </span>
            <UIcon
              name="i-lucide-chevron-right"
              class="size-5 text-slate-400"
            />
          </button>
        </div>
      </AppCard>

      <AppCard
        title="Últimas transações"
        description="As 5 movimentações mais recentes da carteira."
      >
        <div
          v-if="lastTransactions.length === 0"
          class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center"
        >
          <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <UIcon
              name="i-lucide-list"
              class="size-6"
            />
          </div>
          <p class="mt-4 text-sm font-semibold text-slate-700">
            Nenhuma transação encontrada.
          </p>
          <p class="mt-1 text-sm text-slate-500">
            Depósitos e saques aparecerão aqui depois da primeira movimentação.
          </p>
        </div>

        <div
          v-else
          class="overflow-x-auto"
        >
          <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead>
              <tr class="text-left text-xs font-semibold uppercase text-slate-500">
                <th class="whitespace-nowrap px-3 py-3">
                  Data / Hora
                </th>
                <th class="whitespace-nowrap px-3 py-3">
                  Tipo
                </th>
                <th class="whitespace-nowrap px-3 py-3 text-right">
                  Valor
                </th>
                <th class="whitespace-nowrap px-3 py-3 text-right">
                  Saldo após
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="transaction in lastTransactions"
                :key="transaction.id"
                class="text-slate-700"
              >
                <td class="whitespace-nowrap px-3 py-4">
                  {{ formatDate(transaction.occurred_at) }}
                </td>
                <td class="whitespace-nowrap px-3 py-4">
                  <span :class="typeBadgeClasses(transaction.type)">
                    {{ transactionTypeLabel(transaction.type) }}
                  </span>
                </td>
                <td :class="transactionAmountClasses(transaction.type)">
                  {{ transactionSign(transaction.type) }}{{ formatCents(transaction.amount_cents) }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-right font-medium text-slate-950">
                  {{ formatCents(transaction.balance_after_cents) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </AppCard>
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

const quickActions = [
  {
    label: 'Depositar',
    description: 'Adicionar saldo',
    to: '/operations?tab=deposit',
    icon: 'i-lucide-arrow-down',
    iconClasses: 'flex size-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-700',
  },
  {
    label: 'Sacar',
    description: 'Retirar saldo',
    to: '/operations?tab=withdraw',
    icon: 'i-lucide-arrow-up',
    iconClasses: 'flex size-11 items-center justify-center rounded-full bg-rose-50 text-rose-700',
  },
  {
    label: 'Ver transações',
    description: 'Histórico completo',
    to: '/transactions',
    icon: 'i-lucide-list',
    iconClasses: 'flex size-11 items-center justify-center rounded-full bg-sky-50 text-sky-700',
  },
]

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
  return type === 'credit' ? 'Depósito' : 'Saque'
}

function transactionSign(type: TransactionType): string {
  return type === 'credit' ? '+' : '-'
}

function transactionAmountClasses(type: TransactionType): string {
  return [
    'whitespace-nowrap px-3 py-4 text-right font-semibold',
    type === 'credit' ? 'text-emerald-700' : 'text-rose-700',
  ].join(' ')
}

function typeBadgeClasses(type: TransactionType): string {
  return [
    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
    type === 'credit'
      ? 'bg-emerald-50 text-emerald-700'
      : 'bg-rose-50 text-rose-700',
  ].join(' ')
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Data não disponível'
  }

  return dateFormatter.format(new Date(value))
}
</script>
