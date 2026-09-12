<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLeavesStore } from '@/stores/leaves'
import { createLeave, approveLeave, rejectLeave } from '@/api/leaves'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const store = useLeavesStore()

const showModal = ref(false)
const submitting = ref(false)

const leaveForm = ref({
  leave_type: 'annual' as string,
  start_date: '',
  end_date: '',
  reason: '',
})

const leaveTypes = [
  { value: 'annual', label: 'Annual Leave' },
  { value: 'sick', label: 'Sick Leave' },
  { value: 'personal', label: 'Personal Leave' },
  { value: 'maternity', label: 'Maternity Leave' },
  { value: 'paternity', label: 'Paternity Leave' },
  { value: 'unpaid', label: 'Unpaid Leave' },
]

function getStatusBadge(status: string): 'warning' | 'success' | 'danger' | 'neutral' {
  switch (status) {
    case 'pending':
      return 'warning'
    case 'approved':
      return 'success'
    case 'rejected':
      return 'danger'
    default:
      return 'neutral'
  }
}

async function handleSubmit() {
  submitting.value = true
  try {
    await createLeave(leaveForm.value)
    showModal.value = false
    leaveForm.value = { leave_type: 'annual', start_date: '', end_date: '', reason: '' }
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
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-neu-text">
          Leave & Absence Management
        </h2>
        <p class="text-sm text-neu-muted mt-1">
          Approve PTO requests, track department absenteeism, and calculate leave liability.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="primary" size="sm" @click="showModal = true">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Submit Leave Request
        </NeumorphicButton>
      </div>
    </div>

    <!-- Balance Summary Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-neu-primary">12</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Annual Days</p>
      </div>
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-amber-600">8</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Sick Days</p>
      </div>
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-emerald-600">3</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Personal</p>
      </div>
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-neu-text">23</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Remaining</p>
      </div>
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-rose-600">12</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Total Used</p>
      </div>
      <div class="p-4 rounded-2xl bg-neu-base shadow-neu-flat text-center border border-white/40">
        <p class="text-2xl font-black text-neu-muted">35</p>
        <p class="text-xs font-bold text-neu-muted mt-1 uppercase">Allocated</p>
      </div>
    </div>

    <!-- Requests Table Card -->
    <NeumorphicCard>
      <div class="pb-4 border-b border-neu-border/50 flex items-center justify-between">
        <div>
          <h3 class="text-base font-extrabold text-neu-text">Employee Leave Requests</h3>
          <p class="text-xs text-neu-muted">Pending manager approval queue.</p>
        </div>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Employee</th>
              <th class="py-3.5 px-4">Leave Type</th>
              <th class="py-3.5 px-4">Duration</th>
              <th class="py-3.5 px-4 text-center">Days</th>
              <th class="py-3.5 px-4">Reason</th>
              <th class="py-3.5 px-4 text-center">Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr v-if="store.loading">
              <td colspan="7" class="py-8 text-center text-neu-muted font-bold">Loading requests...</td>
            </tr>
            <tr v-else-if="store.leaves.length === 0">
              <td colspan="7" class="py-8 text-center text-neu-muted">No pending leave requests</td>
            </tr>
            <tr
              v-else
              v-for="leave in store.leaves"
              :key="leave.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <td class="py-3.5 px-4 font-extrabold text-neu-text">
                {{ leave.employee_name }}
              </td>
              <td class="py-3.5 px-4 capitalize font-semibold text-neu-text text-xs">
                {{ leave.leave_type }}
              </td>
              <td class="py-3.5 px-4 text-xs font-mono text-neu-muted">
                {{ leave.start_date }} &rarr; {{ leave.end_date }}
              </td>
              <td class="py-3.5 px-4 text-center font-black text-xs">
                {{ leave.days }}
              </td>
              <td class="py-3.5 px-4 text-xs text-neu-muted max-w-xs truncate">
                {{ leave.reason }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge :variant="getStatusBadge(leave.status)" size="sm">
                  {{ leave.status.toUpperCase() }}
                </NeumorphicBadge>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div v-if="leave.status === 'pending'" class="flex items-center justify-end space-x-2">
                  <NeumorphicButton
                    variant="primary"
                    size="sm"
                    @click="handleApprove(leave.id)"
                  >
                    Approve
                  </NeumorphicButton>
                  <NeumorphicButton
                    variant="danger"
                    size="sm"
                    @click="handleReject(leave.id)"
                  >
                    Reject
                  </NeumorphicButton>
                </div>
                <span v-else class="text-xs text-neu-muted font-mono">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>

    <!-- Submit Leave Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fadeIn"
      @click.self="showModal = false"
    >
      <div class="w-full max-w-md rounded-3xl bg-neu-surface shadow-neu-raised border border-white/80 p-6 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/50">
          <h3 class="text-base font-black text-neu-text tracking-tight">Submit Leave Request</h3>
          <button @click="showModal = false" class="text-neu-muted hover:text-neu-text">&times;</button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">Leave Type</label>
            <select
              v-model="leaveForm.leave_type"
              class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
            >
              <option v-for="lt in leaveTypes" :key="lt.value" :value="lt.value">{{ lt.label }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">Start Date</label>
              <input
                v-model="leaveForm.start_date"
                type="date"
                required
                class="w-full px-4 py-2 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
              />
            </div>
            <div class="space-y-1">
              <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">End Date</label>
              <input
                v-model="leaveForm.end_date"
                type="date"
                required
                class="w-full px-4 py-2 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
              />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">Reason</label>
            <textarea
              v-model="leaveForm.reason"
              rows="3"
              required
              placeholder="State reason for absence..."
              class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs text-neu-text border border-white/40 focus:outline-none resize-none"
            ></textarea>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-2">
            <NeumorphicButton variant="default" size="sm" type="button" @click="showModal = false">
              Cancel
            </NeumorphicButton>
            <NeumorphicButton variant="primary" size="sm" type="submit" :loading="submitting">
              Submit Request
            </NeumorphicButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
