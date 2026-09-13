<script setup lang="ts">
withDefaults(
  defineProps<{
    type?: 'card' | 'line' | 'table' | 'chart'
    rows?: number
    height?: string
  }>(),
  {
    type: 'line',
    rows: 3,
    height: '16px',
  }
)
</script>

<template>
  <div v-if="type === 'card'" class="neu-card p-6 animate-pulse space-y-4">
    <div class="h-3 w-1/3 bg-neu-muted/20 rounded-md"></div>
    <div class="h-8 w-1/2 bg-neu-muted/25 rounded-lg"></div>
    <div class="h-3 w-2/3 bg-neu-muted/15 rounded-md"></div>
  </div>

  <div v-else-if="type === 'chart'" class="neu-card p-6 animate-pulse" :style="{ height: height || '320px' }">
    <div class="h-4 w-1/4 bg-neu-muted/20 rounded-md mb-6"></div>
    <div class="h-3/4 w-full bg-neu-muted/10 rounded-xl flex items-end gap-3 p-4">
      <div class="w-full bg-neu-muted/20 rounded-t h-1/2"></div>
      <div class="w-full bg-neu-muted/20 rounded-t h-3/4"></div>
      <div class="w-full bg-neu-muted/20 rounded-t h-2/3"></div>
      <div class="w-full bg-neu-muted/20 rounded-t h-4/5"></div>
      <div class="w-full bg-neu-muted/20 rounded-t h-1/3"></div>
    </div>
  </div>

  <div v-else-if="type === 'table'" class="neu-card p-4 animate-pulse space-y-3">
    <div class="h-8 bg-neu-muted/15 rounded-lg"></div>
    <div v-for="r in rows" :key="r" class="h-10 bg-neu-muted/10 rounded-md"></div>
  </div>

  <div v-else class="animate-pulse space-y-2">
    <div
      v-for="r in rows"
      :key="r"
      class="bg-neu-muted/20 rounded-md"
      :style="{ height: height, width: r === rows ? '70%' : '100%' }"
    ></div>
  </div>
</template>
