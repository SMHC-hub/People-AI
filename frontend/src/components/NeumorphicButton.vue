<script setup lang="ts">
interface Props {
  variant?: 'default' | 'primary' | 'accent' | 'danger' | 'ghost' | 'inset'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
  type?: 'button' | 'submit' | 'reset'
}

withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
  disabled: false,
  loading: false,
  type: 'button',
})

defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-semibold transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed select-none',
      size === 'sm' && 'px-3 py-1.5 text-xs rounded-xl gap-1.5',
      size === 'md' && 'px-4 py-2 text-xs rounded-xl gap-2',
      size === 'lg' && 'px-6 py-2.5 text-sm rounded-xl gap-2.5',
      variant === 'default' && 'btn-secondary',
      variant === 'primary' && 'btn-primary',
      variant === 'accent' && 'btn-accent',
      variant === 'danger' && 'bg-rose-600 text-white rounded-xl shadow-md hover:bg-rose-700',
      variant === 'ghost' && 'bg-transparent hover:bg-neu-surface text-neu-muted hover:text-neu-text',
      variant === 'inset' && 'shadow-neu-inset text-neu-primary font-semibold',
    ]"
    @click="$emit('click', $event)"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </button>
</template>
