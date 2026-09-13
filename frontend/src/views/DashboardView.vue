<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import { useRouter } from 'vue-router'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart, LineChart, PieChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
} from 'echarts/components'
import VChart from 'vue-echarts'
import { getWorkforceStats, getWorkforceHeatmap, getWorkforceInsights } from '@/api/workforce'
import { getAttendanceStats } from '@/api/attendance'
import type { WorkforceStats, DepartmentRisk, WorkforceInsight, AttendanceStats } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

use([
  CanvasRenderer,
  BarChart,
  LineChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
])

provide('THEME_KEY', 'light')

const router = useRouter()
const loading = ref(true)
const workforceStats = ref<WorkforceStats | null>(null)
const attendanceStats = ref<AttendanceStats | null>(null)
const insights = ref<WorkforceInsight[]>([])
const departmentRisks = ref<DepartmentRisk[]>([])

const workforceChartOption = ref<any>({
  tooltip: { trigger: 'axis' },
  grid: { top: 30, right: 20, bottom: 30, left: 35 },
  xAxis: {
    type: 'category',
    data: ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance', 'Ops', 'R&D'],
    axisLine: { lineStyle: { color: '#D8DFE8' } },
    axisLabel: { color: '#8A94A6', fontSize: 11 },
  },
  yAxis: {
    type: 'value',
    splitLine: { lineStyle: { color: '#EAEEF2', type: 'dashed' } },
    axisLabel: { color: '#8A94A6', fontSize: 11 },
  },
  series: [
    {
      name: 'Employees',
      type: 'bar',
      data: [180, 195, 135, 60, 100, 120, 210],
      itemStyle: {
        color: '#2D6CDF',
        borderRadius: [4, 4, 0, 0],
      },
      barWidth: '32%',
    },
    {
      name: 'Elevated Risk',
      type: 'bar',
      data: [24, 45, 18, 5, 6, 12, 32],
      itemStyle: {
        color: '#EF4444',
        borderRadius: [4, 4, 0, 0],
      },
      barWidth: '32%',
    },
  ],
})

async function loadDashboardData() {
  loading.value = true
  try {
    const [wfStats, attStats, heatmaps, ins] = await Promise.all([
      getWorkforceStats().catch(() => null),
      getAttendanceStats().catch(() => null),
      getWorkforceHeatmap().catch(() => []),
      getWorkforceInsights().catch(() => []),
    ])

    workforceStats.value = wfStats
    attendanceStats.value = attStats
    insights.value = ins

    const depts: DepartmentRisk[] = (wfStats?.departments && wfStats.departments.length > 0)
      ? wfStats.departments.map(d => ({
          department: d.name,
          employee_count: d.headcount,
          high_risk_count: d.high_risk_count,
          risk_percentage: d.headcount ? (d.high_risk_count / d.headcount) * 100 : 0,
          avg_satisfaction: 3.8,
          avg_years_promotion: 2.1,
        }))
      : [
          { department: 'Engineering', employee_count: 180, high_risk_count: 24, risk_percentage: 13.3, avg_satisfaction: 3.8, avg_years_promotion: 2.1 },
          { department: 'Sales', employee_count: 195, high_risk_count: 45, risk_percentage: 23.1, avg_satisfaction: 3.6, avg_years_promotion: 1.8 },
          { department: 'Marketing', employee_count: 135, high_risk_count: 18, risk_percentage: 13.3, avg_satisfaction: 4.1, avg_years_promotion: 2.3 },
          { department: 'HR', employee_count: 60, high_risk_count: 5, risk_percentage: 8.3, avg_satisfaction: 4.2, avg_years_promotion: 2.5 },
          { department: 'Finance', employee_count: 100, high_risk_count: 6, risk_percentage: 6.0, avg_satisfaction: 3.9, avg_years_promotion: 2.0 },
          { department: 'Operations', employee_count: 120, high_risk_count: 12, risk_percentage: 10.0, avg_satisfaction: 3.7, avg_years_promotion: 2.4 },
        ]

    departmentRisks.value = depts

    workforceChartOption.value.xAxis.data = depts.map(d => d.department)
    workforceChartOption.value.series[0].data = depts.map(d => d.employee_count)
    workforceChartOption.value.series[1].data = depts.map(d => d.high_risk_count)
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboardData)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Dashboard"
      subtitle="Your workforce at a glance."
    >
      <template #action>
        <button
          @click="loadDashboardData"
          type="button"
          class="btn-secondary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Refresh</span>
        </button>
      </template>
    </PageHeader>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <LoadingSkeleton v-for="i in 4" :key="i" type="card" />
    </div>

    <!-- ROW 1: 4 Clean KPI Cards -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <!-- 1. Total Employees -->
      <NeumorphicStatCard
        title="Employees"
        :value="workforceStats?.total_employees || 1000"
        change="↑ 4.2%"
        changeType="positive"
        caption="Active roster"
      >
        <template #icon>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <!-- 2. Attrition Risk -->
      <NeumorphicStatCard
        title="Attrition Risk"
        :value="workforceStats?.avg_turnover_risk ? (workforceStats.avg_turnover_risk * 100).toFixed(1) + '%' : '14.2%'"
        change="Stable"
        changeType="neutral"
        caption="Company-wide index"
      >
        <template #icon>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <!-- 3. Attendance -->
      <NeumorphicStatCard
        title="Attendance"
        :value="`${attendanceStats?.attendance_rate || 95.8}%`"
        change="945 present"
        changeType="positive"
        caption="Today's attendance"
      >
        <template #icon>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <!-- 4. Payroll Alerts -->
      <NeumorphicStatCard
        title="Payroll Alerts"
        value="4"
        change="Action needed"
        changeType="negative"
        caption="Pending reviews"
      >
        <template #icon>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- ROW 2: Balanced 2-Column (Workforce Risk Chart + Top 3 AI Insights) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: Workforce Risk Chart (2 cols) -->
      <NeumorphicCard class="lg:col-span-2 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-sm font-bold text-neu-text tracking-tight">Workforce Risk</h2>
            <p class="text-xs text-neu-muted mt-0.5">Department staffing and risk levels</p>
          </div>
          <button
            @click="router.push('/workforce')"
            type="button"
            class="text-xs font-semibold text-neu-primary hover:underline focus:outline-none"
          >
            View Details &rarr;
          </button>
        </div>
        <VChart :option="workforceChartOption" style="height: 280px" autoresize />
      </NeumorphicCard>

      <!-- Right: AI Insights (Top 3 only) -->
      <NeumorphicCard class="p-5 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-neu-text tracking-tight">AI Insights</h2>
            <span class="text-[10px] font-semibold uppercase text-neu-muted">Top 3</span>
          </div>

          <div class="space-y-3">
            <div
              v-for="item in (insights.length ? insights.slice(0, 3) : [
                { id: 1, title: 'High attrition risk increased in Engineering.', action: '/workforce' },
                { id: 2, title: 'Attendance anomalies detected in Operations.', action: '/attendance' },
                { id: 3, title: 'Payroll requires review for 4 employees.', action: '/payrolls' },
              ])"
              :key="item.id"
              class="p-3 rounded-xl bg-neu-base/60 flex items-start justify-between gap-3 border border-neu-border/30"
            >
              <div class="flex items-start space-x-2.5">
                <span class="w-2 h-2 rounded-full bg-neu-primary mt-1.5 flex-shrink-0"></span>
                <span class="text-xs text-neu-text font-medium leading-snug">
                  {{ item.title }}
                </span>
              </div>
              <button
                @click="router.push((item as any).action || '/workforce')"
                type="button"
                class="text-xs font-semibold text-neu-primary hover:underline flex-shrink-0 focus:outline-none"
              >
                View
              </button>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-neu-border/30 text-right">
          <button
            @click="router.push('/chatbot')"
            type="button"
            class="btn-accent px-4 py-2 text-xs font-semibold focus:outline-none inline-flex items-center space-x-1.5 cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>Ask AI Assistant &rarr;</span>
          </button>
        </div>
      </NeumorphicCard>
    </div>

    <!-- ROW 3: Compact Sections (Attrition Overview, Department Risk, Recent Alerts) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <!-- 1. Attrition Overview -->
      <NeumorphicCard class="p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-neu-muted mb-3">Attrition Overview</h3>
        <div class="space-y-2.5">
          <div class="flex items-center justify-between text-xs">
            <span class="text-neu-text font-medium">Low Risk</span>
            <span class="font-bold text-emerald-600">{{ workforceStats?.low_risk_count || 650 }} (65%)</span>
          </div>
          <div class="w-full h-1.5 bg-neu-base rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full" style="width: 65%"></div>
          </div>

          <div class="flex items-center justify-between text-xs pt-1">
            <span class="text-neu-text font-medium">Medium Risk</span>
            <span class="font-bold text-amber-600">{{ workforceStats?.medium_risk_count || 208 }} (21%)</span>
          </div>
          <div class="w-full h-1.5 bg-neu-base rounded-full overflow-hidden">
            <div class="h-full bg-amber-500 rounded-full" style="width: 21%"></div>
          </div>

          <div class="flex items-center justify-between text-xs pt-1">
            <span class="text-neu-text font-medium">Elevated Risk</span>
            <span class="font-bold text-rose-600">{{ workforceStats?.high_risk_count || 142 }} (14%)</span>
          </div>
          <div class="w-full h-1.5 bg-neu-base rounded-full overflow-hidden">
            <div class="h-full bg-rose-500 rounded-full" style="width: 14%"></div>
          </div>
        </div>
      </NeumorphicCard>

      <!-- 2. Department Risk -->
      <NeumorphicCard class="p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-neu-muted mb-3">Department Risk</h3>
        <div class="space-y-2.5">
          <div
            v-for="dept in (departmentRisks.length ? departmentRisks.slice(0, 3) : [
              { name: 'Engineering', high_risk_count: 24 },
              { name: 'Sales', high_risk_count: 45 },
              { name: 'Marketing', high_risk_count: 18 },
            ])"
            :key="(dept as any).name || (dept as any).department"
            class="flex items-center justify-between text-xs"
          >
            <span class="font-medium text-neu-text">{{ (dept as any).name || (dept as any).department }}</span>
            <span class="font-semibold text-rose-600">{{ dept.high_risk_count }} at risk</span>
          </div>
        </div>
        <div class="mt-4 pt-3 border-t border-neu-border/30">
          <button
            @click="router.push('/workforce')"
            type="button"
            class="text-xs font-semibold text-neu-primary hover:underline focus:outline-none"
          >
            View all departments &rarr;
          </button>
        </div>
      </NeumorphicCard>

      <!-- 3. Recent Alerts -->
      <NeumorphicCard class="p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-neu-muted mb-3">Recent Alerts</h3>
        <div class="space-y-2">
          <div
            v-for="alert in [
              { id: 1, title: 'Payroll Overtime Spike', dept: 'Engineering', path: '/payrolls' },
              { id: 2, title: 'Check-in Pattern Anomaly', dept: 'Operations', path: '/attendance' },
              { id: 3, title: 'Turnover Signal', dept: 'Sales', path: '/workforce' },
            ]"
            :key="alert.id"
            class="flex items-center justify-between text-xs py-1 cursor-pointer hover:opacity-80"
            @click="router.push(alert.path)"
          >
            <span class="font-medium text-neu-text truncate">{{ alert.title }}</span>
            <span class="text-[11px] text-neu-muted flex-shrink-0 ml-2">{{ alert.dept }}</span>
          </div>
        </div>
      </NeumorphicCard>
    </div>
  </div>
</template>
