<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import { getRiskScore } from '@/api/employees'
import type { RiskScore, Attendance, Leave } from '@/types'

const route = useRoute()
const router = useRouter()
const store = useEmployeesStore()

const riskScore = ref<RiskScore | null>(null)
const recentAttendance = ref<Attendance[]>([])
const recentLeaves = ref<Leave[]>([])
const loading = ref(true)

const employeeId = computed(() => Number(route.params.id))

const employee = computed(() => store.currentEmployee)

const riskColorClass = computed(() => {
  if (!riskScore.value) return 'text-gray-500'
  switch (riskScore.value.level) {
    case 'low':
      return 'text-green-600 bg-green-50'
    case 'medium':
      return 'text-yellow-600 bg-yellow-50'
    case 'high':
      return 'text-orange-600 bg-orange-50'
    case 'critical':
      return 'text-red-600 bg-red-50'
    default:
      return 'text-gray-500 bg-gray-50'
  }
})

const statusColorClass = computed(() => {
  if (!employee.value) return ''
  switch (employee.value.status) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'inactive':
      return 'bg-gray-100 text-gray-800'
    case 'on_leave':
      return 'bg-yellow-100 text-yellow-800'
    case 'terminated':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})

onMounted(async () => {
  loading.value = true
  try {
    await store.fetchEmployee(employeeId.value)
    try {
      riskScore.value = await getRiskScore(employeeId.value)
    } catch {
      // Risk score may not be available
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center gap-4">
      <button
        @click="router.back()"
        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
      >
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <h1 class="text-2xl font-bold text-gray-900">Employee Detail</h1>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <svg class="w-8 h-8 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
    </div>

    <template v-else-if="employee">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Personal Info Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Full Name</p>
              <p class="font-medium text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Employee ID</p>
              <p class="font-medium text-gray-900">{{ employee.employee_id }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="font-medium text-gray-900">{{ employee.email }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Phone</p>
              <p class="font-medium text-gray-900">{{ employee.phone }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Department</p>
              <p class="font-medium text-gray-900">{{ employee.department }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Position</p>
              <p class="font-medium text-gray-900">{{ employee.position }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Hire Date</p>
              <p class="font-medium text-gray-900">{{ employee.hire_date }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  statusColorClass,
                ]"
              >
                {{ employee.status.charAt(0).toUpperCase() + employee.status.slice(1).replace('_', ' ') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Turnover Risk Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Turnover Risk</h2>
          <div v-if="riskScore" class="text-center">
            <div
              :class="['inline-flex items-center justify-center w-24 h-24 rounded-full text-3xl font-bold', riskColorClass]"
            >
              {{ riskScore.score }}
            </div>
            <p class="mt-3 text-sm font-medium" :class="riskColorClass.split(' ')[0]">
              {{ riskScore.level.toUpperCase() }} RISK
            </p>
            <div class="mt-4 text-left">
              <p class="text-sm text-gray-500 mb-2">Risk Factors:</p>
              <ul class="space-y-1">
                <li
                  v-for="(factor, idx) in riskScore.factors"
                  :key="idx"
                  class="text-sm text-gray-700 flex items-start gap-2"
                >
                  <span class="text-red-400 mt-0.5">&#8226;</span>
                  {{ factor }}
                </li>
              </ul>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 text-sm">
            Risk score not available
          </div>
        </div>
      </div>

      <!-- Attendance Summary & Leave Balance -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Attendance Summary</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-green-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-green-600">92%</p>
              <p class="text-sm text-gray-500 mt-1">Attendance Rate</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-yellow-600">3</p>
              <p class="text-sm text-gray-500 mt-1">Late Days</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-blue-600">180</p>
              <p class="text-sm text-gray-500 mt-1">Present Days</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-red-600">5</p>
              <p class="text-sm text-gray-500 mt-1">Absent Days</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave Balance</h2>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Annual Leave</span>
                <span class="font-medium">12 / 20 days</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: 60%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Sick Leave</span>
                <span class="font-medium">8 / 10 days</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-yellow-500 h-2 rounded-full" style="width: 20%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-600">Personal Leave</span>
                <span class="font-medium">3 / 5 days</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: 40%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Records -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Attendance</h2>
          <div v-if="recentAttendance.length === 0" class="text-center py-8 text-gray-500 text-sm">
            No recent attendance records
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="record in recentAttendance"
              :key="record.id"
              class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
            >
              <span class="text-sm text-gray-700">{{ record.date }}</span>
              <span class="text-sm font-medium">{{ record.check_in }} - {{ record.check_out }}</span>
              <span
                :class="[
                  'text-xs px-2 py-0.5 rounded-full',
                  record.status === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                ]"
              >
                {{ record.status }}
              </span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Leaves</h2>
          <div v-if="recentLeaves.length === 0" class="text-center py-8 text-gray-500 text-sm">
            No recent leave records
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="leave in recentLeaves"
              :key="leave.id"
              class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
            >
              <span class="text-sm text-gray-700">{{ leave.leave_type }}</span>
              <span class="text-sm">{{ leave.start_date }} - {{ leave.end_date }}</span>
              <span
                :class="[
                  'text-xs px-2 py-0.5 rounded-full',
                  leave.status === 'approved'
                    ? 'bg-green-100 text-green-800'
                    : leave.status === 'pending'
                    ? 'bg-yellow-100 text-yellow-800'
                    : 'bg-red-100 text-red-800',
                ]"
              >
                {{ leave.status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-20 text-gray-500">Employee not found</div>
  </div>
</template>
