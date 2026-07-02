<template>
  <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] sm:p-6">
    <header
      v-if="title || description"
      class="mb-5"
    >
      <h2
        v-if="title"
        class="text-base font-semibold text-slate-950"
      >
        {{ title }}
      </h2>
      <p
        v-if="description"
        class="mt-1 text-sm text-slate-500"
      >
        {{ description }}
      </p>
    </header>

    <slot />
  </article>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  description?: string
}>()
</script>
