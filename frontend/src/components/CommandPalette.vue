<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isOpen = ref(false)
const searchQuery = ref('')

const commands = [
  { id: 'nav-overview', title: 'Executive Overview', section: 'Navigation', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1', path: '/' },
  { id: 'nav-emp', title: 'Employee Directory (1,000+)', section: 'Navigation', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', path: '/employees' },
  { id: 'nav-wf', title: 'Workforce Intelligence & Heatmaps', section: 'Navigation', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', path: '/workforce' },
  { id: 'nav-att', title: 'Attendance & Anomaly Queue', section: 'Navigation', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', path: '/attendance' },
  { id: 'nav-pay', title: 'Payroll Intelligence & Alerts', section: 'Navigation', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', path: '/payrolls' },
  { id: 'nav-perf', title: 'Performance Reviews & Ratings', section: 'Navigation', icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', path: '/performances' },
  { id: 'nav-chat', title: 'AI Assistant & Policy RAG (Groq)', section: 'AI & Analytics', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', path: '/chatbot' },
  { id: 'nav-rep', title: 'Executive HR Reports', section: 'AI & Analytics', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', path: '/reports' },
  { id: 'nav-mlops', title: 'Models & MLOps Control Center', section: 'MLOps', icon: 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z', path: '/mlops' },
  { id: 'nav-set', title: 'Organization Settings & RBAC', section: 'Settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', path: '/settings' },
]

const filteredCommands = computed(() => {
  if (!searchQuery.value.trim()) return commands
  const q = searchQuery.value.toLowerCase()
  return commands.filter((c) => c.title.toLowerCase().includes(q) || c.section.toLowerCase().includes(q))
})

function handleKeyDown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    isOpen.value = !isOpen.value
  } else if (e.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

function selectCommand(path: string) {
  isOpen.value = false
  searchQuery.value = ''
  router.push(path)
}

onMounted(() => window.addEventListener('keydown', handleKeyDown))
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown))

defineExpose({
  open: () => (isOpen.value = true),
  close: () => (isOpen.value = false),
})
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4 bg-black/30 backdrop-blur-xs animate-in fade-in duration-150"
    @click.self="isOpen = false"
  >
    <div class="w-full max-w-xl neu-card-elevated p-4 overflow-hidden border border-white/60">
      <!-- Search Input -->
      <div class="relative flex items-center mb-4">
        <svg class="w-5 h-5 absolute left-3.5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search workforce intelligence, employees, models, policies... (ESC to close)"
          class="neu-input w-full pl-11 pr-4 py-3 text-sm focus:outline-none"
          autofocus
        />
      </div>

      <!-- Results list -->
      <div class="max-h-80 overflow-y-auto space-y-1 pr-1">
        <div
          v-for="item in filteredCommands"
          :key="item.id"
          @click="selectCommand(item.path)"
          class="flex items-center justify-between p-3 rounded-neu-sm hover:bg-surface cursor-pointer group transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-neu-sm neu-card-sm flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
            </div>
            <div>
              <div class="text-sm font-medium text-text group-hover:text-primary transition-colors">
                {{ item.title }}
              </div>
              <div class="text-xs text-text-muted">
                {{ item.section }}
              </div>
            </div>
          </div>
          <span class="text-xs text-text-muted font-mono bg-background px-2 py-1 rounded">↵</span>
        </div>

        <div v-if="filteredCommands.length === 0" class="text-center py-8 text-text-secondary text-sm">
          No matches found for "{{ searchQuery }}"
        </div>
      </div>
    </div>
  </div>
</template>
