<template>
  <nav
    class="flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between"
    aria-label="Paginação"
  >
    <p class="text-sm text-slate-600">
      Mostrando {{ pagination.from ?? 0 }} a {{ pagination.to ?? 0 }} de {{ pagination.total }} transações
    </p>

    <div class="flex flex-wrap items-center gap-2">
      <button
        type="button"
        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 focus-visible:outline-emerald-300 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="pagination.current_page <= 1"
        aria-label="Página anterior"
        @click="emit('change', pagination.current_page - 1)"
      >
        <UIcon
          name="i-lucide-chevron-left"
          class="size-4"
        />
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        type="button"
        :class="pageButtonClasses(page)"
        :aria-current="page === pagination.current_page ? 'page' : undefined"
        @click="emit('change', page)"
      >
        {{ page }}
      </button>

      <button
        type="button"
        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 focus-visible:outline-emerald-300 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="pagination.current_page >= pagination.last_page"
        aria-label="Próxima página"
        @click="emit('change', pagination.current_page + 1)"
      >
        <UIcon
          name="i-lucide-chevron-right"
          class="size-4"
        />
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import type { PaginationMeta } from '~/types/api'

const props = defineProps<{
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  change: [page: number]
}>()

const visiblePages = computed(() => {
  const total = props.pagination.last_page
  const current = props.pagination.current_page
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)

  return Array.from({ length: end - start + 1 }, (_, index) => start + index)
})

function pageButtonClasses(page: number): string {
  return [
    'inline-flex size-10 items-center justify-center rounded-xl border text-sm font-semibold shadow-sm transition focus-visible:outline-emerald-300',
    page === props.pagination.current_page
      ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
      : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
  ].join(' ')
}
</script>
