<script setup lang="ts">
withDefaults(
  defineProps<{
    title?: string
    description?: string
  }>(),
  {
    title: 'Everything looks normal',
    description: 'No items or alerts require your attention at this time.',
  }
)
</script>

<template>
  <div class="neu-card p-12 text-center flex flex-col items-center justify-center">
    <div class="w-12 h-12 rounded-2xl bg-neu-primary/10 text-neu-primary flex items-center justify-center mb-3">
      <slot name="icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 13l4 4L19 7" />
        </svg>
      </slot>
    </div>
    <h3 class="text-base font-bold text-neu-text tracking-tight mb-1">
      {{ title }}
    </h3>
    <p class="text-xs text-neu-muted max-w-sm">
      {{ description }}
    </p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>
