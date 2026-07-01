<template>
  <main class="min-h-screen px-6 py-8">
    <section class="mx-auto flex max-w-5xl flex-col gap-6">
      <header class="flex flex-col gap-2">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
          Fintech Wallet
        </p>
        <h1 class="text-3xl font-bold text-slate-950">
          Carteira digital MVP
        </h1>
        <p class="max-w-2xl text-slate-600">
          Base Nuxt 3 em SPA mode preparada para consumir a API Laravel.
        </p>
      </header>

      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Stack</p>
          <p class="mt-1 font-semibold">Nuxt 3 + TypeScript</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">Estado</p>
          <p class="mt-1 font-semibold">Pinia configurado</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
          <p class="text-sm text-slate-500">API</p>
          <p class="mt-1 break-all font-semibold">{{ apiBaseUrl }}</p>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const apiBaseUrl = config.public.apiBaseUrl
</script>
