<template>
  <div :class="alertClasses">
    <p class="text-sm font-medium">
      <slot />
    </p>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  variant?: 'success' | 'error' | 'info'
}>(), {
  variant: 'info',
})

const alertClasses = computed(() => [
  'rounded-md border px-4 py-3',
  {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-rose-200 bg-rose-50 text-rose-800',
    info: 'border-sky-200 bg-sky-50 text-sky-800',
  }[props.variant],
])
</script>
