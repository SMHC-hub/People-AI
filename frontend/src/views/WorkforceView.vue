<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getWorkforceStats, getWorkforceHeatmap, getWorkforceInsights } from '@/api/workforce'
import type { WorkforceStats, DepartmentRisk, WorkforceInsight } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import RiskBadge from '@/components/RiskBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const router = useRouter()
const loading = ref(true)
const stats = ref<WorkforceStats | null>(null)
const heatmap = ref<DepartmentRisk[]>([])
const insights = ref<WorkforceInsight[]>([])
const selectedDepartment = ref<string>('all')

async function loadData() {
  loading.value = true
  try {
    const [statsRes, heatmapRes, insightsRes] = await Promise.all([
      getWorkforceStats(),
      getWorkforceHeatmap(),
      getWorkforceInsights(),
    ])
    stats.value = statsRes
    heatmap.value = heatmapRes
    insights.value = insightsRes
  } catch (err) {
    console.error('Failed to fetch workforce data', err)
  } finally {
    loading.value = false
  }
}

function viewEmployeesInDepartment(dept: string) {
  router.push({ path: '/employees', query: { department: dept } })
}

onMounted(loadData)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Workforce Risk"
      subtitle="Identify teams and employees that may need attention."
    >
      <template #action>
        <button
          @click="loadData"
          type="button"
          class="btn-secondary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
        >
          <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Refresh</span>
        </button>
      </template>
    </PageHeader>

    <LoadingSkeleton v-if="loading" type="card" />

    <template v-else>
      <!-- KPI Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <NeumorphicStatCard
          title="Total Workforce"
          :value="stats?.total_employees || 1000"
          caption="Active roster"
        />
        <NeumorphicStatCard
          title="Elevated Risk"
          :value="stats?.high_risk_count || 142"
          change="Needs review"
          changeType="negative"
        />
        <NeumorphicStatCard
          title="Average Satisfaction"
          :value="stats?.avg_job_satisfaction ? `${stats.avg_job_satisfaction} / 5.0` : '3.8 / 5.0'"
          change="Company-wide"
          changeType="positive"
        />
        <NeumorphicStatCard
          title="Turnover Baseline"
          :value="stats?.avg_turnover_risk ? `${(stats.avg_turnover_risk * 100).toFixed(1)}%` : '14.2%'"
          caption="Current average"
        />
      </div>

      <!-- Section 1 & 2: Risk Distribution & Top Factors -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Risk Distribution (2 cols) -->
        <NeumorphicCard class="lg:col-span-2 p-5">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-sm font-bold text-neu-text tracking-tight">Risk Distribution</h2>
              <p class="text-xs text-neu-muted mt-0.5">Workforce segmented by retention stability</p>
            </div>
            <span class="text-xs font-semibold text-neu-muted">1,000 Employees</span>
          </div>

          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span class="text-emerald-700">Low Risk (&lt; 35%)</span>
                <span class="text-neu-text">{{ stats?.low_risk_count || 650 }} employees (65%)</span>
              </div>
              <div class="w-full h-2.5 bg-neu-base rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: 65%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span class="text-amber-700">Medium Risk (35% - 70%)</span>
                <span class="text-neu-text">{{ stats?.medium_risk_count || 208 }} employees (21%)</span>
              </div>
              <div class="w-full h-2.5 bg-neu-base rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: 21%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between text-xs font-semibold mb-1">
                <span class="text-rose-700">Elevated Risk (&gt; 70%)</span>
                <span class="text-neu-text">{{ stats?.high_risk_count || 142 }} employees (14%)</span>
              </div>
              <div class="w-full h-2.5 bg-neu-base rounded-full overflow-hidden">
                <div class="h-full bg-rose-500 rounded-full" style="width: 14%"></div>
              </div>
            </div>
          </div>
        </NeumorphicCard>

        <!-- Top Factors (1 col) -->
        <NeumorphicCard class="p-5">
          <h2 class="text-sm font-bold text-neu-text tracking-tight mb-1">Top Factors</h2>
          <p class="text-xs text-neu-muted mb-4">Key indicators influencing retention</p>

          <div class="space-y-2.5">
            <div class="p-3 rounded-xl bg-neu-base/60 text-xs flex items-center justify-between">
              <span class="font-medium text-neu-text">Promotion Latency</span>
              <span class="font-bold text-rose-600">High impact</span>
            </div>
            <div class="p-3 rounded-xl bg-neu-base/60 text-xs flex items-center justify-between">
              <span class="font-medium text-neu-text">Overtime Hours</span>
              <span class="font-bold text-amber-600">Moderate</span>
            </div>
            <div class="p-3 rounded-xl bg-neu-base/60 text-xs flex items-center justify-between">
              <span class="font-medium text-neu-text">Satisfaction Score</span>
              <span class="font-bold text-rose-600">High impact</span>
            </div>
            <div class="p-3 rounded-xl bg-neu-base/60 text-xs flex items-center justify-between">
              <span class="font-medium text-neu-text">Tenure Stability</span>
              <span class="font-bold text-emerald-600">Protective</span>
            </div>
          </div>
        </NeumorphicCard>
      </div>

      <!-- Department Risk Table -->
      <NeumorphicCard class="p-0 overflow-hidden">
        <div class="p-4 border-b border-neu-border/40 flex items-center justify-between bg-neu-surface/40">
          <div>
            <h2 class="text-sm font-bold text-neu-text tracking-tight">Department Risk</h2>
            <p class="text-xs text-neu-muted mt-0.5">Headcount and elevated risk breakdown by team</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
                <th class="py-3 px-5">Department</th>
                <th class="py-3 px-4 text-center">Headcount</th>
                <th class="py-3 px-4 text-center">Elevated Risk</th>
                <th class="py-3 px-4 text-center">Risk Index</th>
                <th class="py-3 px-5 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/30">
              <tr
                v-for="dept in heatmap"
                :key="dept.department"
                class="hover:bg-neu-base/40 transition-colors"
              >
                <td class="py-3 px-5 font-bold text-neu-text">
                  {{ dept.department }}
                </td>
                <td class="py-3 px-4 text-center text-neu-text">
                  {{ dept.employee_count }}
                </td>
                <td class="py-3 px-4 text-center font-bold" :class="dept.high_risk_count > 20 ? 'text-rose-600' : 'text-neu-text'">
                  {{ dept.high_risk_count }}
                </td>
                <td class="py-3 px-4 text-center">
                  <RiskBadge :score="dept.risk_percentage / 100" size="sm" />
                </td>
                <td class="py-3 px-5 text-right">
                  <button
                    @click="viewEmployeesInDepartment(dept.department)"
                    type="button"
                    class="text-xs font-semibold text-neu-primary hover:underline focus:outline-none"
                  >
                    View Team &rarr;
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </NeumorphicCard>
    </template>
  </div>
</template>
