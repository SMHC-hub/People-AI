<script setup lang="ts">
import { computed } from 'vue'
import { formatStatus } from '@/utils/formatters'

const props = defineProps<{
  status: string
  size?: 'sm' | 'md'
}>()

const formattedStatus = computed(() => {
  return formatStatus(props.status)
})

const badgeClass = computed(() => {
  const s = props.status?.toLowerCase() || ''
  if (s === 'active' || s === 'approved' || s === 'resolved' || s === 'present') {
    return 'bg-emerald-500/10 text-emerald-700'
  }
  if (s === 'on_leave' || s === 'leave' || s === 'pending' || s === 'in_review' || s === 'late' || s === 'half_day' || s.includes('early')) {
    return 'bg-amber-500/10 text-amber-700'
  }
  if (s === 'terminated' || s === 'rejected' || s === 'danger' || s === 'absent') {
    return 'bg-rose-500/10 text-rose-700'
  }
  return 'bg-neu-muted/15 text-neu-muted'
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
        badgeClass.includes('emerald') && 'bg-emerald-500',
        badgeClass.includes('amber') && 'bg-amber-500',
        badgeClass.includes('rose') && 'bg-rose-500',
        badgeClass.includes('neu-muted') && 'bg-neu-muted',
      ]"
    />
    {{ formattedStatus }}
  </span>
</template>
