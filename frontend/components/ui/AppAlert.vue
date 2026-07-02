<template>
  <div
    :class="alertClasses"
    :role="variant === 'error' ? 'alert' : 'status'"
  >
    <UIcon
      :name="iconName"
      class="mt-0.5 size-5 shrink-0"
    />
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

const iconMap: Record<NonNullable<typeof props.variant>, string> = {
  success: 'i-lucide-check-circle-2',
  error: 'i-lucide-alert-circle',
  info: 'i-lucide-info',
}

const iconName = computed(() => iconMap[props.variant])

const alertClasses = computed(() => [
  'flex gap-3 rounded-2xl border px-4 py-3 shadow-sm',
  {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-rose-200 bg-rose-50 text-rose-800',
    info: 'border-sky-200 bg-sky-50 text-sky-800',
  }[props.variant],
])
</script>
