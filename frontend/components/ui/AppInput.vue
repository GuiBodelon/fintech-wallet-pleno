<template>
  <label class="flex flex-col gap-2">
    <span
      v-if="label"
      class="text-sm font-medium text-slate-700"
    >
      {{ label }}
    </span>

    <input
      :value="modelValue"
      :type="type"
      :name="name"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      :class="inputClasses"
      @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    >

    <span
      v-if="error"
      class="text-sm text-rose-600"
    >
      {{ error }}
    </span>
  </label>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  modelValue: string
  label?: string
  type?: string
  name?: string
  placeholder?: string
  autocomplete?: string
  error?: string
  disabled?: boolean
}>(), {
  type: 'text',
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const inputClasses = computed(() => [
  'h-11 w-full rounded-md border bg-white px-3 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500',
  props.error
    ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-100'
    : 'border-slate-300 focus:border-orange-500 focus:ring-orange-100',
])
</script>
