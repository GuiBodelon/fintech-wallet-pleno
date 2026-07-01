<template>
  <UCard class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <template
      v-if="title || description"
      #header
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
    </template>

    <slot />
  </UCard>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  description?: string
}>()
</script>
