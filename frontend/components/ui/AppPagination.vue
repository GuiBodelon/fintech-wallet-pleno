<template>
  <nav
    class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
    aria-label="Paginacao"
  >
    <p class="text-sm text-slate-600">
      Pagina {{ pagination.current_page }} de {{ pagination.last_page }}
    </p>
    <div class="flex items-center gap-2">
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="pagination.current_page <= 1"
        @click="emit('change', pagination.current_page - 1)"
      >
        Anterior
      </AppButton>
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="pagination.current_page >= pagination.last_page"
        @click="emit('change', pagination.current_page + 1)"
      >
        Proxima
      </AppButton>
    </div>
  </nav>
</template>

<script setup lang="ts">
import AppButton from '~/components/ui/AppButton.vue'
import type { PaginationMeta } from '~/types/api'

defineProps<{
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  change: [page: number]
}>()
</script>
