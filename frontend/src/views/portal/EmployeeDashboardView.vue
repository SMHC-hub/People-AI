<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  getPortalDashboard,
  portalCheckIn,
  portalCheckOut,
} from '@/api/portal'
import type { PortalDashboardData } from '@/types'
import { formatDate, formatTime, formatDuration, formatStatus } from '@/utils/formatters'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const clockLoading = ref(false)
const clockMessage = ref('')
const clockError = ref(false)
const data = ref<PortalDashboardData | null>(null)

// Current time ticker for punch clock
const currentTime = ref(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }))
setInterval(() => {
  currentTime.value = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}, 1000)

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

async function loadData() {
  loading.value = true
  try {
    data.value = await getPortalDashboard()
  } catch (err: any) {
    console.error('Failed to load portal dashboard:', err)
  } finally {
    loading.value = false
  }
}

async function handleCheckIn() {
  clockLoading.value = true
  try {
    const res = await portalCheckIn()
    if (data.value) {
      data.value.today_attendance = res
    }
    setClockFeedback('Clocked in successfully!', false)
    await loadData()
  } catch (err: any) {
    const msg = sanitizeError(err, 'Check-in could not be completed.')
    setClockFeedback(msg, true)
    await loadData()
  } finally {
    clockLoading.value = false
  }
}

async function handleCheckOut() {
  clockLoading.value = true
  try {
    const res = await portalCheckOut()
    if (data.value) {
      data.value.today_attendance = res
    }
    setClockFeedback('Clocked out successfully!', false)
    await loadData()
  } catch (err: any) {
    const msg = sanitizeError(err, 'Check-out could not be completed.')
    setClockFeedback(msg, true)
    await loadData()
  } finally {
    clockLoading.value = false
  }
}

function navigateTo(path: string) {
  router.push(path)
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <PageHeader
      :title="`Welcome back, ${data?.employee?.first_name || authStore.user?.full_name?.split(' ')[0] || 'Team Member'}!`"
      subtitle="Here is your personal workforce overview, attendance, and leave status."
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <button
            @click="navigateTo('/portal/leaves')"
            type="button"
            class="btn-primary px-3.5 py-2 text-xs font-semibold cursor-pointer"
          >
            + Request Leave
          </button>
          <button
            @click="navigateTo('/chatbot')"
            type="button"
            class="btn-accent px-3.5 py-2 text-xs font-semibold cursor-pointer"
          >
            Policy Assistant
          </button>
        </div>
      </template>
    </PageHeader>

    <LoadingSkeleton v-if="loading" :count="4" type="card" />

    <template v-else-if="data">
      <!-- Top Row: Interactive Time Clock & Leave Balances -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Interactive Punch Clock Card -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
              <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Time & Attendance</span>
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            </div>

            <!-- Live Clock Display -->
            <div class="my-5 text-center">
              <div class="text-3xl font-black tracking-tight text-neu-text font-mono">
                {{ currentTime }}
              </div>
              <div class="text-xs text-neu-muted mt-1 font-medium">
                {{ new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' }) }}
              </div>
            </div>

            <!-- Today's Status Banner -->
            <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset text-center space-y-1 mb-4">
              <div class="text-[11px] font-bold uppercase tracking-wider text-neu-muted">Today's Status</div>
              <div v-if="data.today_attendance?.check_in" class="text-xs font-bold text-neu-text">
                Checked in at <span class="text-neu-primary">{{ formatTime(data.today_attendance.check_in) }}</span>
                <span v-if="data.today_attendance.check_out">
                  &bull; Checked out at <span class="text-neu-primary">{{ formatTime(data.today_attendance.check_out) }}</span>
                  ({{ formatDuration(data.today_attendance.hours_worked) }})
                </span>
              </div>
              <div v-else class="text-xs font-semibold text-neu-muted">
                Not clocked in yet today
              </div>
            </div>

            <!-- Message notification -->
            <div
              v-if="clockMessage"
              :class="[
                'text-xs font-bold text-center py-2 px-3 rounded-xl transition-all duration-200 mb-2',
                clockError ? 'bg-rose-50 text-rose-600 border border-rose-200 shadow-neu-inset' : 'bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-neu-inset'
              ]"
            >
              {{ clockMessage }}
            </div>
          </div>

          <!-- Actions -->
          <div class="grid grid-cols-2 gap-3 pt-2">
            <button
              @click="handleCheckIn"
              type="button"
              :disabled="clockLoading || !!data.today_attendance?.check_in"
              class="btn-primary py-2.5 text-xs font-bold disabled:opacity-40 cursor-pointer"
            >
              Clock In
            </button>
            <button
              @click="handleCheckOut"
              type="button"
              :disabled="clockLoading || !data.today_attendance?.check_in || !!data.today_attendance?.check_out"
              class="btn-secondary py-2.5 text-xs font-bold disabled:opacity-40 cursor-pointer"
            >
              Clock Out
            </button>
          </div>
        </div>

        <!-- Leave Balances Card -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
              <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Leave Entitlements</span>
              <button
                @click="navigateTo('/portal/leaves')"
                type="button"
                class="text-xs font-bold text-neu-primary hover:underline cursor-pointer"
              >
                View History &rarr;
              </button>
            </div>

            <div class="grid grid-cols-3 gap-3 my-4 text-center">
              <!-- Annual -->
              <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset">
                <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Annual</div>
                <div class="text-2xl font-black text-neu-primary mt-1">
                  {{ data.leave_balances.annual.remaining }}
                </div>
                <div class="text-[10px] text-neu-muted">of {{ data.leave_balances.annual.total }} days</div>
              </div>

              <!-- Sick -->
              <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset">
                <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Sick</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">
                  {{ data.leave_balances.sick.remaining }}
                </div>
                <div class="text-[10px] text-neu-muted">of {{ data.leave_balances.sick.total }} days</div>
              </div>

              <!-- Personal -->
              <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset">
                <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Personal</div>
                <div class="text-2xl font-black text-amber-600 mt-1">
                  {{ data.leave_balances.personal.remaining }}
                </div>
                <div class="text-[10px] text-neu-muted">of {{ data.leave_balances.personal.total }} days</div>
              </div>
            </div>

            <!-- Pending status reminder -->
            <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-center justify-between text-xs">
              <div class="flex items-center space-x-2 text-blue-800">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="font-semibold">Pending Requests:</span>
              </div>
              <span class="font-bold text-blue-900">{{ data.leave_balances.pending_count }} awaiting approval</span>
            </div>
          </div>

          <div class="pt-4">
            <button
              @click="navigateTo('/portal/leaves')"
              type="button"
              class="w-full btn-secondary py-2.5 text-xs font-bold cursor-pointer text-center"
            >
              Submit New Request
            </button>
          </div>
        </div>

        <!-- Latest Payslip & Compensation Summary -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
              <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Latest Payslip</span>
              <button
                @click="navigateTo('/portal/payrolls')"
                type="button"
                class="text-xs font-bold text-neu-primary hover:underline cursor-pointer"
              >
                All Payslips &rarr;
              </button>
            </div>

            <div v-if="data.latest_payslip" class="my-4 space-y-3">
              <div class="flex items-baseline justify-between">
                <div>
                  <div class="text-[11px] font-bold uppercase tracking-wider text-neu-muted">Net Pay</div>
                  <div class="text-3xl font-black text-neu-text tracking-tight mt-0.5">
                    ${{ Number(data.latest_payslip.net_pay ?? (data.latest_payslip as any).net_salary ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                  </div>
                </div>
                <StatusBadge status="paid" />
              </div>

              <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset space-y-1.5 text-xs">
                <div class="flex justify-between text-neu-muted">
                  <span>Pay Period</span>
                  <span class="font-bold text-neu-text">{{ data.latest_payslip.pay_period }}</span>
                </div>
                <div class="flex justify-between text-neu-muted">
                  <span>Gross Salary</span>
                  <span class="font-bold text-neu-text">${{ Number(data.latest_payslip.gross_pay ?? (data.latest_payslip as any).base_salary ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                </div>
                <div class="flex justify-between text-neu-muted">
                  <span>Deductions & Taxes</span>
                  <span class="font-bold text-rose-600">-${{ Number(data.latest_payslip.deductions ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-xs text-neu-muted font-medium">
              No recent payslip records found.
            </div>
          </div>

          <div class="pt-2">
            <button
              @click="navigateTo('/portal/payrolls')"
              type="button"
              class="w-full btn-secondary py-2.5 text-xs font-bold cursor-pointer text-center"
            >
              View Full Breakdown
            </button>
          </div>
        </div>
      </div>

      <!-- Middle Row: Recent Attendance & Performance Review Snapshot -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Attendance Log -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 lg:col-span-2">
          <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
            <div>
              <h3 class="text-sm font-bold text-neu-text">Recent Attendance History</h3>
              <p class="text-xs text-neu-muted">Your logged work days and check-in times.</p>
            </div>
            <button
              @click="navigateTo('/portal/attendance')"
              type="button"
              class="text-xs font-bold text-neu-primary hover:underline cursor-pointer"
            >
              View All &rarr;
            </button>
          </div>

          <div class="overflow-x-auto mt-4">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-neu-border/40 text-[11px] font-bold uppercase tracking-wider text-neu-muted">
                  <th class="py-2.5 px-3">Date</th>
                  <th class="py-2.5 px-3">Check In</th>
                  <th class="py-2.5 px-3">Check Out</th>
                  <th class="py-2.5 px-3">Hours</th>
                  <th class="py-2.5 px-3 text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neu-border/20 text-xs">
                <tr
                  v-for="att in (data.recent_attendance || []).slice(0, 5)"
                  :key="att.id"
                  class="hover:bg-white/40 transition-colors"
                >
                  <td class="py-3 px-3 font-semibold text-neu-text">{{ formatDate(att.date) }}</td>
                  <td class="py-3 px-3 text-neu-muted">{{ formatTime(att.check_in) }}</td>
                  <td class="py-3 px-3 text-neu-muted">{{ formatTime(att.check_out) }}</td>
                  <td class="py-3 px-3 font-medium text-neu-text">{{ formatDuration(att.hours_worked) }}</td>
                  <td class="py-3 px-3 text-right">
                    <StatusBadge :status="att.status" size="sm" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Latest Performance Review Card -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
              <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Performance Appraisal</span>
              <button
                @click="navigateTo('/portal/performance')"
                type="button"
                class="text-xs font-bold text-neu-primary hover:underline cursor-pointer"
              >
                Details &rarr;
              </button>
            </div>

            <div v-if="data.latest_performance" class="my-4 space-y-4">
              <div class="text-center p-4 rounded-2xl bg-neu-base shadow-neu-inset">
                <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Current Rating</div>
                <div class="text-3xl font-black text-neu-primary mt-1">
                  {{ Number(data.latest_performance.rating).toFixed(1) }} <span class="text-xs text-neu-muted">/ 5.0</span>
                </div>
                <div class="text-xs font-bold text-emerald-600 mt-1">
                  Exceeds Expectations
                </div>
              </div>

              <div class="space-y-2 text-xs">
                <div class="flex justify-between text-neu-muted">
                  <span>Cycle Period</span>
                  <span class="font-bold text-neu-text">{{ data.latest_performance.review_period }}</span>
                </div>
                <div class="flex justify-between text-neu-muted">
                  <span>Review Date</span>
                  <span class="font-bold text-neu-text">{{ formatDate(data.latest_performance.review_date) }}</span>
                </div>
                <div v-if="data.latest_performance.feedback" class="pt-2">
                  <span class="text-[11px] font-bold text-neu-muted uppercase tracking-wider block mb-1">Feedback Excerpt</span>
                  <p class="text-xs text-neu-text italic bg-white/50 p-2.5 rounded-xl border border-white/80">
                    "{{ data.latest_performance.feedback.slice(0, 110) }}..."
                  </p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-xs text-neu-muted font-medium">
              No performance reviews on record yet.
            </div>
          </div>

          <div class="pt-2">
            <button
              @click="navigateTo('/portal/performance')"
              type="button"
              class="w-full btn-secondary py-2.5 text-xs font-bold cursor-pointer text-center"
            >
              View Full Review
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
