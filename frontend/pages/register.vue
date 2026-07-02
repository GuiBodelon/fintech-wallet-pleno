<template>
  <main class="min-h-screen bg-slate-50 px-4 py-10 text-slate-950">
    <section class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-lg flex-col justify-center">
      <div class="mb-8 text-center">
        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
          <UIcon
            name="i-lucide-wallet"
            class="size-7"
          />
        </div>
        <p class="mt-4 text-2xl font-bold tracking-tight text-slate-950">
          Fintech Wallet
        </p>
        <p class="mt-2 text-sm text-slate-500">
          Sua carteira digital, simples e segura.
        </p>
      </div>

      <AppCard>
        <div class="mb-6 text-center">
          <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
            <UIcon
              name="i-lucide-user-plus"
              class="size-7"
            />
          </div>
          <h1 class="mt-4 text-2xl font-bold text-slate-950">
            Criar conta
          </h1>
          <p class="mt-2 text-sm text-slate-500">
            Preencha os dados para acessar sua carteira.
          </p>
        </div>

        <form
          class="flex flex-col gap-5"
          @submit.prevent="handleSubmit"
        >
          <AppAlert
            v-if="authStore.error"
            variant="error"
          >
            {{ authStore.error }}
          </AppAlert>

          <AppInput
            v-model="form.name"
            label="Nome"
            name="name"
            autocomplete="name"
            placeholder="Seu nome"
            :disabled="authStore.loading"
            :error="fieldError('name')"
          />

          <AppInput
            v-model="form.email"
            label="E-mail"
            type="email"
            name="email"
            autocomplete="email"
            placeholder="voce@email.com"
            :disabled="authStore.loading"
            :error="fieldError('email')"
          />

          <AppInput
            v-model="form.password"
            label="Senha"
            type="password"
            name="password"
            autocomplete="new-password"
            placeholder="Crie uma senha"
            :disabled="authStore.loading"
            :error="fieldError('password')"
          />

          <AppInput
            v-model="form.password_confirmation"
            label="Confirmar senha"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            placeholder="Repita a senha"
            :disabled="authStore.loading"
            :error="fieldError('password_confirmation')"
          />

          <AppButton
            class="w-full"
            type="submit"
            size="lg"
            :loading="authStore.loading"
          >
            Criar conta
          </AppButton>

          <p class="text-center text-sm text-slate-600">
            Já tem conta?
            <NuxtLink
              to="/login"
              class="font-semibold text-emerald-700 transition hover:text-emerald-800 focus-visible:rounded focus-visible:outline-emerald-300"
            >
              Entrar
            </NuxtLink>
          </p>
        </form>
      </AppCard>
    </section>
  </main>
</template>

<script setup lang="ts">
import AppAlert from '~/components/ui/AppAlert.vue'
import AppButton from '~/components/ui/AppButton.vue'
import AppCard from '~/components/ui/AppCard.vue'
import AppInput from '~/components/ui/AppInput.vue'
import type { RegisterPayload } from '~/types/api'

const authStore = useAuthStore()

const form = reactive<RegisterPayload>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

onMounted(async () => {
  try {
    await authStore.initializeAuth()

    if (authStore.isAuthenticated) {
      await navigateTo('/dashboard')
    }
  } catch {
    // The form remains available when session rehydration fails.
  }
})

function fieldError(field: keyof RegisterPayload): string | undefined {
  return authStore.validationErrors?.[field]?.[0]
}

async function handleSubmit() {
  try {
    await authStore.register(form)
    await navigateTo('/dashboard')
  } catch {
    // Feedback is stored in authStore.error and authStore.validationErrors.
  }
}
</script>
