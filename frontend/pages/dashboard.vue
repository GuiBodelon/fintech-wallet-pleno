<template>
  <section class="flex flex-col gap-6">
    <AppCard
      title="Dashboard"
      :description="authStore.user ? `Sessao ativa para ${authStore.user.name}.` : undefined"
    >
      <p class="text-sm text-slate-600">
        A interface da carteira sera adicionada nas proximas etapas.
      </p>
    </AppCard>
  </section>
</template>

<script setup lang="ts">
import AppCard from '~/components/ui/AppCard.vue'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth',
})

const authStore = useAuthStore()
</script>
