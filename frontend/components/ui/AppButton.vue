<template>
  <UButton
    :type="type"
    :color="color"
    :variant="uiVariant"
    :size="size"
    :loading="loading"
    :disabled="disabled || loading"
    :class="buttonClasses"
  >
    <slot />
  </UButton>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  type?: 'button' | 'submit' | 'reset'
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl'
  loading?: boolean
  disabled?: boolean
}>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  loading: false,
  disabled: false,
})

type ButtonColor = 'error' | 'primary'
type ButtonVariant = 'solid' | 'outline' | 'ghost'

const variantMap: Record<NonNullable<typeof props.variant>, ButtonVariant> = {
  primary: 'solid',
  secondary: 'outline',
  danger: 'solid',
  ghost: 'ghost',
}

const color = computed<ButtonColor>(() => (props.variant === 'danger' ? 'error' : 'primary'))
const uiVariant = computed<ButtonVariant>(() => variantMap[props.variant])

const sizeClasses: Record<NonNullable<typeof props.size>, string> = {
  xs: 'h-8 px-3 text-xs',
  sm: 'h-9 px-3 text-sm',
  md: 'h-10 px-4 text-sm',
  lg: 'h-11 px-5 text-sm',
  xl: 'h-12 px-6 text-base',
}

const variantClasses: Record<NonNullable<typeof props.variant>, string> = {
  primary: 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 focus-visible:ring-emerald-200',
  secondary: 'border border-slate-200 bg-white text-slate-800 shadow-sm hover:bg-slate-50 focus-visible:ring-emerald-200',
  danger: 'bg-rose-600 text-white shadow-sm hover:bg-rose-700 focus-visible:ring-rose-200',
  ghost: 'text-slate-700 hover:bg-slate-100 focus-visible:ring-emerald-200',
}

const buttonClasses = computed(() => [
  'inline-flex items-center justify-center rounded-xl font-semibold transition disabled:cursor-not-allowed disabled:opacity-60 focus-visible:ring-4',
  sizeClasses[props.size],
  variantClasses[props.variant],
])
</script>
