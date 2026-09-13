<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getPortalLeaves, submitPortalLeave } from '@/api/portal'
import type { Leave, LeaveBalances, PaginatedResponse } from '@/types'
import { formatDate } from '@/utils/formatters'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const loading = ref(true)
const modalOpen = ref(false)
const submitLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const balances = ref<LeaveBalances | null>(null)
const leavesData = ref<PaginatedResponse<Leave> | null>(null)

const leavesList = computed<Leave[]>(() => {
  if (!leavesData.value) return []
  const anyLeaves = leavesData.value as any
  if (Array.isArray(anyLeaves.items)) return anyLeaves.items
  if (Array.isArray(anyLeaves.data)) return anyLeaves.data
  if (Array.isArray(anyLeaves)) return anyLeaves
  return []
})

// Form state with default valid upcoming dates
const form = ref({
  type: 'annual',
  start_date: '2026-09-20',
  end_date: '2026-09-24',
  reason: '',
})

async function fetchLeaves() {
  loading.value = true
  try {
    const res = await getPortalLeaves()
    balances.value = res.balances
    leavesData.value = res.leaves
  } catch (err: any) {
    console.error('Failed to load leaves:', err)
  } finally {
    loading.value = false
  }
}

function openModal() {
  errorMessage.value = ''
  successMessage.value = ''
  form.value = {
    type: 'annual',
    start_date: '2026-09-20',
    end_date: '2026-09-24',
    reason: '',
  }
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
}

async function handleSubmit() {
  if (!form.value.reason.trim()) {
    errorMessage.value = 'Please provide a reason for the leave request.'
    return
  }
  submitLoading.value = true
  errorMessage.value = ''
  try {
    await submitPortalLeave(form.value)
    successMessage.value = 'Leave request submitted successfully!'
    await fetchLeaves()
    setTimeout(() => {
      closeModal()
      successMessage.value = ''
    }, 1500)
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.message || 'Failed to submit leave request.'
  } finally {
    submitLoading.value = false
  }
}

onMounted(() => {
  fetchLeaves()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <PageHeader
      title="My Leaves"
      subtitle="View your leave balances, track approval status, and submit time off requests."
    >
      <template #action>
        <button
          @click="openModal"
          type="button"
          class="btn-primary px-4 py-2 text-xs font-semibold cursor-pointer"
        >
          + Request Leave
        </button>
      </template>
    </PageHeader>

    <!-- Leave Balances -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Annual Leave -->
      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <div class="flex items-center justify-between pb-2 border-b border-neu-border/40">
          <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Annual Vacation</span>
          <span class="text-xs font-bold text-neu-primary">Paid</span>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
          <div>
            <div class="text-3xl font-black text-neu-primary tracking-tight">
              {{ balances?.annual.remaining ?? 15 }}
            </div>
            <div class="text-xs text-neu-muted mt-0.5">Days Available</div>
          </div>
          <div class="text-right text-xs text-neu-muted space-y-0.5">
            <div>Used: <strong class="text-neu-text">{{ balances?.annual.used ?? 5 }}</strong></div>
            <div>Total: <strong class="text-neu-text">{{ balances?.annual.total ?? 20 }}</strong></div>
          </div>
        </div>
      </div>

      <!-- Sick Leave -->
      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <div class="flex items-center justify-between pb-2 border-b border-neu-border/40">
          <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Sick Leave</span>
          <span class="text-xs font-bold text-emerald-600">Medical</span>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
          <div>
            <div class="text-3xl font-black text-emerald-600 tracking-tight">
              {{ balances?.sick.remaining ?? 8 }}
            </div>
            <div class="text-xs text-neu-muted mt-0.5">Days Available</div>
          </div>
          <div class="text-right text-xs text-neu-muted space-y-0.5">
            <div>Used: <strong class="text-neu-text">{{ balances?.sick.used ?? 2 }}</strong></div>
            <div>Total: <strong class="text-neu-text">{{ balances?.sick.total ?? 10 }}</strong></div>
          </div>
        </div>
      </div>

      <!-- Personal Leave -->
      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <div class="flex items-center justify-between pb-2 border-b border-neu-border/40">
          <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Personal & Family</span>
          <span class="text-xs font-bold text-amber-600">Discretionary</span>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
          <div>
            <div class="text-3xl font-black text-amber-600 tracking-tight">
              {{ balances?.personal.remaining ?? 5 }}
            </div>
            <div class="text-xs text-neu-muted mt-0.5">Days Available</div>
          </div>
          <div class="text-right text-xs text-neu-muted space-y-0.5">
            <div>Used: <strong class="text-neu-text">{{ balances?.personal.used ?? 0 }}</strong></div>
            <div>Total: <strong class="text-neu-text">{{ balances?.personal.total ?? 5 }}</strong></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leaves History Table -->
    <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
        <h3 class="text-sm font-bold text-neu-text">Leave Requests & History</h3>
        <span class="text-xs text-neu-muted font-medium">
          {{ leavesList.length }} total requests
        </span>
      </div>

      <LoadingSkeleton v-if="loading" :rows="4" type="table" />

      <template v-else-if="leavesList.length > 0">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-neu-border/40 text-[11px] font-bold uppercase tracking-wider text-neu-muted">
                <th class="py-3 px-4">Leave Type</th>
                <th class="py-3 px-4">Dates</th>
                <th class="py-3 px-4">Duration</th>
                <th class="py-3 px-4">Reason</th>
                <th class="py-3 px-4 text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/20 text-xs">
              <tr
                v-for="leave in leavesList"
                :key="leave.id"
                class="hover:bg-white/40 transition-colors"
              >
                <td class="py-3.5 px-4 font-bold text-neu-text capitalize">
                  {{ leave.leave_type || leave.type }} Leave
                </td>
                <td class="py-3.5 px-4 text-neu-muted">
                  {{ formatDate(leave.start_date) }} &rarr; {{ formatDate(leave.end_date) }}
                </td>
                <td class="py-3.5 px-4 font-semibold text-neu-text">
                  {{ leave.days }} {{ leave.days === 1 ? 'day' : 'days' }}
                </td>
                <td class="py-3.5 px-4 text-neu-muted max-w-xs truncate" :title="leave.reason">
                  {{ leave.reason }}
                </td>
                <td class="py-3.5 px-4 text-right">
                  <StatusBadge :status="leave.status" size="sm" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <EmptyState
        v-else
        title="No leave requests yet"
        description="Click + Request Leave above to submit your first time-off request."
      />
    </div>

    <!-- Request Leave Modal -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <div class="neu-card p-6 shadow-neu-flat-lg border border-white/80 max-w-lg w-full space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <div>
            <h3 class="text-sm font-bold text-neu-text">Submit Leave Request</h3>
            <p class="text-xs text-neu-muted">Your manager will review and approve your request.</p>
          </div>
          <button
            @click="closeModal"
            type="button"
            class="text-neu-muted hover:text-neu-text p-1 rounded-lg text-lg"
          >
            &times;
          </button>
        </div>

        <div v-if="errorMessage" class="p-3 rounded-2xl bg-rose-50 text-rose-700 text-xs font-semibold shadow-neu-inset border border-rose-200">
          {{ errorMessage }}
        </div>
        <div v-if="successMessage" class="p-3 rounded-2xl bg-emerald-50 text-emerald-700 text-xs font-semibold shadow-neu-inset border border-emerald-200">
          {{ successMessage }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4 text-xs">
          <!-- Type -->
          <div class="space-y-1">
            <label class="block font-bold uppercase tracking-wider text-neu-muted">Leave Type</label>
            <select
              v-model="form.type"
              class="w-full px-3.5 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset font-semibold text-neu-text border border-white/40 focus:outline-none"
            >
              <option value="annual">Annual Leave ({{ balances?.annual.remaining ?? 15 }} days available)</option>
              <option value="sick">Sick Leave ({{ balances?.sick.remaining ?? 8 }} days available)</option>
              <option value="personal">Personal Leave ({{ balances?.personal.remaining ?? 5 }} days available)</option>
              <option value="maternity">Maternity / Paternity Leave</option>
            </select>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block font-bold uppercase tracking-wider text-neu-muted">Start Date</label>
              <input
                v-model="form.start_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset font-semibold text-neu-text border border-white/40 focus:outline-none"
              />
            </div>
            <div class="space-y-1">
              <label class="block font-bold uppercase tracking-wider text-neu-muted">End Date</label>
              <input
                v-model="form.end_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset font-semibold text-neu-text border border-white/40 focus:outline-none"
              />
            </div>
          </div>

          <!-- Reason -->
          <div class="space-y-1">
            <label class="block font-bold uppercase tracking-wider text-neu-muted">Reason for Leave</label>
            <textarea
              v-model="form.reason"
              rows="3"
              required
              placeholder="Please provide details (e.g. planned vacation, family event, recovery)..."
              class="w-full px-3.5 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset font-medium text-neu-text border border-white/40 focus:outline-none placeholder-neu-muted"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-neu-border/30">
            <button
              @click="closeModal"
              type="button"
              class="btn-secondary px-4 py-2 font-bold cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitLoading"
              class="btn-primary px-5 py-2 font-bold disabled:opacity-40 cursor-pointer"
            >
              {{ submitLoading ? 'Submitting...' : 'Submit Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
