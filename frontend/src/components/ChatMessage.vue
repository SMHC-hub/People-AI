<script setup lang="ts">
import type { Citation } from '@/types'
import NeumorphicBadge from './NeumorphicBadge.vue'

defineProps<{
  message: string
  timestamp: string
  isUser: boolean
  citations?: Citation[]
}>()

function formatTime(ts: string): string {
  const date = new Date(ts)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div :class="['flex mb-6', isUser ? 'justify-end' : 'justify-start']">
    <div :class="['flex items-start gap-3.5 max-w-[85%] sm:max-w-[75%]', isUser ? 'flex-row-reverse' : 'flex-row']">
      <!-- Avatar -->
      <div
        :class="[
          'flex-shrink-0 w-10 h-10 rounded-2xl flex items-center justify-center text-xs font-black shadow-neu-flat border border-white/50 select-none',
          isUser
            ? 'bg-gradient-to-br from-neu-primary to-blue-600 text-white'
            : 'bg-neu-surface text-neu-primary',
        ]"
      >
        {{ isUser ? 'YOU' : 'AI' }}
      </div>

      <!-- Message Content -->
      <div class="space-y-2">
        <div
          :class="[
            'p-4 rounded-3xl text-sm leading-relaxed whitespace-pre-wrap transition-all duration-150',
            isUser
              ? 'bg-neu-primary text-white shadow-neu-flat rounded-tr-none font-medium'
              : 'bg-neu-surface text-neu-text shadow-neu-flat rounded-tl-none border border-white/60',
          ]"
        >
          {{ message }}
        </div>

        <!-- Document Citations Badge List -->
        <div v-if="citations && citations.length > 0 && !isUser" class="space-y-1.5 pl-1">
          <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted flex items-center space-x-1">
            <svg class="w-3 h-3 text-neu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Verified Policy Citations</span>
          </div>

          <div class="flex flex-wrap gap-2">
            <div
              v-for="(cit, idx) in citations"
              :key="idx"
              class="px-2.5 py-1 rounded-xl bg-neu-base shadow-neu-inset border border-white/40 text-[11px] text-neu-text flex items-center space-x-1.5"
              :title="cit.content_snippet"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              <span class="font-bold">{{ cit.document_title }}</span>
              <span class="text-[10px] font-mono text-neu-primary">
                {{ Math.round((cit.relevance_score || 0.85) * 100) }}% match
              </span>
            </div>
          </div>
        </div>

        <p :class="['text-[10px] text-neu-muted font-medium px-2', isUser ? 'text-right' : 'text-left']">
          {{ formatTime(timestamp) }}
        </p>
      </div>
    </div>
  </div>
</template>
