<script setup lang="ts">
import NeumorphicCard from './NeumorphicCard.vue'

interface Props {
  title: string
  value: string | number
  change?: string
  changeType?: 'positive' | 'negative' | 'neutral'
  caption?: string
  badgeText?: string
  badgeVariant?: 'primary' | 'success' | 'warning' | 'danger' | 'neutral'
}

withDefaults(defineProps<Props>(), {
  change: '',
  changeType: 'neutral',
  caption: '',
  badgeText: '',
  badgeVariant: 'neutral',
})
</script>

<template>
  <NeumorphicCard elevation="flat" class="p-5 flex flex-col justify-between">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold uppercase tracking-wider text-text-secondary">
        {{ title }}
      </span>
      <div v-if="$slots.icon" class="w-9 h-9 rounded-neu-sm neu-card-sm flex items-center justify-center text-primary">
        <slot name="icon" />
      </div>
      <span
        v-else-if="badgeText"
        :class="[
          'neu-badge text-xs',
          badgeVariant === 'primary' && 'bg-blue-50 text-primary',
          badgeVariant === 'success' && 'bg-emerald-50 text-status-success',
          badgeVariant === 'warning' && 'bg-amber-50 text-status-warning',
          badgeVariant === 'danger' && 'bg-red-50 text-status-danger',
          badgeVariant === 'neutral' && 'bg-gray-100 text-text-secondary',
        ]"
      >
        {{ badgeText }}
      </span>
    </div>

    <div>
      <div class="text-2xl lg:text-3xl font-bold text-text tracking-tight">
        {{ value }}
      </div>
      <div v-if="change || caption" class="flex items-center gap-2 mt-2">
        <span
          v-if="change"
          :class="[
            'text-xs font-semibold flex items-center gap-1',
            changeType === 'positive' && 'text-status-success',
            changeType === 'negative' && 'text-status-danger',
            changeType === 'neutral' && 'text-text-secondary',
          ]"
        >
          <svg
            v-if="changeType === 'positive'"
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
          <svg
            v-else-if="changeType === 'negative'"
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          {{ change }}
        </span>
        <span v-if="caption" class="text-xs text-text-secondary">
          {{ caption }}
        </span>
      </div>
    </div>
  </NeumorphicCard>
</template>
