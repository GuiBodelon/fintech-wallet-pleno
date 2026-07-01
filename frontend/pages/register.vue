<template>
  <main class="min-h-screen bg-slate-50 px-4 py-10">
    <section class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-md flex-col justify-center">
      <div class="mb-6 text-center">
        <p class="text-sm font-semibold uppercase text-emerald-700">
          Fintech Wallet
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">
          Criar conta
        </h1>
        <p class="mt-2 text-sm text-slate-600">
          Cadastre-se para acessar sua carteira.
        </p>
      </div>

      <AppCard>
        <form
          class="flex flex-col gap-4"
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
            type="submit"
            size="lg"
            :loading="authStore.loading"
          >
            Criar conta
          </AppButton>

          <p class="text-center text-sm text-slate-600">
            Ja tem conta?
            <NuxtLink
              to="/login"
              class="font-semibold text-emerald-700 hover:text-emerald-800"
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
