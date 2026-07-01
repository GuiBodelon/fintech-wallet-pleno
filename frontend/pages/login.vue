<template>
  <main class="min-h-screen bg-slate-50 px-4 py-10">
    <section class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-md flex-col justify-center">
      <div class="mb-6 text-center">
        <p class="text-sm font-semibold uppercase text-emerald-700">
          Fintech Wallet
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">
          Entrar
        </h1>
        <p class="mt-2 text-sm text-slate-600">
          Acesse sua carteira digital.
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
            v-model="form.email"
            label="E-mail"
            type="email"
            name="email"
            autocomplete="email"
            placeholder="demo@fintech.test"
            :disabled="authStore.loading"
            :error="fieldError('email')"
          />

          <AppInput
            v-model="form.password"
            label="Senha"
            type="password"
            name="password"
            autocomplete="current-password"
            placeholder="Sua senha"
            :disabled="authStore.loading"
            :error="fieldError('password')"
          />

          <AppButton
            type="submit"
            size="lg"
            :loading="authStore.loading"
          >
            Entrar
          </AppButton>

          <p class="text-center text-sm text-slate-600">
            Novo na Fintech Wallet?
            <NuxtLink
              to="/register"
              class="font-semibold text-emerald-700 hover:text-emerald-800"
            >
              Criar conta
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
import type { LoginPayload } from '~/types/api'

const authStore = useAuthStore()

const form = reactive<LoginPayload>({
  email: '',
  password: '',
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

function fieldError(field: keyof LoginPayload): string | undefined {
  return authStore.validationErrors?.[field]?.[0]
}

async function handleSubmit() {
  try {
    await authStore.login(form)
    await navigateTo('/dashboard')
  } catch {
    // Feedback is stored in authStore.error and authStore.validationErrors.
  }
}
</script>
