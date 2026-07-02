<template>
  <div class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-950">
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
        <div class="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:min-h-20 lg:px-8">
          <NuxtLink
            to="/dashboard"
            class="flex min-w-0 items-center gap-3 lg:hidden"
            @click="closeMobileMenu"
          >
            <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
              <UIcon
                name="i-lucide-wallet"
                class="size-5"
              />
            </div>
            <span class="truncate text-lg font-bold text-slate-950">Fintech Wallet</span>
          </NuxtLink>

          <div class="hidden lg:block">
            <p class="text-sm font-medium text-slate-500">
              Fintech Wallet
            </p>
            <p class="text-lg font-semibold text-slate-950">
              {{ authStore.user?.name ?? 'Carteira digital' }}
            </p>
          </div>

          <button
            type="button"
            class="inline-flex size-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline-emerald-300 lg:hidden"
            aria-label="Abrir menu de navegação"
            :aria-expanded="mobileMenuOpen"
            aria-controls="mobile-navigation"
            @click="mobileMenuOpen = true"
          >
            <UIcon
              name="i-lucide-menu"
              class="size-6"
            />
          </button>

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

    <Teleport to="body">
      <div
        v-if="mobileMenuOpen"
        class="fixed inset-0 z-50 lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Menu de navegação"
      >
        <button
          type="button"
          class="absolute inset-0 bg-slate-950/40"
          aria-label="Fechar menu de navegação"
          @click="closeMobileMenu"
        />

        <aside
          id="mobile-navigation"
          class="relative flex h-full w-full max-w-80 flex-col bg-white shadow-2xl"
        >
          <div class="flex h-20 items-center justify-between gap-3 border-b border-slate-100 px-5">
            <div class="flex min-w-0 items-center gap-3">
              <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                <UIcon
                  name="i-lucide-wallet"
                  class="size-5"
                />
              </div>
              <div class="min-w-0">
                <p class="truncate text-base font-bold text-slate-950">
                  Fintech Wallet
                </p>
                <p class="text-xs font-medium text-slate-500">
                  Carteira digital
                </p>
              </div>
            </div>

            <button
              type="button"
              class="inline-flex size-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 focus-visible:outline-emerald-300"
              aria-label="Fechar menu de navegação"
              @click="closeMobileMenu"
            >
              <UIcon
                name="i-lucide-x"
                class="size-5"
              />
            </button>
          </div>

          <nav class="flex flex-1 flex-col gap-2 px-4 py-6 text-sm font-medium text-slate-600">
            <NuxtLink
              v-for="link in links"
              :key="link.to"
              :to="link.to"
              class="flex items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-slate-50 hover:text-slate-950 focus-visible:outline-emerald-300"
              active-class="bg-emerald-50 text-emerald-700 shadow-sm"
              @click="closeMobileMenu"
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
              class="w-full"
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
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import AppButton from '~/components/ui/AppButton.vue'

const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const links = [
  { label: 'Dashboard', to: '/dashboard', icon: 'i-lucide-layout-dashboard' },
  { label: 'Depositar / Sacar', to: '/operations', icon: 'i-lucide-arrow-up-down' },
  { label: 'Transações', to: '/transactions', icon: 'i-lucide-list' },
]

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

async function handleLogout() {
  closeMobileMenu()

  try {
    await authStore.logout()
  } finally {
    await navigateTo('/login')
  }
}
</script>
