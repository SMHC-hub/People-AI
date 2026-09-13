<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  score?: number | null
  level?: string
  size?: 'sm' | 'md'
}>()

const computedLevel = computed<'low' | 'medium' | 'high'>(() => {
  if (props.level) {
    const l = props.level.toLowerCase()
    if (l.includes('high') || l.includes('critical')) return 'high'
    if (l.includes('med')) return 'medium'
    return 'low'
  }
  const s = props.score ?? 0
  if (s >= 0.7) return 'high'
  if (s >= 0.35) return 'medium'
  return 'low'
})

const label = computed(() => {
  switch (computedLevel.value) {
    case 'high':
      return 'High Risk'
    case 'medium':
      return 'Medium'
    case 'low':
      return 'Low Risk'
  }
})

const badgeClass = computed(() => {
  switch (computedLevel.value) {
    case 'high':
      return 'bg-rose-500/10 text-rose-700'
    case 'medium':
      return 'bg-amber-500/10 text-amber-700'
    case 'low':
      return 'bg-emerald-500/10 text-emerald-700'
  }
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center font-medium rounded-full',
      size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs',
      badgeClass,
    ]"
  >
    <span
      class="w-1.5 h-1.5 rounded-full mr-1.5"
      :class="[
        computedLevel === 'high' && 'bg-rose-500',
        computedLevel === 'medium' && 'bg-amber-500',
        computedLevel === 'low' && 'bg-emerald-500',
      ]"
    />
    {{ label }}
  </span>
</template>
