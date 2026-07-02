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
        class="grid gap-4 lg:grid-cols-[1.2fr_1fr_1fr_auto]"
        @submit.prevent="applyFilters"
      >
        <label class="flex flex-col gap-2">
          <span class="text-sm font-semibold text-slate-700">Tipo de transação</span>
          <select
            v-model="filters.type"
            class="h-11 rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-950 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
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
        </label>

        <AppInput
          v-model="filters.from"
          label="Data inicial"
          type="date"
          name="from"
        />

        <AppInput
          v-model="filters.to"
          label="Data final"
          type="date"
          name="to"
        />

        <div class="flex items-end gap-2">
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
              Limpar
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
import AppInput from '~/components/ui/AppInput.vue'
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

const paginationDescription = computed(() => {
  const pagination = walletStore.pagination

  if (!pagination || pagination.total === 0) {
    return 'Nenhuma movimentação para os filtros atuais.'
  }

  return `Mostrando ${pagination.from ?? 0} a ${pagination.to ?? 0} de ${pagination.total} transações.`
})

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
