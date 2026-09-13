<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLeavesStore } from '@/stores/leaves'
import { createLeave, approveLeave, rejectLeave } from '@/api/leaves'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDate } from '@/utils/formatters'

const store = useLeavesStore()

const showModal = ref(false)
const submitting = ref(false)

function getTodayString(offsetDays = 0) {
  const d = new Date()
  if (offsetDays) d.setDate(d.getDate() + offsetDays)
  return d.toISOString().split('T')[0]
}

const leaveForm = ref({
  leave_type: 'annual' as string,
  start_date: getTodayString(1),
  end_date: getTodayString(3),
  reason: '',
})

function openRequestModal() {
  leaveForm.value = {
    leave_type: 'annual',
    start_date: getTodayString(1),
    end_date: getTodayString(3),
    reason: '',
  }
  showModal.value = true
}

const leaveTypes = [
  { value: 'annual', label: 'Annual Leave' },
  { value: 'sick', label: 'Sick Leave' },
  { value: 'personal', label: 'Personal Leave' },
  { value: 'maternity', label: 'Maternity Leave' },
  { value: 'paternity', label: 'Paternity Leave' },
  { value: 'unpaid', label: 'Unpaid Leave' },
]

async function handleSubmit() {
  submitting.value = true
  try {
    await createLeave(leaveForm.value)
    showModal.value = false
    leaveForm.value = {
      leave_type: 'annual',
      start_date: getTodayString(1),
      end_date: getTodayString(3),
      reason: '',
    }
    await store.fetchLeaves()
  } finally {
    submitting.value = false
  }
}

async function handleApprove(id: number) {
  await approveLeave(id)
  await store.fetchLeaves()
}

async function handleReject(id: number) {
  await rejectLeave(id)
  await store.fetchLeaves()
}

onMounted(() => {
  store.fetchLeaves()
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Leaves"
      subtitle="Manage employee absence and leave requests."
    >
      <template #action>
        <button
          @click="openRequestModal"
          type="button"
          class="btn-primary flex items-center space-x-2 px-4 py-2 text-xs font-semibold focus:outline-none"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Request Leave</span>
        </button>
      </template>
    </PageHeader>

    <!-- 4 Clean KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <NeumorphicStatCard
        title="Annual Balance"
        value="14 days"
        caption="Company average"
      />
      <NeumorphicStatCard
        title="Pending Approvals"
        :value="store.leaves.filter(l => l.status === 'pending').length || 2"
        change="Requires action"
        changeType="negative"
      />
      <NeumorphicStatCard
        title="On Leave Today"
        value="6"
        caption="Scheduled away"
      />
      <NeumorphicStatCard
        title="Sick Leave Used"
        value="2.1 days"
        caption="YTD average"
      />
    </div>

    <!-- Leaves Requests Table -->
    <NeumorphicCard class="p-0 overflow-hidden">
      <div class="p-4 border-b border-neu-border/40 bg-neu-surface/40 flex items-center justify-between">
        <h2 class="text-sm font-bold text-neu-text tracking-tight">Leave Requests</h2>
        <span class="text-xs text-neu-muted">{{ store.leaves.length }} records</span>
      </div>

      <LoadingSkeleton v-if="store.loading" type="table" :rows="5" />

      <div v-else-if="store.leaves.length === 0" class="p-8 text-center text-xs text-neu-muted">
        No leave requests found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
              <th class="py-3 px-5">Employee</th>
              <th class="py-3 px-4">Leave Type</th>
              <th class="py-3 px-4">Dates</th>
              <th class="py-3 px-4 text-center">Days</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/30">
            <tr
              v-for="leave in store.leaves"
              :key="leave.id"
              class="hover:bg-neu-base/40 transition-colors"
            >
              <td class="py-3 px-5 font-semibold text-neu-text">
                {{ leave.employee_name || `Employee #${leave.employee_id}` }}
              </td>
              <td class="py-3 px-4 text-neu-text capitalize">
                {{ leave.leave_type }}
              </td>
              <td class="py-3 px-4 text-neu-muted">
                {{ formatDate(leave.start_date) }} to {{ formatDate(leave.end_date) }}
              </td>
              <td class="py-3 px-4 text-center text-neu-text font-semibold">
                {{ leave.days || 1 }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="leave.status" size="sm" />
              </td>
              <td class="py-3 px-5 text-right space-x-2">
                <template v-if="leave.status === 'pending'">
                  <button
                    @click="handleApprove(leave.id)"
                    type="button"
                    class="text-xs font-semibold text-emerald-600 hover:underline focus:outline-none"
                  >
                    Approve
                  </button>
                  <button
                    @click="handleReject(leave.id)"
                    type="button"
                    class="text-xs font-semibold text-rose-600 hover:underline focus:outline-none"
                  >
                    Reject
                  </button>
                </template>
                <span v-else class="text-xs text-neu-muted">&ndash;</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>

    <!-- Request Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
      @click.self="showModal = false"
    >
      <NeumorphicCard class="max-w-md w-full p-6 space-y-4 shadow-neu-flat-lg">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <h3 class="text-sm font-bold text-neu-text">Submit Leave Request</h3>
          <button @click="showModal = false" class="text-neu-muted hover:text-neu-text text-sm font-bold">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-neu-text mb-1">Leave Type</label>
            <select
              v-model="leaveForm.leave_type"
              class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none"
            >
              <option v-for="t in leaveTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-neu-text mb-1">Start Date</label>
              <input
                v-model="leaveForm.start_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs text-neu-text focus:outline-none"
              />
            </div>
            <div>
              <label class="block font-semibold text-neu-text mb-1">End Date</label>
              <input
                v-model="leaveForm.end_date"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs text-neu-text focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block font-semibold text-neu-text mb-1">Reason (Optional)</label>
            <textarea
              v-model="leaveForm.reason"
              rows="2"
              class="w-full p-3 rounded-xl bg-neu-base shadow-neu-inset text-xs text-neu-text focus:outline-none"
              placeholder="Brief explanation..."
            ></textarea>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-neu-border/40">
            <button
              @click="showModal = false"
              type="button"
              class="btn-secondary px-4 py-2 text-xs font-semibold focus:outline-none"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary px-5 py-2 text-xs font-semibold focus:outline-none disabled:opacity-50"
            >
              {{ submitting ? 'Submitting...' : 'Submit Request' }}
            </button>
          </div>
        </form>
      </NeumorphicCard>
    </div>
  </div>
</template>
