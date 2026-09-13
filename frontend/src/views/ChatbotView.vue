<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import type { ChatMessage as ChatMessageType } from '@/types'
import { sendQuery, askPolicy, getHistory } from '@/api/chatbot'
import ChatMessage from '@/components/ChatMessage.vue'
import PageHeader from '@/components/PageHeader.vue'

const messages = ref<ChatMessageType[]>([])
const input = ref('')
const isPolicyMode = ref(false)
const isTyping = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

const suggestedPrompts = [
  'Which teams have the highest attrition risk?',
  'Show attendance anomalies.',
  'What is our leave policy?',
  'Summarize this month\'s workforce trends.',
]

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

async function handleSend(textToSend?: string) {
  const text = (textToSend || input.value).trim()
  if (!text) return

  const textLower = text.toLowerCase()
  const isPolicyTopic =
    textLower.includes('policy') ||
    textLower.includes('rule') ||
    textLower.includes('conduct') ||
    textLower.includes('leave policy') ||
    textLower.includes('parental') ||
    textLower.includes('sick leave')

  const isAnalyticsTopic =
    textLower.includes('attrition') ||
    textLower.includes('turnover') ||
    textLower.includes('teams') ||
    textLower.includes('risk') ||
    textLower.includes('headcount') ||
    textLower.includes('salary') ||
    textLower.includes('trend')

  const routeToPolicy = (isPolicyMode.value && !isAnalyticsTopic) || isPolicyTopic

  const userMessage: ChatMessageType = {
    id: `user-${Date.now()}`,
    content: text,
    role: 'user',
    timestamp: new Date().toISOString(),
    is_policy_question: routeToPolicy,
  }

  messages.value.push(userMessage)
  input.value = ''
  isTyping.value = true
  await scrollToBottom()

  try {
    const response = routeToPolicy
      ? await askPolicy(text)
      : await sendQuery(text)
    messages.value.push(response)
  } catch {
    messages.value.push({
      id: `error-${Date.now()}`,
      content: 'I apologize, but I could not process your request. Please try asking again.',
      role: 'assistant',
      timestamp: new Date().toISOString(),
      is_policy_question: false,
    })
  } finally {
    isTyping.value = false
    await scrollToBottom()
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    handleSend()
  }
}

function clearChat() {
  messages.value = []
}

onMounted(async () => {
  try {
    const history = await getHistory()
    if (history && history.length > 0) {
      messages.value = history
    }
  } catch {
    messages.value = []
  }
  await scrollToBottom()
})
</script>

<template>
  <div class="flex flex-col h-[calc(100vh-7rem)] max-w-4xl mx-auto">
    <!-- Header -->
    <PageHeader
      title="AI Assistant"
      subtitle="Ask questions about your workforce."
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <button
            @click="isPolicyMode = !isPolicyMode"
            type="button"
            :class="isPolicyMode ? 'btn-accent px-3 py-1.5 text-xs font-semibold' : 'btn-secondary px-3 py-1.5 text-xs font-semibold'"
          >
            {{ isPolicyMode ? 'Policy RAG' : 'General Query' }}
          </button>
          <button
            v-if="messages.length > 0"
            @click="clearChat"
            type="button"
            class="btn-secondary px-2.5 py-1.5 text-xs font-semibold"
          >
            Clear
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Welcome Hero (Empty State) -->
    <div
      v-if="messages.length === 0"
      class="flex-1 flex flex-col items-center justify-center text-center px-4 -mt-10"
    >
      <div class="w-12 h-12 rounded-2xl bg-neu-primary/10 text-neu-primary flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
      </div>

      <h2 class="text-xl font-bold tracking-tight text-neu-text mb-2">
        How can I help with your workforce?
      </h2>
      <p class="text-xs text-neu-muted max-w-md mb-6">
        Ask about employee metrics, attendance trends, company policies, or payroll anomalies.
      </p>

      <!-- Centered Input in Hero -->
      <div class="w-full max-w-xl neu-card p-2 shadow-neu-flat mb-4 flex items-center">
        <input
          v-model="input"
          @keydown="handleKeydown"
          type="text"
          placeholder="Ask about employees, attendance, payroll or policies..."
          class="flex-1 px-3 py-2 bg-transparent text-xs text-neu-text focus:outline-none placeholder-neu-muted"
        />
        <button
          @click="handleSend()"
          type="button"
          :disabled="!input.trim()"
          class="btn-primary px-4 py-2 text-xs font-semibold disabled:opacity-40 transition-opacity focus:outline-none"
        >
          Send
        </button>
      </div>

      <!-- Suggested Question Chips -->
      <div class="flex flex-wrap items-center justify-center gap-2 max-w-xl">
        <button
          v-for="(prompt, idx) in suggestedPrompts"
          :key="idx"
          @click="handleSend(prompt)"
          type="button"
          class="btn-secondary px-3 py-1.5 text-xs text-neu-text hover:border-neu-primary hover:text-neu-primary transition-all focus:outline-none"
        >
          {{ prompt }}
        </button>
      </div>
    </div>

    <!-- Active Message Conversation Thread -->
    <div
      v-else
      ref="messagesContainer"
      class="flex-1 overflow-y-auto px-4 py-4 space-y-3 rounded-2xl bg-neu-base shadow-neu-inset scrollbar-thin"
    >
      <ChatMessage
        v-for="msg in messages"
        :key="msg.id"
        :message="msg.content"
        :timestamp="msg.timestamp"
        :is-user="msg.role === 'user'"
        :citations="msg.citations"
      />

      <div v-if="isTyping" class="flex items-center space-x-2 text-xs text-neu-muted pl-3 py-2">
        <span class="w-2 h-2 rounded-full bg-neu-primary animate-pulse"></span>
        <span>Thinking...</span>
      </div>
    </div>

    <!-- Bottom Input Bar (when conversation is active) -->
    <div v-if="messages.length > 0" class="pt-3 space-y-2">
      <div class="neu-card p-2 flex items-center shadow-neu-flat">
        <input
          v-model="input"
          @keydown="handleKeydown"
          type="text"
          placeholder="Ask about employees, attendance, payroll or policies..."
          class="flex-1 px-3 py-2 bg-transparent text-xs text-neu-text focus:outline-none placeholder-neu-muted"
        />
        <button
          @click="handleSend()"
          type="button"
          :disabled="!input.trim() || isTyping"
          class="btn-primary px-4 py-2 text-xs font-semibold disabled:opacity-40 transition-opacity focus:outline-none"
        >
          Send
        </button>
      </div>

      <!-- Quick prompts underneath -->
      <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar py-1">
        <button
          v-for="(prompt, idx) in suggestedPrompts"
          :key="idx"
          @click="handleSend(prompt)"
          type="button"
          class="btn-secondary px-2.5 py-1 text-[11px] text-neu-text hover:border-neu-primary hover:text-neu-primary whitespace-nowrap transition-all focus:outline-none"
        >
          {{ prompt }}
        </button>
      </div>
    </div>
  </div>
</template>
