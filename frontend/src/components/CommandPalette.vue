<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps<{
  isOpen?: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const router = useRouter()
const localOpen = ref(false)
const searchQuery = ref('')

const openState = computed({
  get: () => props.isOpen ?? localOpen.value,
  set: (val: boolean) => {
    localOpen.value = val
    if (!val) emit('close')
  },
})

const commands = [
  { id: 'nav-dashboard', title: 'Dashboard', section: 'Intelligence', path: '/' },
  { id: 'nav-wf', title: 'Workforce Risk', section: 'Intelligence', path: '/workforce' },
  { id: 'nav-emp', title: 'Employees', section: 'Intelligence', path: '/employees' },
  { id: 'nav-att', title: 'Attendance', section: 'Operations', path: '/attendance' },
  { id: 'nav-leave', title: 'Leaves', section: 'Operations', path: '/leaves' },
  { id: 'nav-pay', title: 'Payroll', section: 'Operations', path: '/payrolls' },
  { id: 'nav-perf', title: 'Performance', section: 'Operations', path: '/performances' },
  { id: 'nav-chat', title: 'AI Assistant', section: 'AI', path: '/chatbot' },
  { id: 'nav-rep', title: 'Reports', section: 'AI', path: '/reports' },
  { id: 'nav-mlops', title: 'Models', section: 'Admin', path: '/mlops' },
  { id: 'nav-set', title: 'Settings', section: 'Admin', path: '/settings' },
]

const filteredCommands = computed(() => {
  if (!searchQuery.value.trim()) return commands
  const q = searchQuery.value.toLowerCase()
  return commands.filter(
    (c) => c.title.toLowerCase().includes(q) || c.section.toLowerCase().includes(q)
  )
})

function handleKeyDown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    openState.value = !openState.value
  } else if (e.key === 'Escape' && openState.value) {
    openState.value = false
  }
}

function selectCommand(path: string) {
  openState.value = false
  searchQuery.value = ''
  router.push(path)
}

onMounted(() => window.addEventListener('keydown', handleKeyDown))
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown))
</script>

<template>
  <div
    v-if="openState"
    class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4 bg-black/30 backdrop-blur-xs"
    @click.self="openState = false"
  >
    <div class="w-full max-w-lg neu-card p-4 overflow-hidden shadow-neu-flat-lg border border-neu-border/40">
      <!-- Search Input -->
      <div class="relative flex items-center mb-3">
        <svg class="w-4 h-4 absolute left-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search employees, reports, pages... (ESC to close)"
          class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none placeholder-neu-muted"
          autofocus
        />
      </div>

      <!-- Command List -->
      <div class="max-h-72 overflow-y-auto space-y-1 divide-y divide-neu-border/20">
        <div
          v-for="cmd in filteredCommands"
          :key="cmd.id"
          @click="selectCommand(cmd.path)"
          class="flex items-center justify-between px-3.5 py-2.5 rounded-xl cursor-pointer hover:bg-neu-base/60 transition-colors text-xs"
        >
          <span class="font-semibold text-neu-text">{{ cmd.title }}</span>
          <span class="text-[10px] uppercase font-bold text-neu-muted tracking-wider">{{ cmd.section }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
