<script setup lang="ts">
defineProps<{
  message: string
  timestamp: string
  isUser: boolean
}>()

function formatTime(ts: string): string {
  const date = new Date(ts)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div :class="['flex mb-4', isUser ? 'justify-end' : 'justify-start']">
    <div :class="['flex items-start gap-3 max-w-[75%]', isUser ? 'flex-row-reverse' : 'flex-row']">
      <div
        :class="[
          'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white',
          isUser ? 'bg-indigo-600' : 'bg-gray-600',
        ]"
      >
        {{ isUser ? 'U' : 'AI' }}
      </div>
      <div>
        <div
          :class="[
            'px-4 py-2 rounded-lg text-sm',
            isUser
              ? 'bg-indigo-600 text-white rounded-tr-none'
              : 'bg-gray-100 text-gray-900 rounded-tl-none',
          ]"
        >
          {{ message }}
        </div>
        <p :class="['text-xs text-gray-400 mt-1', isUser ? 'text-right' : 'text-left']">
          {{ formatTime(timestamp) }}
        </p>
      </div>
    </div>
  </div>
</template>
