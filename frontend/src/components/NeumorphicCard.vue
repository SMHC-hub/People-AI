<script setup lang="ts">
interface Props {
  elevation?: 'flat' | 'sm' | 'lg' | 'inset'
  hoverable?: boolean
  className?: string
}

withDefaults(defineProps<Props>(), {
  elevation: 'flat',
  hoverable: false,
  className: '',
})
</script>

<template>
  <div
    :class="[
      'transition-all duration-200',
      elevation === 'sm' && 'neu-card-sm',
      elevation === 'flat' && 'neu-card',
      elevation === 'lg' && 'neu-card-elevated',
      elevation === 'inset' && 'neu-input p-5',
      hoverable && 'hover:translate-y-[-2px]',
      className,
    ]"
  >
    <slot />
  </div>
</template>
