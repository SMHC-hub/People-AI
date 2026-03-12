<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import type { ChatMessage as ChatMessageType } from '@/types'
import { sendQuery, askPolicy, getHistory } from '@/api/chatbot'
import ChatMessage from '@/components/ChatMessage.vue'

const messages = ref<ChatMessageType[]>([])
const input = ref('')
const isPolicyMode = ref(false)
const isTyping = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

async function handleSend() {
  const text = input.value.trim()
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
      content: 'Sorry, I encountered an error. Please try again.',
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
    // Start fresh if history fails
  }
  await scrollToBottom()
})
</script>

<template>
  <div class="flex flex-col h-screen">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-gray-900">AI HR Chatbot</h1>
          <p class="text-sm text-gray-500">Ask questions about HR data, policies, and analytics</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-600">Policy Mode</span>
          <button
            @click="isPolicyMode = !isPolicyMode"
            :class="[
              'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
              isPolicyMode ? 'bg-indigo-600' : 'bg-gray-300',
            ]"
          >
            <span
              :class="[
                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                isPolicyMode ? 'translate-x-6' : 'translate-x-1',
              ]"
            />
          </button>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div
      ref="messagesContainer"
      class="flex-1 overflow-y-auto px-6 py-4 bg-gray-50"
    >
      <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <p class="text-lg font-medium">Start a conversation</p>
        <p class="text-sm mt-1">Ask about employees, attendance, policies, or analytics</p>
      </div>

      <div v-else class="max-w-3xl mx-auto">
        <ChatMessage
          v-for="msg in messages"
          :key="msg.id"
          :message="msg.content"
          :timestamp="msg.timestamp"
          :is-user="msg.role === 'user'"
        />

        <!-- Typing indicator -->
        <div v-if="isTyping" class="flex items-start gap-3 mb-4">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-sm font-bold text-white">
            AI
          </div>
          <div class="bg-gray-100 rounded-lg rounded-tl-none px-4 py-2">
            <div class="flex gap-1">
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
              <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="bg-white border-t border-gray-200 px-6 py-4">
      <div class="max-w-3xl mx-auto flex items-end gap-3">
        <div class="flex-1 relative">
          <textarea
            v-model="input"
            @keydown="handleKeydown"
            rows="1"
            placeholder="Type your question..."
            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm resize-none"
            :class="{ 'ring-2 ring-indigo-300': isPolicyMode }"
          />
          <span
            v-if="isPolicyMode"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"
          >
            Policy
          </span>
        </div>
        <button
          @click="handleSend"
          :disabled="!input.trim() || isTyping"
          class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
