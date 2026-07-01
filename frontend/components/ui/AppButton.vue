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

const buttonClasses = computed(() => [
  'inline-flex items-center justify-center rounded-md font-semibold transition disabled:cursor-not-allowed disabled:opacity-60',
  props.size === 'lg' ? 'h-11 px-4 text-sm' : 'h-10 px-3 text-sm',
  {
    primary: 'bg-emerald-600 text-white hover:bg-emerald-700',
    secondary: 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50',
    danger: 'bg-rose-600 text-white hover:bg-rose-700',
    ghost: 'text-slate-700 hover:bg-slate-100',
  }[props.variant],
])
</script>
