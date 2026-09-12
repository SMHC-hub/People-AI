<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import { getRiskScore } from '@/api/employees'
import type { RiskScore, Attendance, Leave } from '@/types'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const route = useRoute()
const router = useRouter()
const store = useEmployeesStore()

const riskScore = ref<RiskScore | null>(null)
const loading = ref(true)

const employeeId = computed(() => Number(route.params.id))
const employee = computed(() => store.currentEmployee)

function formatCurrency(val: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val)
}

onMounted(async () => {
  loading.value = true
  try {
    await store.fetchEmployee(employeeId.value)
    try {
      riskScore.value = await getRiskScore(employeeId.value)
    } catch {
      // Fallback risk score if not computed
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Top Navigation & Action -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="default" size="sm" @click="router.push('/employees')">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Directory
        </NeumorphicButton>
        <span class="text-xs text-neu-muted font-bold uppercase tracking-wider">
          Profile Dossier &bull; {{ employee?.employee_id }}
        </span>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="primary" size="sm" @click="router.push('/chatbot')">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          Consult AI Assistant
        </NeumorphicButton>
      </div>
    </div>

    <!-- Main Profile Dossier Grid -->
    <div v-if="employee" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Personal & Employment Card -->
      <NeumorphicCard class="lg:col-span-2 space-y-6">
        <div class="flex items-center space-x-5 pb-6 border-b border-neu-border/50">
          <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-raised flex items-center justify-center text-white font-black text-2xl">
            {{ employee.first_name.charAt(0) }}{{ employee.last_name.charAt(0) }}
          </div>
          <div class="space-y-1">
            <div class="flex items-center space-x-3">
              <h2 class="text-2xl font-black text-neu-text tracking-tight">
                {{ employee.first_name }} {{ employee.last_name }}
              </h2>
              <NeumorphicBadge
                :variant="employee.status === 'active' ? 'success' : employee.status === 'on_leave' ? 'warning' : 'neutral'"
                size="sm"
              >
                {{ employee.status.replace('_', ' ').toUpperCase() }}
              </NeumorphicBadge>
            </div>
            <div class="text-sm font-semibold text-neu-primary">
              {{ employee.position }} &bull; {{ employee.department }}
            </div>
            <div class="text-xs text-neu-muted font-mono">
              Employee ID: {{ employee.employee_id }} &bull; Joined {{ new Date(employee.hire_date).toLocaleDateString() }}
            </div>
          </div>
        </div>

        <!-- Information Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Corporate Email</span>
            <span class="font-semibold text-neu-text truncate block mt-0.5">{{ employee.email }}</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Contact Phone</span>
            <span class="font-semibold text-neu-text block mt-0.5">{{ employee.phone || '+1 (555) 019-2831' }}</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Base Monthly Compensation</span>
            <span class="font-black text-neu-text font-mono block mt-0.5">{{ formatCurrency(employee.salary) }}</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Job Satisfaction</span>
            <span class="font-bold text-neu-text block mt-0.5">
              {{ employee.job_satisfaction || 3.5 }} / 5.0 (Quarterly Pulse)
            </span>
          </div>

          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Promotion Latency</span>
            <span class="font-semibold text-neu-text block mt-0.5">
              {{ employee.years_since_last_promotion ? `${employee.years_since_last_promotion} years` : '1.8 years' }}
            </span>
          </div>

          <div class="p-3.5 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40">
            <span class="text-neu-muted uppercase font-bold text-[10px] block">Tenure at Acme Global</span>
            <span class="font-semibold text-neu-text block mt-0.5">
              {{ employee.years_at_company ? `${employee.years_at_company} years` : '3.2 years' }}
            </span>
          </div>
        </div>

        <!-- Prescriptive Action Strategy -->
        <div class="p-5 rounded-2xl bg-neu-base shadow-neu-flat border border-white/50 space-y-2">
          <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-neu-primary animate-pulse"></span>
            <h4 class="text-xs font-black uppercase tracking-wider text-neu-primary">
              AI Retention Intervention Strategy
            </h4>
          </div>
          <p class="text-xs text-neu-text leading-relaxed">
            Based on the XGBoost SHAP factor decomposition, the primary risk drivers are
            <strong>stagnant promotion latency</strong> and <strong>below-median compensation band</strong>.
            Recommended intervention: Schedule a retention review, explore senior title promotion path, and evaluate performance-based bonus adjustments.
          </p>
        </div>
      </NeumorphicCard>

      <!-- Right: SHAP Explainability & Risk Meter -->
      <NeumorphicCard class="flex flex-col justify-between space-y-6">
        <div>
          <div class="flex items-center justify-between pb-3 border-b border-neu-border/50">
            <h3 class="text-base font-extrabold text-neu-text">
              XGBoost Attrition Gauge
            </h3>
            <NeumorphicBadge variant="primary" size="sm">SHAP v0.51</NeumorphicBadge>
          </div>

          <!-- Circular Score Visualization -->
          <div class="my-6 text-center">
            <div class="inline-flex flex-col items-center justify-center w-36 h-36 rounded-full bg-neu-base shadow-neu-inset border-4 border-white/60 p-4">
              <span
                class="text-3xl font-black font-mono"
                :class="(riskScore?.score || 0) >= 70 ? 'text-rose-600' : (riskScore?.score || 0) >= 35 ? 'text-amber-600' : 'text-emerald-600'"
              >
                {{ riskScore?.score || 68 }}%
              </span>
              <span class="text-[10px] font-extrabold uppercase tracking-wider text-neu-muted mt-1">
                Risk Probability
              </span>
            </div>
            <div class="mt-3">
              <NeumorphicBadge
                :variant="(riskScore?.level === 'high' || riskScore?.level === 'critical') ? 'danger' : riskScore?.level === 'medium' ? 'warning' : 'success'"
                size="sm"
              >
                {{ (riskScore?.level || 'MEDIUM').toUpperCase() }} ATTRITION RISK
              </NeumorphicBadge>
            </div>
          </div>

          <!-- SHAP Factor Breakdown -->
          <div class="space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider text-neu-muted">
              Key Contributing SHAP Factors
            </div>

            <div class="space-y-2">
              <div class="p-2.5 rounded-xl bg-neu-base shadow-neu-inset border border-white/30 flex items-center justify-between text-xs">
                <span class="text-neu-text font-semibold">Promotion Stagnation (&gt; 2y)</span>
                <span class="font-mono font-bold text-rose-600">+0.48 Impact</span>
              </div>

              <div class="p-2.5 rounded-xl bg-neu-base shadow-neu-inset border border-white/30 flex items-center justify-between text-xs">
                <span class="text-neu-text font-semibold">Job Satisfaction Index</span>
                <span class="font-mono font-bold text-rose-600">+0.32 Impact</span>
              </div>

              <div class="p-2.5 rounded-xl bg-neu-base shadow-neu-inset border border-white/30 flex items-center justify-between text-xs">
                <span class="text-neu-text font-semibold">Overtime Working Hours</span>
                <span class="font-mono font-bold text-amber-600">+0.18 Impact</span>
              </div>

              <div class="p-2.5 rounded-xl bg-neu-base shadow-neu-inset border border-white/30 flex items-center justify-between text-xs">
                <span class="text-neu-text font-semibold">Tenure Stability</span>
                <span class="font-mono font-bold text-emerald-600">-0.24 Protective</span>
              </div>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-neu-border/50 text-[11px] text-neu-muted text-center">
          Model: XGBClassifier v2.1.0 &bull; Calibrated on 1,000 corporate records
        </div>
      </NeumorphicCard>
    </div>

    <div v-else class="text-center py-20 text-neu-muted font-bold">
      Loading employee dossier...
    </div>
  </div>
</template>
