<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  change?: number
  changeLabel?: string
}>()
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ value }}</p>
        <div v-if="change !== undefined" class="mt-2 flex items-center text-sm">
          <span
            :class="[
              'font-medium',
              change >= 0 ? 'text-green-600' : 'text-red-600',
            ]"
          >
            {{ change >= 0 ? '+' : '' }}{{ change }}%
          </span>
          <span v-if="changeLabel" class="ml-1 text-gray-500">{{ changeLabel }}</span>
        </div>
      </div>
      <div class="p-3 bg-indigo-50 rounded-full">
        <slot name="icon">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </slot>
      </div>
    </div>
  </div>
</template>
