<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getPortalAttendance, portalCheckIn, portalCheckOut } from '@/api/portal'
import type { Attendance, PaginatedResponse } from '@/types'
import { formatDate, formatTime, formatDuration, formatStatus } from '@/utils/formatters'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const loading = ref(true)
const clockLoading = ref(false)
const clockMessage = ref('')
const clockError = ref(false)
const page = ref(1)
const attendanceData = ref<PaginatedResponse<Attendance> | null>(null)
const selectedRecord = ref<Attendance | null>(null)

// Current time ticker for punch clock
const currentTime = ref(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }))
setInterval(() => {
  currentTime.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}, 1000)

const todayRecord = computed(() => {
  const today = new Date().toISOString().slice(0, 10)
  return attendanceData.value?.items?.find(x => x.date && x.date.startsWith(today))
})

function setClockFeedback(msg: string, isError = false) {
  clockMessage.value = msg
  clockError.value = isError
  setTimeout(() => {
    clockMessage.value = ''
    clockError.value = false
  }, 4500)
}

function sanitizeError(err: any, fallback: string): string {
  const raw = err.response?.data?.message || err.message || fallback
  if (typeof raw === 'string' && (raw.includes('SQLSTATE') || raw.includes('constraint') || raw.includes('UNIQUE'))) {
    return 'You have already recorded attendance for today.'
  }
  return raw
}

async function fetchRecords() {
  loading.value = true
  try {
    attendanceData.value = await getPortalAttendance({ page: page.value, per_page: 15 })
  } catch (err: any) {
    console.error('Failed to load attendance:', err)
  } finally {
    loading.value = false
  }
}

async function handleCheckIn() {
  clockLoading.value = true
  try {
    await portalCheckIn()
    setClockFeedback('Clocked in successfully!', false)
    await fetchRecords()
  } catch (err: any) {
    const msg = sanitizeError(err, 'Check-in failed')
    setClockFeedback(msg, true)
    await fetchRecords()
  } finally {
    clockLoading.value = false
  }
}

async function handleCheckOut() {
  clockLoading.value = true
  try {
    await portalCheckOut()
    setClockFeedback('Clocked out successfully!', false)
    await fetchRecords()
  } catch (err: any) {
    const msg = sanitizeError(err, 'Check-out failed')
    setClockFeedback(msg, true)
    await fetchRecords()
  } finally {
    clockLoading.value = false
  }
}

function openModal(item: Attendance) {
  selectedRecord.value = item
}

function closeModal() {
  selectedRecord.value = null
}

function changePage(newPage: number) {
  if (newPage < 1 || (attendanceData.value && newPage > attendanceData.value.total_pages)) return
  page.value = newPage
  fetchRecords()
}

onMounted(() => {
  fetchRecords()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <PageHeader
      title="My Attendance"
      subtitle="Track your daily work hours, punctuality, and clock-in logs."
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <button
            @click="fetchRecords"
            type="button"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold cursor-pointer"
          >
            Refresh
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Punch Clock Card -->
    <div class="neu-card p-6 shadow-neu-flat border border-white/60">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
          <div class="w-12 h-12 rounded-2xl bg-neu-primary/10 text-neu-primary flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <div class="text-xs font-bold uppercase tracking-wider text-neu-muted">Live Time Clock</div>
            <div class="text-2xl font-black text-neu-text font-mono tracking-tight">{{ currentTime }}</div>
          </div>
        </div>

        <div
          v-if="clockMessage"
          :class="[
            'text-xs font-bold px-3 py-1.5 rounded-xl transition-all',
            clockError ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'
          ]"
        >
          {{ clockMessage }}
        </div>

        <div class="flex items-center space-x-3">
          <button
            @click="handleCheckIn"
            type="button"
            :disabled="clockLoading || !!todayRecord?.check_in"
            class="btn-primary px-5 py-2.5 text-xs font-bold cursor-pointer disabled:opacity-40"
          >
            Clock In
          </button>
          <button
            @click="handleCheckOut"
            type="button"
            :disabled="clockLoading || !todayRecord?.check_in || !!todayRecord?.check_out"
            class="btn-secondary px-5 py-2.5 text-xs font-bold cursor-pointer disabled:opacity-40"
          >
            Clock Out
          </button>
        </div>
      </div>
    </div>

    <!-- Attendance Table -->
    <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
        <h3 class="text-sm font-bold text-neu-text">Attendance History</h3>
        <span class="text-xs text-neu-muted font-medium">
          Showing {{ attendanceData?.items?.length || 0 }} of {{ attendanceData?.total || 0 }} days
        </span>
      </div>

      <LoadingSkeleton v-if="loading" :rows="5" type="table" />

      <template v-else-if="attendanceData && attendanceData.items.length > 0">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-neu-border/40 text-[11px] font-bold uppercase tracking-wider text-neu-muted">
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Check In</th>
                <th class="py-3 px-4">Check Out</th>
                <th class="py-3 px-4">Hours Worked</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/20 text-xs">
              <tr
                v-for="att in attendanceData.items"
                :key="att.id"
                class="hover:bg-white/40 transition-colors cursor-pointer"
                @click="openModal(att)"
              >
                <td class="py-3 px-4 font-semibold text-neu-text">{{ formatDate(att.date) }}</td>
                <td class="py-3 px-4 text-neu-muted">{{ formatTime(att.check_in) }}</td>
                <td class="py-3 px-4 text-neu-muted">{{ formatTime(att.check_out) }}</td>
                <td class="py-3 px-4 font-medium text-neu-text">{{ formatDuration(att.hours_worked) }}</td>
                <td class="py-3 px-4">
                  <StatusBadge :status="att.status" size="sm" />
                </td>
                <td class="py-3 px-4 text-right">
                  <button
                    type="button"
                    class="text-xs font-bold text-neu-primary hover:underline"
                    @click.stop="openModal(att)"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between pt-4 border-t border-neu-border/30 text-xs text-neu-muted">
          <span>Page {{ attendanceData.page }} of {{ attendanceData.total_pages }}</span>
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(attendanceData.page - 1)"
              :disabled="attendanceData.page <= 1"
              type="button"
              class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 cursor-pointer"
            >
              Previous
            </button>
            <button
              @click="changePage(attendanceData.page + 1)"
              :disabled="attendanceData.page >= attendanceData.total_pages"
              type="button"
              class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 cursor-pointer"
            >
              Next
            </button>
          </div>
        </div>
      </template>

      <EmptyState
        v-else
        title="No attendance records found"
        description="Your logged attendance entries will appear here."
      />
    </div>

    <!-- Attendance Detail Modal -->
    <div
      v-if="selectedRecord"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <div class="neu-card p-6 shadow-neu-flat-lg border border-white/80 max-w-md w-full space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <h3 class="text-sm font-bold text-neu-text">Attendance Record Details</h3>
          <button
            @click="closeModal"
            type="button"
            class="text-neu-muted hover:text-neu-text p-1 rounded-lg"
          >
            &times;
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset space-y-2">
            <div class="flex justify-between">
              <span class="text-neu-muted">Date:</span>
              <span class="font-bold text-neu-text">{{ formatDate(selectedRecord.date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Check In:</span>
              <span class="font-bold text-neu-text">{{ formatTime(selectedRecord.check_in) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Check Out:</span>
              <span class="font-bold text-neu-text">{{ formatTime(selectedRecord.check_out) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Total Hours:</span>
              <span class="font-bold text-neu-primary">{{ formatDuration(selectedRecord.hours_worked) }}</span>
            </div>
            <div class="flex justify-between items-center pt-1 border-t border-neu-border/30">
              <span class="text-neu-muted">Status:</span>
              <StatusBadge :status="selectedRecord.status" size="sm" />
            </div>
          </div>
        </div>

        <div class="pt-2">
          <button
            @click="closeModal"
            type="button"
            class="w-full btn-primary py-2.5 text-xs font-bold cursor-pointer text-center"
          >
            Done
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
