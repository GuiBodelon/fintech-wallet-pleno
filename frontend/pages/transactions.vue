<template>
  <section class="flex flex-col gap-6">
    <div>
      <p class="text-sm font-semibold text-emerald-700">
        Carteira
      </p>
      <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">
        Histórico de transações
      </h2>
      <p class="mt-2 text-sm text-slate-500">
        Confira todas as movimentações da sua carteira.
      </p>
    </div>

    <AppAlert
      v-if="walletStore.error"
      variant="error"
    >
      {{ walletStore.error }}
    </AppAlert>

    <AppCard>
      <form
        class="grid gap-4 xl:grid-cols-[1.2fr_1fr_1fr_auto]"
        @submit.prevent="applyFilters"
      >
        <label class="flex flex-col gap-2">
          <span class="text-sm font-semibold text-slate-700">Tipo de transação</span>
          <div class="relative">
            <UIcon
              name="i-lucide-list-filter"
              class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
            />
            <select
              v-model="filters.type"
              class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-10 text-sm text-slate-950 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
            >
              <option value="">
                Todos
              </option>
              <option value="credit">
                Crédito
              </option>
              <option value="debit">
                Débito
              </option>
            </select>
            <UIcon
              name="i-lucide-chevron-down"
              class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
            />
          </div>
        </label>

        <label class="flex flex-col gap-2">
          <span class="text-sm font-semibold text-slate-700">Data inicial</span>
          <div class="relative">
            <UIcon
              name="i-lucide-calendar"
              class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
            />
            <input
              v-model="filters.from"
              type="date"
              name="from"
              class="h-11 w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-950 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
              @change="resetPage"
            >
          </div>
          <span
            v-if="filters.from"
            class="text-xs font-medium text-slate-500"
          >
            {{ formatFilterDate(filters.from) }}
          </span>
        </label>

        <label class="flex flex-col gap-2">
          <span class="text-sm font-semibold text-slate-700">Data final</span>
          <div class="relative">
            <UIcon
              name="i-lucide-calendar"
              class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
            />
            <input
              v-model="filters.to"
              type="date"
              name="to"
              class="h-11 w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-950 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
              @change="resetPage"
            >
          </div>
          <span
            v-if="filters.to"
            class="text-xs font-medium text-slate-500"
          >
            {{ formatFilterDate(filters.to) }}
          </span>
        </label>

        <div class="flex flex-col gap-2 sm:flex-row xl:items-end">
          <AppButton
            type="submit"
            size="lg"
            :loading="walletStore.loading"
          >
            Filtrar
          </AppButton>

          <AppButton
            variant="secondary"
            size="lg"
            :disabled="walletStore.loading"
            @click="clearFilters"
          >
            <span class="flex items-center gap-2">
              <UIcon
                name="i-lucide-rotate-ccw"
                class="size-4"
              />
              Limpar filtros
            </span>
          </AppButton>
        </div>
      </form>
    </AppCard>

    <AppCard
      title="Movimentações"
      :description="paginationDescription"
    >
      <div
        v-if="walletStore.loading && walletStore.transactions.length === 0"
        class="flex flex-col gap-3"
      >
        <div
          v-for="item in 8"
          :key="item"
          class="h-14 animate-pulse rounded-xl bg-slate-100"
        />
      </div>

      <div
        v-else-if="walletStore.transactions.length === 0"
        class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center"
      >
        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
          <UIcon
            name="i-lucide-search"
            class="size-6"
          />
        </div>
        <p class="mt-4 text-sm font-semibold text-slate-700">
          Nenhuma transação encontrada.
        </p>
        <p class="mt-1 text-sm text-slate-500">
          Ajuste os filtros ou realize uma movimentação na carteira.
        </p>
      </div>

      <div
        v-else
        class="overflow-x-auto"
      >
        <table class="min-w-full divide-y divide-slate-100 text-sm">
          <thead>
            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
              <th class="whitespace-nowrap px-4 py-3">
                Data / Hora
              </th>
              <th class="whitespace-nowrap px-4 py-3">
                Tipo
              </th>
              <th class="whitespace-nowrap px-4 py-3 text-right">
                Valor
              </th>
              <th class="whitespace-nowrap px-4 py-3 text-right">
                Saldo após operação
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="transaction in walletStore.transactions"
              :key="transaction.id"
              class="text-slate-700 transition hover:bg-slate-50/70"
            >
              <td class="whitespace-nowrap px-4 py-4">
                {{ formatDate(transaction.occurred_at) }}
              </td>
              <td class="whitespace-nowrap px-4 py-4">
                <span :class="typeBadgeClasses(transaction.type)">
                  <UIcon
                    :name="transaction.type === 'credit' ? 'i-lucide-arrow-down' : 'i-lucide-arrow-up'"
                    class="size-3.5"
                  />
                  {{ transactionTypeLabel(transaction.type) }}
                </span>
              </td>
              <td :class="amountClasses(transaction.type)">
                {{ transactionSign(transaction.type) }}{{ formatCents(transaction.amount_cents) }}
              </td>
              <td class="whitespace-nowrap px-4 py-4 text-right font-medium text-slate-950">
                {{ formatCents(transaction.balance_after_cents) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <AppPagination
        v-if="walletStore.pagination && walletStore.pagination.last_page > 1"
        class="mt-4"
        :pagination="walletStore.pagination"
        @change="changePage"
      />
    </AppCard>
  </section>
</template>

<script setup lang="ts">
import AppAlert from '~/components/ui/AppAlert.vue'
import AppButton from '~/components/ui/AppButton.vue'
import AppCard from '~/components/ui/AppCard.vue'
import AppPagination from '~/components/ui/AppPagination.vue'
import type { TransactionFilters, TransactionType } from '~/types/api'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth',
})

const walletStore = useWalletStore()
const { formatCents } = useMoney()

const filters = reactive({
  type: '',
  from: '',
  to: '',
  page: 1,
  per_page: 10,
})

const dateFormatter = new Intl.DateTimeFormat('pt-BR', {
  dateStyle: 'short',
  timeStyle: 'short',
})

const filterDateFormatter = new Intl.DateTimeFormat('pt-BR', {
  dateStyle: 'short',
})

const paginationDescription = computed(() => {
  const pagination = walletStore.pagination

  if (!pagination || pagination.total === 0) {
    return 'Nenhuma movimentação para os filtros atuais.'
  }

  return `Mostrando ${pagination.from ?? 0} a ${pagination.to ?? 0} de ${pagination.total} transações.`
})

watch(() => filters.type, resetPage)

onMounted(() => {
  void loadTransactions()
})

async function applyFilters() {
  filters.page = 1
  await loadTransactions()
}

async function clearFilters() {
  filters.type = ''
  filters.from = ''
  filters.to = ''
  filters.page = 1
  filters.per_page = 10
  await loadTransactions()
}

async function changePage(page: number) {
  filters.page = page
  await loadTransactions()
}

async function loadTransactions() {
  try {
    await walletStore.fetchTransactions(buildTransactionFilters())
  } catch {
    // API feedback is stored in walletStore.error.
  }
}

function resetPage() {
  filters.page = 1
}

function buildTransactionFilters(): TransactionFilters {
  return {
    type: filters.type ? filters.type as TransactionType : undefined,
    from: filters.from || undefined,
    to: filters.to || undefined,
    page: filters.page,
    per_page: filters.per_page,
  }
}

function transactionTypeLabel(type: TransactionType): string {
  return type === 'credit' ? 'Crédito' : 'Débito'
}

function transactionSign(type: TransactionType): string {
  return type === 'credit' ? '+' : '-'
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Data não disponível'
  }

  return dateFormatter.format(new Date(value))
}

function formatFilterDate(value: string): string {
  return filterDateFormatter.format(new Date(`${value}T00:00:00`))
}

function typeBadgeClasses(type: TransactionType): string {
  return [
    'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold',
    type === 'credit'
      ? 'bg-emerald-50 text-emerald-700'
      : 'bg-rose-50 text-rose-700',
  ].join(' ')
}

function amountClasses(type: TransactionType): string {
  return [
    'whitespace-nowrap px-4 py-4 text-right font-semibold',
    type === 'credit' ? 'text-emerald-700' : 'text-rose-700',
  ].join(' ')
}
</script>
