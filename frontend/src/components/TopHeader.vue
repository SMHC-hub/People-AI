<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const emit = defineEmits<{
  (e: 'open-command-palette'): void
}>()

const router = useRouter()
const authStore = useAuthStore()
const notificationsOpen = ref(false)

const notifications = [
  { id: 1, title: 'Payroll Alert', time: '12m ago', desc: 'Overtime spike detected for Marcus Vance' },
  { id: 2, title: 'System Check', time: '1h ago', desc: 'Workforce telemetry and models stable' },
  { id: 3, title: 'Attrition Alert', time: '3h ago', desc: 'Engineering department risk increased' },
]

function openCommandPalette() {
  emit('open-command-palette')
}

function navigateToChat() {
  router.push('/chatbot')
}

function navigateToPayroll() {
  notificationsOpen.value = false
  router.push('/payrolls')
}
</script>

<template>
  <header class="h-16 px-6 lg:px-8 flex items-center justify-between border-b border-neu-border/40 bg-neu-base/90 backdrop-blur-md sticky top-0 z-30">
    <!-- Left: Compact Organization Context -->
    <div class="flex items-center">
      <div class="flex items-center space-x-2 text-xs text-neu-text font-medium">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span class="font-bold text-neu-text tracking-tight">Acme Global Technologies</span>
        <span class="text-[10px] uppercase font-semibold tracking-wider text-neu-muted px-1.5 py-0.5 rounded bg-neu-surface">
          {{ authStore.user?.role === 'employee' ? 'Employee Portal' : 'Enterprise' }}
        </span>
      </div>
    </div>

    <!-- Right: Search, Ask AI, Notifications, User Profile -->
    <div class="flex items-center space-x-3">
      <!-- Compact Global Search -->
      <button
        @click="openCommandPalette"
        type="button"
        class="hidden sm:flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-neu-surface shadow-neu-flat-sm text-xs text-neu-muted hover:text-neu-text transition-colors focus:outline-none"
        title="Search (Ctrl + K)"
      >
        <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="text-xs">Search...</span>
        <kbd class="text-[10px] font-semibold bg-neu-base px-1.5 py-0.5 rounded text-neu-muted/80">
          Ctrl K
        </kbd>
      </button>

      <!-- Clean Ask AI Trigger with 3rd Accent Contrast -->
      <button
        @click="navigateToChat"
        type="button"
        class="btn-accent flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none cursor-pointer"
        title="Ask AI Assistant"
      >
        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span>Ask AI</span>
      </button>

      <!-- Notifications -->
      <div class="relative">
        <button
          @click="notificationsOpen = !notificationsOpen"
          type="button"
          class="relative p-2 rounded-xl text-neu-muted hover:text-neu-text hover:bg-neu-surface transition-colors focus:outline-none"
          title="Notifications"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-rose-500"></span>
        </button>

        <!-- Dropdown Card -->
        <div
          v-if="notificationsOpen"
          class="absolute right-0 mt-2 w-72 rounded-2xl bg-neu-surface shadow-neu-flat-lg p-3.5 z-50 text-left border border-neu-border/30"
        >
          <div class="flex items-center justify-between pb-2 border-b border-neu-border/40">
            <h4 class="text-xs font-bold text-neu-text">Notifications</h4>
            <span class="text-[11px] font-medium text-neu-primary cursor-pointer hover:underline" @click="navigateToPayroll">
              View all
            </span>
          </div>
          <div class="divide-y divide-neu-border/30 mt-1">
            <div
              v-for="item in notifications"
              :key="item.id"
              class="py-2 cursor-pointer hover:opacity-75 transition-opacity"
              @click="navigateToPayroll"
            >
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-neu-text">{{ item.title }}</span>
                <span class="text-[10px] text-neu-muted">{{ item.time }}</span>
              </div>
              <p class="text-[11px] text-neu-muted mt-0.5 leading-snug">{{ item.desc }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Profile Avatar & Name -->
      <div class="flex items-center space-x-2 pl-2 border-l border-neu-border/40">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat-sm flex items-center justify-center text-white font-bold text-xs">
          {{ (authStore.user?.full_name || authStore.user?.name || 'U').charAt(0).toUpperCase() }}
        </div>
        <div class="hidden md:block text-left">
          <div class="text-xs font-bold text-neu-text leading-tight">
            {{ authStore.user?.full_name || authStore.user?.name || 'User' }}
          </div>
          <div class="text-[10px] font-medium text-neu-muted capitalize leading-none">
            {{ authStore.user?.role === 'employee' ? (authStore.user?.employee?.position?.title ? (authStore.user.employee.position.title + ' • ' + (authStore.user.employee.department?.name || '')) : 'Staff Member') : (authStore.user?.role || 'admin').replace('_', ' ') }}
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
