<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import type { ChatMessage as ChatMessageType } from '@/types'
import { sendQuery, askPolicy, getHistory } from '@/api/chatbot'
import ChatMessage from '@/components/ChatMessage.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const messages = ref<ChatMessageType[]>([])
const input = ref('')
const isPolicyMode = ref(true)
const isTyping = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

const suggestedPrompts = [
  'What is the annual professional development budget per employee?',
  'How are overtime anomalies detected by the platform?',
  'What is the standard parental leave policy duration?',
  'What are the core retention factors for engineers?',
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

  const userMessage: ChatMessageType = {
    id: `user-${Date.now()}`,
    content: text,
    role: 'user',
    timestamp: new Date().toISOString(),
    is_policy_question: isPolicyMode.value,
  }

  messages.value.push(userMessage)
  input.value = ''
  isTyping.value = true
  await scrollToBottom()

  try {
    const response = isPolicyMode.value
      ? await askPolicy(text)
      : await sendQuery(text)
    messages.value.push(response)
  } catch {
    messages.value.push({
      id: `error-${Date.now()}`,
      content: 'I apologize, but I encountered a processing error communicating with the Groq inference service. Please retry.',
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

onMounted(async () => {
  try {
    messages.value = await getHistory()
  } catch {
    // Start fresh
  }
  if (messages.value.length === 0) {
    messages.value.push({
      id: 'welcome-1',
      content: 'Hello! I am your PeopleAI Workforce & Policy Intelligence Assistant, powered by Groq LLM inference. I can answer questions grounded in Acme Global Technologies company policies, analyze turnover risk trends, and explain anomaly detection flags.\n\nHow may I assist you today?',
      role: 'assistant',
      timestamp: new Date().toISOString(),
    })
  }
  await scrollToBottom()
})
</script>

<template>
  <div class="flex flex-col h-[calc(100vh-8rem)] max-w-5xl mx-auto pb-4">
    <!-- Chat Header Card -->
    <NeumorphicCard class="mb-4 !p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat flex items-center justify-center text-white font-black text-sm">
          AI
        </div>
        <div>
          <div class="flex items-center space-x-2">
            <h3 class="text-base font-black text-neu-text tracking-tight">AI HR Policy Assistant</h3>
            <NeumorphicBadge variant="success" size="sm">Groq GPT-OSS 120B</NeumorphicBadge>
          </div>
          <p class="text-xs text-neu-muted">Document-grounded RAG with verifiable policy citations</p>
        </div>
      </div>

      <!-- Policy Mode Toggle -->
      <div class="flex items-center space-x-3">
        <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Policy Grounding:</span>
        <button
          @click="isPolicyMode = !isPolicyMode"
          class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200"
          :class="isPolicyMode ? 'bg-neu-primary text-white shadow-neu-pressed' : 'bg-neu-base text-neu-muted shadow-neu-flat'"
        >
          {{ isPolicyMode ? 'STRICT RAG (Active)' : 'General Chat' }}
        </button>
      </div>
    </NeumorphicCard>

    <!-- Messages Window -->
    <div
      ref="messagesContainer"
      class="flex-1 overflow-y-auto px-6 py-6 rounded-3xl bg-neu-base shadow-neu-inset border border-white/40 scrollbar-thin"
    >
      <ChatMessage
        v-for="msg in messages"
        :key="msg.id"
        :message="msg.content"
        :timestamp="msg.timestamp"
        :is-user="msg.role === 'user'"
        :citations="msg.citations"
      />

      <!-- Typing Indicator -->
      <div v-if="isTyping" class="flex items-center space-x-3 text-xs font-semibold text-neu-muted pl-4">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neu-primary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-neu-primary"></span>
        </span>
        <span>Groq LLM is synthesizing document citations...</span>
      </div>
    </div>

    <!-- Suggested Prompts Pill Row -->
    <div class="py-3 flex items-center space-x-2 overflow-x-auto no-scrollbar">
      <button
        v-for="(prompt, idx) in suggestedPrompts"
        :key="idx"
        @click="handleSend(prompt)"
        class="whitespace-nowrap px-3.5 py-1.5 rounded-xl bg-neu-surface shadow-neu-flat hover:shadow-neu-pressed text-xs font-semibold text-neu-muted hover:text-neu-text border border-white/50 transition-all duration-150"
      >
        {{ prompt }}
      </button>
    </div>

    <!-- Input Box -->
    <div class="relative">
      <textarea
        v-model="input"
        @keydown="handleKeydown"
        rows="2"
        placeholder="Ask a policy question or query workforce intelligence (Press Enter to send)..."
        class="w-full pl-5 pr-28 py-3.5 rounded-3xl bg-neu-surface shadow-neu-inset text-xs font-medium text-neu-text border border-white/60 focus:outline-none resize-none"
      ></textarea>

      <div class="absolute right-3.5 top-3.5 flex items-center space-x-2">
        <NeumorphicButton
          variant="primary"
          size="sm"
          :disabled="!input.trim() || isTyping"
          @click="() => handleSend()"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          Send
        </NeumorphicButton>
      </div>
    </div>
  </div>
</template>
