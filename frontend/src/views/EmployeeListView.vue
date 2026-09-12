<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const router = useRouter()
const route = useRoute()
const store = useEmployeesStore()

const search = ref('')
const departmentFilter = ref(route.query.department as string || '')
const statusFilter = ref('')
const page = ref(1)
const pageSize = 15

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

function getStatusBadge(status: string): 'success' | 'warning' | 'danger' | 'neutral' {
  switch (status) {
    case 'active':
      return 'success'
    case 'on_leave':
      return 'warning'
    case 'terminated':
      return 'danger'
    default:
      return 'neutral'
  }
}

function formatSalary(val: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val)
}

watch([search, departmentFilter, statusFilter], () => {
  page.value = 1
  fetchData()
})

watch(page, fetchData)

onMounted(fetchData)
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <h2 class="text-2xl font-black tracking-tight text-neu-text">
            Workforce Directory
          </h2>
          <NeumorphicBadge variant="primary" size="sm">{{ store.totalCount }} Total Records</NeumorphicBadge>
        </div>
        <p class="text-sm text-neu-muted mt-1">
          Search and inspect employee profiles, compensation history, and ML-inferred attrition risks.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="default" size="sm" @click="fetchData">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh Directory
        </NeumorphicButton>
      </div>
    </div>

    <!-- Filters Card -->
    <NeumorphicCard>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Search Input -->
        <div class="sm:col-span-1">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted mb-1">
            Search Employee
          </label>
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Filter by name, ID, or title..."
              class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
            />
            <svg class="w-4 h-4 text-neu-muted absolute right-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Department Filter -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted mb-1">
            Department
          </label>
          <select
            v-model="departmentFilter"
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted mb-1">
            Employment Status
          </label>
          <select
            v-model="statusFilter"
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
          >
            <option value="">All Statuses</option>
            <option v-for="s in statuses" :key="s" :value="s">
              {{ s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') }}
            </option>
          </select>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Employees Table Card -->
    <NeumorphicCard>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Employee</th>
              <th class="py-3.5 px-4">Department & Role</th>
              <th class="py-3.5 px-4 text-center">Status</th>
              <th class="py-3.5 px-4 text-right">Base Salary</th>
              <th class="py-3.5 px-4 text-center">Satisfaction</th>
              <th class="py-3.5 px-4 text-center">Attrition Risk</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="emp in store.employees"
              :key="emp.id"
              class="hover:bg-neu-base/60 transition-colors duration-150 cursor-pointer"
              @click="handleRowClick(emp.id)"
            >
              <!-- Name & ID -->
              <td class="py-3.5 px-4">
                <div class="flex items-center space-x-3">
                  <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat flex items-center justify-center text-white font-black text-xs">
                    {{ emp.first_name?.charAt(0) }}{{ emp.last_name?.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-extrabold text-neu-text">
                      {{ emp.first_name }} {{ emp.last_name }}
                    </div>
                    <div class="text-[11px] font-mono text-neu-muted">
                      {{ emp.employee_id }} &bull; {{ emp.email }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Department & Role -->
              <td class="py-3.5 px-4">
                <div class="font-semibold text-neu-text text-xs">{{ emp.position }}</div>
                <div class="text-[11px] text-neu-muted">{{ emp.department }}</div>
              </td>

              <!-- Status -->
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge :variant="getStatusBadge(emp.status)" size="sm">
                  {{ emp.status.replace('_', ' ').toUpperCase() }}
                </NeumorphicBadge>
              </td>

              <!-- Salary -->
              <td class="py-3.5 px-4 text-right font-medium text-neu-muted font-mono">
                {{ formatSalary(emp.salary) }}
              </td>

              <!-- Satisfaction -->
              <td class="py-3.5 px-4 text-center font-bold text-xs">
                <span :class="(emp.job_satisfaction || 3) >= 4 ? 'text-emerald-600' : (emp.job_satisfaction || 3) >= 3 ? 'text-neu-text' : 'text-rose-600'">
                  {{ emp.job_satisfaction ? `${emp.job_satisfaction} / 5` : '3.5 / 5' }}
                </span>
              </td>

              <!-- Risk -->
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge
                  :variant="(emp.attrition_risk_score || 0) >= 0.7 ? 'danger' : (emp.attrition_risk_score || 0) >= 0.35 ? 'warning' : 'success'"
                  size="sm"
                >
                  {{ (emp.attrition_risk_score || 0) >= 0.7 ? 'HIGH RISK' : (emp.attrition_risk_score || 0) >= 0.35 ? 'MEDIUM' : 'LOW RISK' }}
                </NeumorphicBadge>
              </td>

              <!-- Action -->
              <td class="py-3.5 px-4 text-right">
                <NeumorphicButton variant="default" size="sm" @click.stop="handleRowClick(emp.id)">
                  Inspect
                </NeumorphicButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between pt-6 border-t border-neu-border/50">
        <span class="text-xs font-medium text-neu-muted">
          Showing page {{ store.currentPage }} of {{ store.totalPages }} ({{ store.totalCount }} total)
        </span>
        <div class="flex items-center space-x-2">
          <NeumorphicButton
            variant="default"
            size="sm"
            :disabled="page <= 1"
            @click="page--"
          >
            Previous
          </NeumorphicButton>
          <NeumorphicButton
            variant="default"
            size="sm"
            :disabled="page >= store.totalPages"
            @click="page++"
          >
            Next
          </NeumorphicButton>
        </div>
      </div>
    </NeumorphicCard>
  </div>
</template>
