<template>
  <div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase text-emerald-700">
            Fintech Wallet
          </p>
          <h1 class="text-xl font-bold text-slate-950">
            Carteira digital
          </h1>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <nav class="flex flex-wrap gap-2 text-sm font-medium text-slate-700">
            <NuxtLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              class="rounded-md px-3 py-2 hover:bg-slate-100"
              active-class="bg-emerald-50 text-emerald-700"
            >
              {{ link.label }}
            </NuxtLink>
          </nav>

          <AppButton
            variant="ghost"
            size="sm"
            :loading="authStore.loading"
            @click="handleLogout"
          >
            Sair
          </AppButton>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import AppButton from '~/components/ui/AppButton.vue'

const authStore = useAuthStore()

const links = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Depositar', to: '/deposit' },
  { label: 'Sacar', to: '/withdraw' },
  { label: 'Historico', to: '/transactions' },
]

async function handleLogout() {
  try {
    await authStore.logout()
  } finally {
    await navigateTo('/login')
  }
}
</script>
