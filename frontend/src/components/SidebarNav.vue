<script setup lang="ts">
import { ref, computed } from 'vue'
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

const employeeSections: { title: string; items: NavItem[] }[] = [
  {
    title: 'Workspace',
    items: [
      {
        name: 'My Overview',
        path: '/portal',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1',
      },
      {
        name: 'My Attendance',
        path: '/portal/attendance',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      },
      {
        name: 'My Leaves',
        path: '/portal/leaves',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
      },
      {
        name: 'My Payslips',
        path: '/portal/payrolls',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      },
      {
        name: 'My Performance',
        path: '/portal/performance',
        icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
      },
    ],
  },
  {
    title: 'Support',
    items: [
      {
        name: 'Policy Assistant',
        path: '/chatbot',
        icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
      },
      {
        name: 'My Profile',
        path: '/portal/profile',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
      },
    ],
  },
]

const adminSections: { title: string; items: NavItem[] }[] = [
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
        name: 'Payroll',
        path: '/payrolls',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      },
      {
        name: 'Performance',
        path: '/performances',
        icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
      },
    ],
  },
  {
    title: 'AI',
    items: [
      {
        name: 'AI Assistant',
        path: '/chatbot',
        icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
      },
      {
        name: 'Reports',
        path: '/reports',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
      },
    ],
  },
  {
    title: 'Admin',
    items: [
      {
        name: 'Models',
        path: '/mlops',
        icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
      },
      {
        name: 'Settings',
        path: '/settings',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
      },
    ],
  },
]

const navSections = computed(() => {
  return authStore.user?.role === 'employee' ? employeeSections : adminSections
})

function isActive(path: string): boolean {
  const current = route.path
  if (path === '/portal') return current === '/portal'
  if (path === '/portal/attendance') return current === '/portal/attendance'
  if (path === '/portal/leaves') return current === '/portal/leaves'
  if (path === '/portal/payrolls') return current === '/portal/payrolls'
  if (path === '/portal/performance') return current === '/portal/performance'
  if (path === '/portal/profile') return current === '/portal/profile'
  if (path === '/') return current === '/' || current === '/dashboard'
  if (path === '/workforce') return current === '/workforce' || current === '/workforce-risk'
  if (path === '/employees') return current === '/employees' || current.startsWith('/employees/')
  if (path === '/attendance') return current === '/attendance'
  if (path === '/leaves') return current === '/leaves'
  if (path === '/payrolls') return current === '/payrolls' || current === '/payroll'
  if (path === '/performances') return current === '/performances' || current === '/performance'
  if (path === '/chatbot') return current === '/chatbot' || current === '/ai-assistant' || current === '/chat'
  if (path === '/reports') return current === '/reports'
  if (path === '/mlops') return current === '/mlops' || current === '/models'
  if (path === '/settings') return current === '/settings'
  return current.startsWith(path)
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
      'flex flex-col bg-neu-base transition-all duration-300 select-none z-40',
      collapsed ? 'w-20' : 'w-64',
    ]"
    class="min-h-screen sticky top-0 h-screen border-r border-neu-border/50"
  >
    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-neu-border/40">
      <router-link
        :to="authStore.user?.role === 'employee' ? '/portal' : '/'"
        class="flex items-center space-x-3 focus:outline-none"
      >
        <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat-sm flex items-center justify-center text-white font-bold text-base">
          P
        </div>
        <div v-if="!collapsed">
          <span class="text-base font-bold tracking-tight text-neu-text block leading-none">
            People<span class="text-neu-primary font-extrabold">AI</span>
          </span>
          <span class="text-[10px] font-medium tracking-wide text-neu-muted mt-0.5 block">
            {{ authStore.user?.role === 'employee' ? 'Employee Portal' : 'Workforce Intelligence' }}
          </span>
        </div>
      </router-link>

      <button
        @click="toggleSidebar"
        type="button"
        class="p-1.5 rounded-xl text-neu-muted hover:text-neu-text hover:bg-neu-surface transition-colors focus:outline-none"
        :title="collapsed ? 'Expand' : 'Collapse'"
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

    <!-- Navigation List -->
    <nav class="flex-1 overflow-y-auto px-3.5 py-5 space-y-5 scrollbar-thin">
      <div v-for="section in navSections" :key="section.title">
        <div
          v-if="!collapsed"
          class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-neu-muted/75"
        >
          {{ section.title }}
        </div>
        <div class="space-y-1">
          <router-link
            v-for="item in section.items"
            :key="item.path"
            :to="item.path"
            :class="[
              'flex items-center px-3 py-2 rounded-2xl text-xs font-medium transition-all duration-150 group focus:outline-none',
              isActive(item.path)
                ? 'bg-neu-primary/10 text-neu-primary font-semibold shadow-neu-inset'
                : 'text-neu-muted hover:text-neu-text hover:bg-neu-surface hover:shadow-neu-flat-sm',
            ]"
            :title="collapsed ? item.name : undefined"
          >
            <svg
              class="w-4 h-4 flex-shrink-0 transition-transform duration-150"
              :class="isActive(item.path) ? 'text-neu-primary' : 'text-neu-muted group-hover:text-neu-text'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
            </svg>
            <span v-if="!collapsed" class="ml-2.5 flex-1 truncate">{{ item.name }}</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Footer: AI System Health & Logout -->
    <div class="p-3.5 border-t border-neu-border/40 space-y-2">
      <!-- Subtle AI System Status Indicator -->
      <div
        v-if="!collapsed"
        class="px-3 py-2 rounded-xl bg-neu-surface/70 text-xs flex items-center justify-between"
      >
        <div class="flex items-center space-x-2">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="font-medium text-neu-text text-[11px]">AI Systems</span>
        </div>
        <span class="text-[11px] font-semibold text-emerald-600">
          Healthy
        </span>
      </div>

      <!-- Logout Button -->
      <button
        @click="handleLogout"
        type="button"
        :class="[
          'flex items-center w-full px-3 py-2 rounded-xl text-xs font-medium text-neu-muted hover:text-rose-600 hover:bg-rose-50/50 transition-colors focus:outline-none',
          collapsed ? 'justify-center' : '',
        ]"
      >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.8"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
          />
        </svg>
        <span v-if="!collapsed" class="ml-2.5">Sign Out</span>
      </button>
    </div>
  </aside>
</template>
