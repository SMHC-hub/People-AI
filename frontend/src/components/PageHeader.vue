<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
  badge?: string
}>()
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold tracking-tight text-neu-text">
          {{ title }}
        </h1>
        <span
          v-if="badge"
          class="text-[11px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-md bg-neu-primary/10 text-neu-primary"
        >
          {{ badge }}
        </span>
      </div>
      <p v-if="subtitle" class="text-sm text-neu-muted mt-1 leading-normal">
        {{ subtitle }}
      </p>
    </div>

    <div v-if="$slots.action" class="flex items-center gap-3 self-start sm:self-auto">
      <slot name="action" />
    </div>
  </div>
</template>
