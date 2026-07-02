<template>
  <div class="min-h-screen bg-slate-50 text-slate-950">
    <aside class="fixed inset-y-0 left-0 hidden w-72 flex-col border-r border-slate-200 bg-white lg:flex">
      <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-6">
        <div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
          <UIcon
            name="i-lucide-wallet"
            class="size-6"
          />
        </div>
        <div>
          <p class="text-lg font-bold text-slate-950">
            Fintech Wallet
          </p>
          <p class="text-xs font-medium text-slate-500">
            Carteira digital
          </p>
        </div>
      </div>

      <nav class="flex flex-1 flex-col gap-2 px-4 py-6 text-sm font-medium text-slate-600">
        <NuxtLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-slate-50 hover:text-slate-950 focus-visible:outline-emerald-300"
          active-class="bg-emerald-50 text-emerald-700 shadow-sm"
        >
          <UIcon
            :name="link.icon"
            class="size-5 shrink-0"
          />
          <span>{{ link.label }}</span>
        </NuxtLink>
      </nav>

      <div class="border-t border-slate-100 p-4">
        <AppButton
          variant="ghost"
          size="lg"
          :loading="authStore.loading"
          @click="handleLogout"
        >
          <span class="flex items-center gap-2">
            <UIcon
              name="i-lucide-log-out"
              class="size-4"
            />
            Sair
          </span>
        </AppButton>
      </div>
    </aside>

    <div class="lg:pl-72">
      <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="flex min-h-20 flex-col gap-3 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
          <div class="flex items-center justify-between gap-3">
            <NuxtLink
              to="/dashboard"
              class="flex items-center gap-3 lg:hidden"
            >
              <div class="flex size-10 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                <UIcon
                  name="i-lucide-wallet"
                  class="size-5"
                />
              </div>
              <span class="text-lg font-bold text-slate-950">Fintech Wallet</span>
            </NuxtLink>

            <div class="hidden lg:block">
              <p class="text-sm font-medium text-slate-500">
                Fintech Wallet
              </p>
              <p class="text-lg font-semibold text-slate-950">
                {{ authStore.user?.name ?? 'Carteira digital' }}
              </p>
            </div>

            <AppButton
              class="lg:hidden"
              variant="ghost"
              size="sm"
              :loading="authStore.loading"
              @click="handleLogout"
            >
              Sair
            </AppButton>
          </div>

          <nav class="flex gap-2 overflow-x-auto pb-1 text-sm font-medium text-slate-600 lg:hidden">
            <NuxtLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              class="flex shrink-0 items-center gap-2 rounded-full border border-transparent px-3 py-2 transition hover:bg-slate-50 hover:text-slate-950"
              active-class="border-emerald-100 bg-emerald-50 text-emerald-700"
            >
              <UIcon
                :name="link.icon"
                class="size-4"
              />
              {{ link.label }}
            </NuxtLink>
          </nav>

          <div class="hidden items-center gap-3 lg:flex">
            <div class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
              {{ authStore.user?.email ?? 'Sessão ativa' }}
            </div>
          </div>
        </div>
      </header>

      <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppButton from '~/components/ui/AppButton.vue'

const authStore = useAuthStore()

const links = [
  { label: 'Dashboard', to: '/dashboard', icon: 'i-lucide-layout-dashboard' },
  { label: 'Depositar / Sacar', to: '/operations', icon: 'i-lucide-arrow-up-down' },
  { label: 'Transações', to: '/transactions', icon: 'i-lucide-list' },
]

async function handleLogout() {
  try {
    await authStore.logout()
  } finally {
    await navigateTo('/login')
  }
}
</script>
