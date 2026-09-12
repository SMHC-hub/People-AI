<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NeumorphicBadge from './NeumorphicBadge.vue'

const emit = defineEmits<{
  (e: 'open-command-palette'): void
}>()

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sectionCategory = computed(() => {
  const sections: Record<string, string> = {
    dashboard: 'Workforce Intelligence',
    workforce: 'Turnover Intelligence',
    employees: 'Talent Directory',
    'employee-detail': 'Talent Directory',
    attendance: 'Operations & Timesheet',
    leaves: 'Absence Management',
    payrolls: 'Financial Operations',
    performances: 'Talent Reviews',
    reports: 'Executive Analytics',
    chatbot: 'Policy Intelligence',
    mlops: 'ModelOps Governance',
    settings: 'Tenant Administration',
  }
  return sections[route.name as string] || 'Enterprise SaaS'
})

const notificationsOpen = ref(false)
const notifications = [
  { id: 1, title: 'Payroll Anomaly Flagged', time: '12m ago', desc: 'Overtime spike detected for Marcus Vance ($1,420)', type: 'warning' },
  { id: 2, title: 'PSI Drift Check Complete', time: '1h ago', desc: 'All workforce metrics stable (Max PSI 0.07)', type: 'info' },
  { id: 3, title: 'Turnover Alert', time: '3h ago', desc: 'Sales department risk index rose by 4.2%', type: 'error' },
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
  <header class="h-20 px-8 flex items-center justify-between border-b border-neu-border/60 bg-neu-base/80 backdrop-blur-md sticky top-0 z-30">
    <!-- Left: Tenant & Workspace Context (Title shown once on the page view) -->
    <div class="flex items-center space-x-3">
      <div class="flex items-center space-x-2.5 px-4 py-2 rounded-2xl bg-neu-surface shadow-neu-inset text-xs font-semibold text-neu-text border border-white/50">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="font-bold text-neu-text tracking-tight">Acme Global Technologies</span>
        <span class="text-neu-muted/60">&bull;</span>
        <span class="text-neu-primary font-bold text-[11px] uppercase tracking-wider">{{ sectionCategory }}</span>
        <NeumorphicBadge variant="primary" size="sm">Enterprise</NeumorphicBadge>
      </div>
    </div>

    <!-- Center: Global Command Palette Trigger -->
    <div class="flex-1 max-w-md mx-6 hidden md:block">
      <button
        @click="openCommandPalette"
        type="button"
        class="w-full flex items-center justify-between px-4 py-2.5 rounded-2xl bg-neu-surface shadow-neu-inset text-sm text-neu-muted hover:text-neu-text transition-all duration-200 border border-white/30 group"
      >
        <div class="flex items-center space-x-2.5">
          <svg class="w-4 h-4 text-neu-muted group-hover:text-neu-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span>Search employees, anomalies, models...</span>
        </div>
        <kbd class="px-2 py-0.5 text-xs font-semibold bg-neu-base rounded-md shadow-neu-flat text-neu-muted border border-neu-border/40">
          Ctrl K
        </kbd>
      </button>
    </div>

    <!-- Right: Groq Status, Notifications, Profile -->
    <div class="flex items-center space-x-4">
      <!-- Groq AI Quick Trigger -->
      <button
        @click="navigateToChat"
        class="hidden sm:flex items-center space-x-2 px-3.5 py-2 rounded-2xl bg-neu-surface shadow-neu-flat hover:shadow-neu-pressed transition-all duration-200 text-xs font-bold text-neu-text border border-white/50 group"
        title="Open Groq-Powered AI HR Assistant"
      >
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
        </span>
        <span class="group-hover:text-neu-primary transition-colors">Groq AI</span>
        <span class="text-[10px] px-1.5 py-0.5 rounded bg-neu-primary/10 text-neu-primary font-mono">120B</span>
      </button>

      <!-- Notifications Dropdown -->
      <div class="relative">
        <button
          @click="notificationsOpen = !notificationsOpen"
          class="relative p-2.5 rounded-2xl bg-neu-surface shadow-neu-flat hover:shadow-neu-pressed transition-all duration-200 text-neu-muted hover:text-neu-text border border-white/50 focus:outline-none"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-neu-base"></span>
        </button>

        <!-- Dropdown Card -->
        <div
          v-if="notificationsOpen"
          class="absolute right-0 mt-3 w-80 rounded-2xl bg-neu-surface shadow-neu-raised border border-white/60 p-4 z-50 animate-fadeIn"
        >
          <div class="flex items-center justify-between pb-3 border-b border-neu-border/50">
            <h4 class="text-sm font-bold text-neu-text">Real-Time Alerts</h4>
            <span class="text-[11px] font-semibold text-neu-primary cursor-pointer" @click="navigateToPayroll">View all</span>
          </div>
          <div class="divide-y divide-neu-border/30 mt-2 space-y-2">
            <div
              v-for="item in notifications"
              :key="item.id"
              class="pt-2 text-left cursor-pointer hover:opacity-80"
              @click="navigateToPayroll"
            >
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-neu-text">{{ item.title }}</span>
                <span class="text-[10px] text-neu-muted">{{ item.time }}</span>
              </div>
              <p class="text-xs text-neu-muted mt-0.5 leading-snug">{{ item.desc }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Profile Pill -->
      <div class="flex items-center space-x-3 pl-2 border-l border-neu-border/60">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat flex items-center justify-center text-white font-black text-sm">
          {{ authStore.user?.full_name?.charAt(0) || 'A' }}
        </div>
        <div class="hidden lg:block text-left">
          <div class="text-xs font-bold text-neu-text leading-tight">
            {{ authStore.user?.full_name || 'Admin User' }}
          </div>
          <div class="text-[11px] font-medium text-neu-muted capitalize">
            {{ (authStore.user?.role || 'admin').replace('_', ' ') }}
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
