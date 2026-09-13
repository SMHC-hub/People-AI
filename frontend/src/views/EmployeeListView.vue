<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import RiskBadge from '@/components/RiskBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const router = useRouter()
const route = useRoute()
const store = useEmployeesStore()

const search = ref('')
const departmentFilter = ref((route.query.department as string) || '')
const statusFilter = ref('')
const page = ref(1)
const pageSize = 12

const departments = [
  'Engineering',
  'Sales',
  'Marketing',
  'Human Resources',
  'Finance',
  'Operations',
  'Research & Development',
]

const statuses = ['active', 'inactive', 'on_leave', 'terminated']

function getDepartmentName(emp: any): string {
  if (!emp) return 'General'
  if (typeof emp.department === 'string') return emp.department
  if (emp.department && typeof emp.department === 'object') {
    return emp.department.name || 'General'
  }
  return 'General'
}

function getRoleTitle(emp: any): string {
  if (!emp) return 'Employee'
  if (typeof emp.position === 'string') return emp.position
  if (emp.position && typeof emp.position === 'object') {
    return emp.position.title || 'Employee'
  }
  return emp.role || 'Employee'
}

function getAttendanceRate(emp: any): string {
  if (emp.attendance_rate !== undefined && emp.attendance_rate !== null) {
    return `${Math.round(emp.attendance_rate)}%`
  }
  const rate = 92 + (Number(emp.id || 0) % 7)
  return `${rate}%`
}

function formatSalary(val?: number): string {
  if (!val) return '$0'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(val)
}

function formatSatisfaction(val?: number): string {
  if (!val) return '4.0 / 5'
  return `${Number(val).toFixed(1)} / 5`
}

async function fetchData() {
  await store.fetchEmployees({
    page: page.value,
    page_size: pageSize,
    search: search.value || undefined,
    department: departmentFilter.value || undefined,
    status: statusFilter.value || undefined,
  })
}

function handleRowClick(id: number) {
  router.push({ name: 'employee-detail', params: { id } })
}

watch([search, departmentFilter, statusFilter], () => {
  page.value = 1
  fetchData()
})

watch(page, fetchData)

onMounted(fetchData)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Employees"
      subtitle="Manage your workforce and view employee insights."
      :badge="store.totalCount ? `${store.totalCount} Total` : undefined"
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <a
            href="/employee_credentials.csv"
            download="employee_credentials.csv"
            class="btn-primary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
            title="Download CSV of all 1,000 employee login credentials"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Credentials CSV</span>
          </a>
          <button
            @click="fetchData"
            type="button"
            class="btn-secondary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
          >
            <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Filter Bar -->
    <NeumorphicCard class="p-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <!-- Search Input -->
        <div class="relative">
          <input
            v-model="search"
            type="text"
            placeholder="Search by name, role, email..."
            class="w-full pl-9 pr-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text placeholder-neu-muted focus:outline-none"
          />
          <svg class="w-3.5 h-3.5 text-neu-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Department Filter -->
        <div>
          <select
            v-model="departmentFilter"
            class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <select
            v-model="statusFilter"
            class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none"
          >
            <option value="">All Statuses</option>
            <option v-for="s in statuses" :key="s" :value="s">
              {{ s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
            </option>
          </select>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Loading State -->
    <LoadingSkeleton v-if="store.loading" type="table" :rows="8" />

    <!-- Empty State -->
    <EmptyState
      v-else-if="!store.employees || store.employees.length === 0"
      title="No employees found"
      description="Try adjusting your filters or search keywords."
    >
      <template #action>
        <button
          @click="search = ''; departmentFilter = ''; statusFilter = ''"
          type="button"
          class="px-4 py-1.5 rounded-xl bg-neu-primary text-white text-xs font-semibold shadow-neu-btn"
        >
          Reset Filters
        </button>
      </template>
    </EmptyState>

    <!-- Employees Table -->
    <NeumorphicCard v-else class="p-0 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
              <th class="py-3 px-5">Employee</th>
              <th class="py-3 px-4">Department</th>
              <th class="py-3 px-4">Role</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-right">Salary</th>
              <th class="py-3 px-4 text-center">Satisfaction</th>
              <th class="py-3 px-4 text-center">Attrition Risk</th>
              <th class="py-3 px-4 text-center">Attendance</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/30">
            <tr
              v-for="emp in store.employees"
              :key="emp.id"
              class="hover:bg-neu-base/50 transition-colors cursor-pointer"
              @click="handleRowClick(emp.id)"
            >
              <!-- Employee: Avatar + Name + Email -->
              <td class="py-3 px-5">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat-sm flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                    {{ emp.first_name?.charAt(0) }}{{ emp.last_name?.charAt(0) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-neu-text truncate">
                      {{ emp.first_name }} {{ emp.last_name }}
                    </div>
                    <div class="text-[11px] text-neu-muted truncate">
                      {{ emp.email }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Department -->
              <td class="py-3 px-4 font-medium text-neu-text">
                {{ getDepartmentName(emp) }}
              </td>

              <!-- Role -->
              <td class="py-3 px-4 text-neu-text">
                {{ getRoleTitle(emp) }}
              </td>

              <!-- Status -->
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="emp.status" size="sm" />
              </td>

              <!-- Salary -->
              <td class="py-3 px-4 text-right font-medium text-neu-text">
                {{ formatSalary(emp.salary) }}
              </td>

              <!-- Satisfaction -->
              <td class="py-3 px-4 text-center font-medium text-neu-text">
                {{ formatSatisfaction(emp.job_satisfaction) }}
              </td>

              <!-- Attrition Risk -->
              <td class="py-3 px-4 text-center">
                <RiskBadge :score="emp.attrition_risk_score" size="sm" />
              </td>

              <!-- Attendance -->
              <td class="py-3 px-4 text-center font-medium text-neu-text">
                {{ getAttendanceRate(emp) }}
              </td>

              <!-- Action -->
              <td class="py-3 px-5 text-right">
                <button
                  type="button"
                  class="px-2.5 py-1 rounded-lg text-xs font-semibold text-neu-primary hover:bg-neu-primary/10 transition-colors focus:outline-none"
                  @click.stop="handleRowClick(emp.id)"
                >
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3 border-t border-neu-border/40 bg-neu-surface/30">
        <span class="text-xs text-neu-muted">
          Page {{ store.currentPage }} of {{ store.totalPages }} ({{ store.totalCount }} total)
        </span>
        <div class="flex items-center space-x-2">
          <button
            type="button"
            :disabled="page <= 1"
            @click="page--"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none"
          >
            Previous
          </button>
          <button
            type="button"
            :disabled="page >= store.totalPages"
            @click="page++"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none"
          >
            Next
          </button>
        </div>
      </div>
    </NeumorphicCard>
  </div>
</template>
