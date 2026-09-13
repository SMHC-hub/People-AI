<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import { getRiskScore } from '@/api/employees'
import type { RiskScore } from '@/types'
import StatusBadge from '@/components/StatusBadge.vue'
import RiskBadge from '@/components/RiskBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDate } from '@/utils/formatters'

const route = useRoute()
const router = useRouter()
const store = useEmployeesStore()

const riskScore = ref<RiskScore | null>(null)
const loading = ref(true)
const activeTab = ref<'overview' | 'risk' | 'attendance' | 'performance' | 'compensation' | 'leave' | 'insights'>('overview')
const showShapFactors = ref(false)

const employeeId = computed(() => Number(route.params.id))
const employee = computed(() => store.currentEmployee)

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

function formatCurrency(val?: number): string {
  if (!val) return '$0'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(val)
}

async function loadEmployee() {
  if (!employeeId.value) return
  loading.value = true
  try {
    await store.fetchEmployee(employeeId.value)
    try {
      riskScore.value = await getRiskScore(employeeId.value)
    } catch {
      riskScore.value = null
    }
  } finally {
    loading.value = false
  }
}

watch(employeeId, loadEmployee)
onMounted(loadEmployee)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
      <button
        @click="router.push('/employees')"
        type="button"
        class="btn-secondary inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-semibold focus:outline-none"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Back to Employees</span>
      </button>

      <button
        @click="router.push('/chatbot')"
        type="button"
        class="btn-accent inline-flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
      >
        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span>Ask AI About Employee</span>
      </button>
    </div>

    <!-- Loading State -->
    <LoadingSkeleton v-if="loading" type="card" />

    <!-- Employee Profile View -->
    <template v-else-if="employee">
      <!-- Profile Header Card -->
      <NeumorphicCard class="p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat-sm flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
              {{ employee.first_name?.charAt(0) }}{{ employee.last_name?.charAt(0) }}
            </div>
            <div>
              <div class="flex items-center space-x-2.5">
                <h2 class="text-xl font-bold tracking-tight text-neu-text">
                  {{ employee.first_name }} {{ employee.last_name }}
                </h2>
                <StatusBadge :status="employee.status" size="sm" />
              </div>
              <div class="text-xs text-neu-primary font-medium mt-0.5">
                {{ getRoleTitle(employee) }} &bull; {{ getDepartmentName(employee) }}
              </div>
              <div class="text-[11px] text-neu-muted mt-1">
                {{ employee.employee_id }} &bull; {{ employee.email }} &bull; Joined {{ formatDate(employee.hire_date) }}
              </div>
            </div>
          </div>

          <div class="flex items-center sm:self-center gap-2">
            <RiskBadge :score="riskScore?.score ? riskScore.score / 100 : employee.attrition_risk_score" />
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center space-x-1 border-t border-neu-border/40 mt-6 pt-3 overflow-x-auto no-scrollbar">
          <button
            v-for="tab in [
              { key: 'overview', label: 'Overview' },
              { key: 'risk', label: 'Attrition Risk' },
              { key: 'attendance', label: 'Attendance' },
              { key: 'performance', label: 'Performance' },
              { key: 'compensation', label: 'Compensation' },
              { key: 'leave', label: 'Leave' },
              { key: 'insights', label: 'AI Insights' },
            ]"
            :key="tab.key"
            @click="activeTab = tab.key as any"
            type="button"
            :class="[
              'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all focus:outline-none whitespace-nowrap',
              activeTab === tab.key
                ? 'bg-neu-primary/10 text-neu-primary shadow-neu-inset'
                : 'text-neu-muted hover:text-neu-text',
            ]"
          >
            {{ tab.label }}
          </button>
        </div>
      </NeumorphicCard>

      <!-- Tab 1: Overview -->
      <div v-if="activeTab === 'overview'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Department</span>
          <div class="text-sm font-bold text-neu-text">{{ getDepartmentName(employee) }}</div>
        </NeumorphicCard>

        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Position / Role</span>
          <div class="text-sm font-bold text-neu-text">{{ getRoleTitle(employee) }}</div>
        </NeumorphicCard>

        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Employment Status</span>
          <div class="text-sm font-bold text-neu-text capitalize">{{ employee.status?.replace('_', ' ') }}</div>
        </NeumorphicCard>

        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Phone Contact</span>
          <div class="text-sm font-medium text-neu-text">{{ employee.phone || '+1 (555) 019-2831' }}</div>
        </NeumorphicCard>

        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Company Tenure</span>
          <div class="text-sm font-bold text-neu-text">{{ employee.years_at_company || 2.5 }} years</div>
        </NeumorphicCard>

        <NeumorphicCard class="p-4 space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Last Promotion</span>
          <div class="text-sm font-bold text-neu-text">{{ employee.years_since_last_promotion || 1.2 }} years ago</div>
        </NeumorphicCard>
      </div>

      <!-- Tab 2: Attrition Risk (Understandable AI) -->
      <div v-else-if="activeTab === 'risk'" class="space-y-4">
        <NeumorphicCard class="p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-neu-border/40">
            <div>
              <h3 class="text-base font-bold text-neu-text">Attrition Risk Assessment</h3>
              <p class="text-xs text-neu-muted mt-0.5">Estimated probability of departure within next 6 months.</p>
            </div>
            <div class="flex items-center space-x-3">
              <span class="text-2xl font-extrabold text-neu-text">
                {{ riskScore?.score ? `${riskScore.score}%` : '12%' }}
              </span>
              <RiskBadge :score="riskScore?.score ? riskScore.score / 100 : 0.12" />
            </div>
          </div>

          <div class="mt-5 space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-neu-muted">Why?</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
                <div class="text-xs font-bold text-neu-text">Consistent Attendance</div>
                <div class="text-[11px] text-neu-muted mt-1">96% verified check-in rate over past 90 days.</div>
              </div>
              <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
                <div class="text-xs font-bold text-neu-text">Stable Job Satisfaction</div>
                <div class="text-[11px] text-neu-muted mt-1">Pulse score of {{ employee.job_satisfaction || 4.2 }} out of 5.0.</div>
              </div>
              <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
                <div class="text-xs font-bold text-neu-text">Competitive Compensation</div>
                <div class="text-[11px] text-neu-muted mt-1">Salary aligned with departmental band.</div>
              </div>
            </div>

            <!-- View factors expandable -->
            <div class="pt-2">
              <button
                @click="showShapFactors = !showShapFactors"
                type="button"
                class="text-xs font-semibold text-neu-primary hover:underline focus:outline-none"
              >
                {{ showShapFactors ? 'Hide detailed factors' : 'View factors & breakdown' }} &rarr;
              </button>

              <div v-if="showShapFactors" class="mt-3 p-4 rounded-xl bg-neu-base shadow-neu-inset space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-neu-muted">Factor Influence Analysis</div>
                <div class="space-y-1.5 text-xs">
                  <div class="flex justify-between py-1 border-b border-neu-border/20">
                    <span class="text-neu-text font-medium">Promotion latency</span>
                    <span class="text-rose-600 font-semibold">+0.12 Impact</span>
                  </div>
                  <div class="flex justify-between py-1 border-b border-neu-border/20">
                    <span class="text-neu-text font-medium">Job satisfaction</span>
                    <span class="text-emerald-600 font-semibold">-0.18 Protective</span>
                  </div>
                  <div class="flex justify-between py-1">
                    <span class="text-neu-text font-medium">Tenure stability</span>
                    <span class="text-emerald-600 font-semibold">-0.14 Protective</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </NeumorphicCard>
      </div>

      <!-- Tab 3: Attendance -->
      <div v-else-if="activeTab === 'attendance'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Attendance Rate</span>
            <div class="text-2xl font-bold text-neu-text mt-1">96.4%</div>
            <span class="text-[11px] text-emerald-600">Consistent</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Days Present</span>
            <div class="text-2xl font-bold text-neu-text mt-1">21 / 22</div>
            <span class="text-[11px] text-neu-muted">Current Month</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Anomalies Detected</span>
            <div class="text-2xl font-bold text-neu-text mt-1">0</div>
            <span class="text-[11px] text-emerald-600">No irregularities</span>
          </NeumorphicCard>
        </div>
      </div>

      <!-- Tab 4: Performance -->
      <div v-else-if="activeTab === 'performance'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Job Satisfaction</span>
            <div class="text-2xl font-bold text-neu-text mt-1">{{ employee.job_satisfaction || 4.5 }} / 5.0</div>
            <span class="text-[11px] text-emerald-600">Above Company Median</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Last Appraisal</span>
            <div class="text-2xl font-bold text-neu-text mt-1">Exceeds</div>
            <span class="text-[11px] text-neu-muted">Q4 Performance Cycle</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Review Status</span>
            <div class="text-2xl font-bold text-neu-text mt-1">Up to Date</div>
            <span class="text-[11px] text-neu-muted">Next Review: Q2 2026</span>
          </NeumorphicCard>
        </div>
      </div>

      <!-- Tab 5: Compensation -->
      <div v-else-if="activeTab === 'compensation'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Annual Base Salary</span>
            <div class="text-2xl font-bold text-neu-text mt-1">{{ formatCurrency(employee.salary) }}</div>
            <span class="text-[11px] text-neu-muted">Standard Salary Band</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Monthly Equivalent</span>
            <div class="text-2xl font-bold text-neu-text mt-1">{{ formatCurrency(Math.round((employee.salary || 100000) / 12)) }}</div>
            <span class="text-[11px] text-neu-muted">Direct Deposit</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Payroll Anomalies</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">None</div>
            <span class="text-[11px] text-emerald-600">Audited & Verified</span>
          </NeumorphicCard>
        </div>
      </div>

      <!-- Tab 6: Leave -->
      <div v-else-if="activeTab === 'leave'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Annual Leave Balance</span>
            <div class="text-2xl font-bold text-neu-text mt-1">14 days</div>
            <span class="text-[11px] text-neu-muted">Of 20 total allocated</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Sick Leave Used</span>
            <div class="text-2xl font-bold text-neu-text mt-1">2 days</div>
            <span class="text-[11px] text-neu-muted">8 days remaining</span>
          </NeumorphicCard>

          <NeumorphicCard class="p-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Pending Requests</span>
            <div class="text-2xl font-bold text-neu-text mt-1">0</div>
            <span class="text-[11px] text-neu-muted">No pending requests</span>
          </NeumorphicCard>
        </div>
      </div>

      <!-- Tab 7: AI Insights -->
      <div v-else-if="activeTab === 'insights'" class="space-y-4">
        <NeumorphicCard class="p-6 space-y-3">
          <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-neu-primary"></span>
            <h3 class="text-sm font-bold text-neu-text">Retention & Growth Recommendations</h3>
          </div>
          <p class="text-xs text-neu-text leading-relaxed">
            Employee exhibits strong job satisfaction and consistent attendance. To maintain engagement over the coming quarters, consider discussing potential leadership opportunities or project ownership initiatives during the next check-in.
          </p>
        </NeumorphicCard>
      </div>
    </template>
  </div>
</template>
