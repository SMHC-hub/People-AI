<script setup lang="ts">
interface Props {
  variant?: 'default' | 'primary' | 'danger' | 'ghost' | 'inset'
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
      'inline-flex items-center justify-center font-medium transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed',
      size === 'sm' && 'px-3 py-1.5 text-xs rounded-neu-sm gap-1.5',
      size === 'md' && 'px-4 py-2 text-sm rounded-neu gap-2',
      size === 'lg' && 'px-6 py-3 text-base rounded-neu-lg gap-2.5',
      variant === 'default' && 'neu-btn hover:text-primary',
      variant === 'primary' && 'neu-btn-primary',
      variant === 'danger' && 'bg-status-danger text-white rounded-neu shadow-md hover:bg-red-700',
      variant === 'ghost' && 'bg-transparent hover:bg-surface text-text-secondary hover:text-text',
      variant === 'inset' && 'neu-pressed text-primary font-semibold',
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
