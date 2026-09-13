<script setup lang="ts">
interface Props {
  variant?:
    | 'primary'
    | 'accent'
    | 'success'
    | 'warning'
    | 'danger'
    | 'info'
    | 'neutral'
    | 'low'
    | 'medium'
    | 'high'
    | 'critical'
    | 'active'
    | 'terminated'
    | 'on_leave'
  size?: 'sm' | 'md'
  label?: string
}

withDefaults(defineProps<Props>(), {
  variant: 'neutral',
  size: 'md',
  label: '',
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 rounded-full font-bold uppercase tracking-wider select-none',
      size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-1 text-xs',
      (variant === 'primary') && 'bg-neu-primary/10 text-neu-primary border border-neu-primary/20',
      (variant === 'accent') && 'bg-amber-500/15 text-amber-800 border border-amber-300/60',
      (variant === 'success' || variant === 'low' || variant === 'active') && 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
      (variant === 'warning' || variant === 'medium') && 'bg-amber-50 text-amber-700 border border-amber-200/60',
      (variant === 'danger' || variant === 'critical' || variant === 'terminated' || variant === 'high') && 'bg-rose-50 text-rose-700 border border-rose-200/60',
      (variant === 'on_leave' || variant === 'info') && 'bg-blue-50 text-blue-700 border border-blue-200/60',
      (variant === 'neutral') && 'bg-neu-base text-neu-muted border border-neu-border/60',
    ]"
  >
    <span
      :class="[
        'rounded-full flex-shrink-0',
        size === 'sm' ? 'w-1.5 h-1.5' : 'w-2 h-2',
        variant === 'primary' && 'bg-neu-primary',
        variant === 'accent' && 'bg-amber-500',
        (variant === 'success' || variant === 'low' || variant === 'active') && 'bg-emerald-500',
        (variant === 'warning' || variant === 'medium') && 'bg-amber-500',
        (variant === 'danger' || variant === 'critical' || variant === 'terminated' || variant === 'high') && 'bg-rose-500',
        (variant === 'on_leave' || variant === 'info') && 'bg-blue-500',
        variant === 'neutral' && 'bg-neu-muted',
      ]"
    />
    <slot>{{ label }}</slot>
  </span>
</template>
