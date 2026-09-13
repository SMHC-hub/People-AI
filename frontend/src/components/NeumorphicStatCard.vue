<script setup lang="ts">
import NeumorphicCard from './NeumorphicCard.vue'

interface Props {
  title: string
  value: string | number
  change?: string
  changeType?: 'positive' | 'negative' | 'neutral'
  caption?: string
  subtitle?: string
  trend?: string
  badgeText?: string
  badgeVariant?: 'primary' | 'success' | 'warning' | 'danger' | 'neutral'
  iconBg?: string
}

withDefaults(defineProps<Props>(), {
  change: '',
  changeType: 'neutral',
  caption: '',
  subtitle: '',
  trend: '',
  badgeText: '',
  badgeVariant: 'neutral',
})
</script>

<template>
  <NeumorphicCard elevation="flat" class="p-5 flex flex-col justify-between">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold uppercase tracking-wider text-neu-muted">
        {{ title }}
      </span>
      <div
        v-if="$slots.icon"
        class="w-8 h-8 rounded-xl bg-neu-primary/10 flex items-center justify-center text-neu-primary"
      >
        <slot name="icon" />
      </div>
      <span
        v-else-if="badgeText"
        class="text-[10px] font-semibold tracking-wide uppercase px-2 py-0.5 rounded-full"
        :class="[
          badgeVariant === 'primary' && 'bg-neu-primary/10 text-neu-primary',
          badgeVariant === 'success' && 'bg-emerald-500/10 text-emerald-700',
          badgeVariant === 'warning' && 'bg-amber-500/10 text-amber-700',
          badgeVariant === 'danger' && 'bg-rose-500/10 text-rose-700',
          badgeVariant === 'neutral' && 'bg-neu-muted/15 text-neu-muted',
        ]"
      >
        {{ badgeText }}
      </span>
    </div>

    <div class="mt-2">
      <div class="text-2xl lg:text-3xl font-extrabold text-neu-text tracking-tight leading-tight">
        {{ value }}
      </div>
      <div v-if="change || trend || caption || subtitle" class="flex items-center gap-1.5 mt-2">
        <span
          v-if="change"
          class="text-xs font-semibold flex items-center gap-1"
          :class="[
            changeType === 'positive' && 'text-emerald-600',
            changeType === 'negative' && 'text-rose-600',
            changeType === 'neutral' && 'text-neu-muted',
          ]"
        >
          <svg
            v-if="changeType === 'positive'"
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
          <svg
            v-else-if="changeType === 'negative'"
            class="w-3.5 h-3.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          {{ change }}
        </span>

        <span v-if="trend && !change" class="text-xs font-medium text-emerald-600">
          {{ trend }}
        </span>

        <span v-if="caption || subtitle" class="text-xs text-neu-muted">
          {{ caption || subtitle }}
        </span>
      </div>
    </div>
  </NeumorphicCard>
</template>
