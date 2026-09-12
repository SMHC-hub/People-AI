<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const collapsed = ref(false)

interface NavItem {
  name: string
  path: string
  icon: string
  badge?: string
}

const navSections: { title: string; items: NavItem[] }[] = [
  {
    title: 'Intelligence',
    items: [
      {
        name: 'Dashboard',
        path: '/',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1',
      },
      {
        name: 'Workforce Risk',
        path: '/workforce',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
      },
      {
        name: 'Employees',
        path: '/employees',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      },
    ],
  },
  {
    title: 'Operations',
    items: [
      {
        name: 'Attendance',
        path: '/attendance',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      },
      {
        name: 'Leaves',
        path: '/leaves',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
      },
      {
        name: 'Payroll & Anomalies',
        path: '/payrolls',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        badge: 'AI',
      },
      {
        name: 'Performance',
        path: '/performances',
        icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
      },
    ],
  },
  {
    title: 'Platform AI',
    items: [
      {
        name: 'AI HR Assistant',
        path: '/chatbot',
        icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        badge: 'RAG',
      },
      {
        name: 'Reports & Briefs',
        path: '/reports',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
      },
      {
        name: 'MLOps & Drift',
        path: '/mlops',
        icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
      },
      {
        name: 'Settings & RBAC',
        path: '/settings',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
      },
    ],
  },
]

function isActive(path: string): boolean {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

function toggleSidebar() {
  collapsed.value = !collapsed.value
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <aside
    :class="[
      'flex flex-col bg-neu-base border-r border-neu-border/70 transition-all duration-300 select-none z-40',
      collapsed ? 'w-20' : 'w-72',
    ]"
    class="min-h-screen sticky top-0 h-screen"
  >
    <!-- Brand Header -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-neu-border/50">
      <div v-if="!collapsed" class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat flex items-center justify-center text-white font-black text-xl">
          P
        </div>
        <div>
          <span class="text-lg font-black tracking-tight text-neu-text block leading-none">
            People<span class="text-neu-primary">AI</span>
          </span>
          <span class="text-[10px] font-semibold tracking-wider uppercase text-neu-muted">
            Workforce SaaS
          </span>
        </div>
      </div>

      <div v-else class="mx-auto">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat flex items-center justify-center text-white font-black text-xl">
          P
        </div>
      </div>

      <button
        @click="toggleSidebar"
        class="p-2 rounded-xl bg-neu-surface shadow-neu-flat hover:shadow-neu-pressed transition-all duration-150 text-neu-muted hover:text-neu-text border border-white/50 focus:outline-none"
        :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            :d="collapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'"
          />
        </svg>
      </button>
    </div>

    <!-- Navigation Scroll Area -->
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-6 scrollbar-thin">
      <div v-for="section in navSections" :key="section.title">
        <div
          v-if="!collapsed"
          class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-neu-muted/80"
        >
          {{ section.title }}
        </div>
        <div class="space-y-1.5">
          <router-link
            v-for="item in section.items"
            :key="item.path"
            :to="item.path"
            :class="[
              'flex items-center px-3.5 py-2.5 rounded-2xl text-sm font-semibold transition-all duration-200 group',
              isActive(item.path)
                ? 'bg-neu-base shadow-neu-inset text-neu-primary border border-white/40'
                : 'text-neu-muted hover:text-neu-text hover:bg-neu-surface hover:shadow-neu-flat',
            ]"
            :title="collapsed ? item.name : undefined"
          >
            <svg
              class="w-5 h-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110"
              :class="isActive(item.path) ? 'text-neu-primary' : 'text-neu-muted group-hover:text-neu-text'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            <span v-if="!collapsed" class="ml-3 flex-1 truncate">{{ item.name }}</span>
            <span
              v-if="!collapsed && item.badge"
              class="ml-auto text-[10px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-neu-primary/10 text-neu-primary"
            >
              {{ item.badge }}
            </span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Footer / Model Status & Logout -->
    <div class="p-4 border-t border-neu-border/60 space-y-3">
      <!-- MLOps Status Pill -->
      <div
        v-if="!collapsed"
        class="px-3 py-2 rounded-2xl bg-neu-surface shadow-neu-inset border border-white/30 text-xs flex items-center justify-between"
      >
        <div class="flex items-center space-x-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-bold text-neu-text text-[11px]">XGBoost v2.1.0</span>
        </div>
        <span class="text-[10px] font-mono font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">
          PSI 0.04
        </span>
      </div>

      <!-- Logout Button -->
      <button
        @click="handleLogout"
        :class="[
          'flex items-center w-full px-3.5 py-2.5 rounded-2xl text-sm font-bold text-neu-muted hover:text-rose-600 hover:bg-rose-50/50 hover:shadow-neu-flat transition-all duration-200 border border-transparent hover:border-rose-200/40',
          collapsed ? 'justify-center' : '',
        ]"
      >
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
          />
        </svg>
        <span v-if="!collapsed" class="ml-3">Sign Out</span>
      </button>
    </div>
  </aside>
</template>
