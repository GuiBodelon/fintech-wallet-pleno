<template>
  <div />
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

await navigateTo('/operations?tab=withdraw')
</script>
