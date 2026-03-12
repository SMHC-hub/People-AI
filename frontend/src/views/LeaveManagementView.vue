<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLeavesStore } from '@/stores/leaves'
import { createLeave, approveLeave, rejectLeave } from '@/api/leaves'

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

function getStatusBadgeClass(status: string): string {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'approved':
      return 'bg-green-100 text-green-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
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
  <div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-2xl font-bold text-gray-900">Leave Management</h1>
      <button
        @click="showModal = true"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Submit Leave Request
      </button>
    </div>

    <!-- Leave Balance Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-indigo-600">12</p>
        <p class="text-xs text-gray-500 mt-1">Annual</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">8</p>
        <p class="text-xs text-gray-500 mt-1">Sick</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-green-600">3</p>
        <p class="text-xs text-gray-500 mt-1">Personal</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-purple-600">23</p>
        <p class="text-xs text-gray-500 mt-1">Total Remaining</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-orange-600">12</p>
        <p class="text-xs text-gray-500 mt-1">Total Used</p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-2xl font-bold text-gray-600">35</p>
        <p class="text-xs text-gray-500 mt-1">Total Allocated</p>
      </div>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Leave Requests</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="store.loading">
              <td colspan="8" class="px-6 py-12 text-center text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="store.leaves.length === 0">
              <td colspan="8" class="px-6 py-12 text-center text-gray-500">No leave requests found</td>
            </tr>
            <tr v-else v-for="leave in store.leaves" :key="leave.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.employee_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ leave.leave_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.start_date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.end_date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ leave.days }}</td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ leave.reason }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusBadgeClass(leave.status),
                  ]"
                >
                  {{ leave.status.charAt(0).toUpperCase() + leave.status.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="leave.status === 'pending'" class="flex gap-2">
                  <button
                    @click="handleApprove(leave.id)"
                    class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors"
                  >
                    Approve
                  </button>
                  <button
                    @click="handleReject(leave.id)"
                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors"
                  >
                    Reject
                  </button>
                </div>
                <span v-else class="text-sm text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Submit Leave Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="showModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Submit Leave Request</h2>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
            <select
              v-model="leaveForm.leave_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            >
              <option v-for="lt in leaveTypes" :key="lt.value" :value="lt.value">{{ lt.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input
              v-model="leaveForm.start_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input
              v-model="leaveForm.end_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
            <textarea
              v-model="leaveForm.reason"
              required
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
              placeholder="Provide a reason for your leave..."
            />
          </div>
          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="showModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors text-sm font-medium"
            >
              {{ submitting ? 'Submitting...' : 'Submit' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
