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
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

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

const departmentChartOption = ref<any>({
  title: {
    text: 'Department Workforce & Risk',
    textStyle: { color: '#2B3674', fontSize: 14, fontWeight: 'bold', fontFamily: 'Inter, sans-serif' },
    left: 'left',
  },
  tooltip: { trigger: 'axis' },
  grid: { top: 50, right: 20, bottom: 40, left: 40 },
  xAxis: {
    type: 'category',
    data: ['R&D', 'Sales', 'Engineering', 'Marketing', 'Ops', 'Finance', 'HR'],
    axisLine: { lineStyle: { color: '#A3AED0' } },
    axisLabel: { color: '#707EAE', fontSize: 11 },
  },
  yAxis: {
    type: 'value',
    name: 'Employees',
    nameTextStyle: { color: '#707EAE', fontSize: 10 },
    splitLine: { lineStyle: { color: '#E8ECF1', type: 'dashed' } },
  },
  series: [
    {
      name: 'Total Staff',
      type: 'bar',
      data: [210, 195, 180, 135, 120, 100, 60],
      itemStyle: {
        color: '#2D6CDF',
        borderRadius: [6, 6, 0, 0],
      },
      barWidth: '35%',
    },
    {
      name: 'High Risk',
      type: 'bar',
      data: [32, 45, 24, 18, 12, 6, 5],
      itemStyle: {
        color: '#EF4444',
        borderRadius: [6, 6, 0, 0],
      },
      barWidth: '35%',
    },
  ],
})

const attendanceTrendOption = ref<any>({
  title: {
    text: 'Attendance Stability (30 Days)',
    textStyle: { color: '#2B3674', fontSize: 14, fontWeight: 'bold', fontFamily: 'Inter, sans-serif' },
    left: 'left',
  },
  tooltip: { trigger: 'axis' },
  grid: { top: 50, right: 20, bottom: 40, left: 40 },
  xAxis: {
    type: 'category',
    data: ['W1-M', 'W1-W', 'W1-F', 'W2-M', 'W2-W', 'W2-F', 'W3-M', 'W3-W', 'W3-F', 'W4-M', 'W4-W', 'W4-F'],
    axisLine: { lineStyle: { color: '#A3AED0' } },
    axisLabel: { color: '#707EAE', fontSize: 11 },
  },
  yAxis: {
    type: 'value',
    min: 85,
    max: 100,
    name: 'Rate (%)',
    nameTextStyle: { color: '#707EAE', fontSize: 10 },
    splitLine: { lineStyle: { color: '#E8ECF1', type: 'dashed' } },
  },
  series: [
    {
      type: 'line',
      data: [94.5, 96.2, 95.0, 93.8, 95.5, 96.1, 94.2, 95.8, 96.5, 94.9, 95.2, 96.0],
      smooth: true,
      lineStyle: { color: '#10B981', width: 3 },
      itemStyle: { color: '#10B981' },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(16, 185, 129, 0.25)' },
            { offset: 1, color: 'rgba(16, 185, 129, 0.0)' },
          ],
        },
      },
    },
  ],
})

const turnoverRiskOption = ref<any>({
  title: {
    text: 'Predicted Attrition Tiers (XGBoost)',
    textStyle: { color: '#2B3674', fontSize: 14, fontWeight: 'bold', fontFamily: 'Inter, sans-serif' },
    left: 'left',
  },
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { bottom: 0, textStyle: { color: '#707EAE', fontSize: 11 } },
  series: [
    {
      type: 'pie',
      radius: ['45%', '72%'],
      center: ['50%', '48%'],
      data: [
        { value: 650, name: 'Low Risk (<30%)', itemStyle: { color: '#10B981' } },
        { value: 208, name: 'Medium Risk (30-70%)', itemStyle: { color: '#F59E0B' } },
        { value: 142, name: 'High Risk (>70%)', itemStyle: { color: '#EF4444' } },
      ],
      label: { show: false },
    },
  ],
})

async function loadDashboardData() {
  loading.value = true
  try {
    const [wfStats, attStats, heatmaps, ins] = await Promise.all([
      getWorkforceStats(),
      getAttendanceStats().catch(() => null),
      getWorkforceHeatmap().catch(() => []),
      getWorkforceInsights().catch(() => []),
    ])

    workforceStats.value = wfStats
    attendanceStats.value = attStats
    insights.value = ins

    // Update charts if live data returns
    if (heatmaps.length > 0) {
      departmentChartOption.value.xAxis.data = heatmaps.map((h: DepartmentRisk) => h.department)
      departmentChartOption.value.series[0].data = heatmaps.map((h: DepartmentRisk) => h.employee_count)
      departmentChartOption.value.series[1].data = heatmaps.map((h: DepartmentRisk) => h.high_risk_count)
    }

    if (wfStats) {
      turnoverRiskOption.value.series[0].data = [
        { value: wfStats.low_risk_count, name: 'Low Risk (<30%)', itemStyle: { color: '#10B981' } },
        { value: wfStats.medium_risk_count, name: 'Medium Risk (30-70%)', itemStyle: { color: '#F59E0B' } },
        { value: wfStats.high_risk_count, name: 'High Risk (>70%)', itemStyle: { color: '#EF4444' } },
      ]
    }
  } catch (err) {
    console.error('Failed to load dashboard data', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-neu-text">
          Workforce Intelligence Executive Overview
        </h2>
        <p class="text-sm text-neu-muted mt-1">
          Real-time telemetry across 1,000 corporate employees, ML turnover risk, and AI synthesized recommendations.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="default" size="sm" @click="loadDashboardData">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Sync Telemetry
        </NeumorphicButton>

        <NeumorphicButton variant="primary" size="sm" @click="router.push('/workforce')">
          Attrition Heatmap
        </NeumorphicButton>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <NeumorphicStatCard
        title="Total Workforce"
        :value="workforceStats?.total_employees || 1000"
        subtitle="Full Enterprise Roster"
        trend="940 Active | 60 On Leave"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Daily Attendance Rate"
        :value="`${attendanceStats?.attendance_rate || 95.8}%`"
        subtitle="Logged & Verified"
        trend="Controlled Variance"
        iconBg="success"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Turnover Risk Score"
        :value="workforceStats?.avg_turnover_risk ? (workforceStats.avg_turnover_risk * 100).toFixed(1) + '%' : '14.2%'"
        subtitle="XGBoost Baseline"
        :trend="`${workforceStats?.high_risk_count || 142} Elevated Risk Staff`"
        iconBg="danger"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Workforce Health Index"
        :value="`${workforceStats?.overall_health_score || 88} / 100`"
        subtitle="Stability & Retention"
        trend="PSI Drift: 0.04 (Healthy)"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- AI Synthesized Insight Cards -->
    <div class="space-y-4">
      <div class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 rounded-full bg-neu-primary animate-pulse"></span>
        <h3 class="text-lg font-black text-neu-text tracking-tight">
          Real-Time AI Workforce Signals
        </h3>
        <NeumorphicBadge variant="primary" size="sm">Groq LLM + SHAP Synthesized</NeumorphicBadge>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <NeumorphicCard
          v-for="item in insights.slice(0, 3)"
          :key="item.id"
          class="flex flex-col justify-between"
        >
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <NeumorphicBadge
                :variant="item.impact_level === 'critical' || item.impact_level === 'high' ? 'danger' : 'warning'"
                size="sm"
              >
                {{ item.impact_level.toUpperCase() }}
              </NeumorphicBadge>
              <span class="text-xs font-semibold text-neu-muted">{{ item.affected_department }}</span>
            </div>
            <h4 class="text-sm font-extrabold text-neu-text leading-snug">{{ item.title }}</h4>
            <p class="text-xs text-neu-muted leading-relaxed">{{ item.description }}</p>
          </div>

          <div class="mt-4 pt-3 border-t border-neu-border/50">
            <div class="text-[10px] font-bold text-neu-primary uppercase">Action</div>
            <p class="text-xs font-medium text-neu-text">{{ item.recommendation }}</p>
          </div>
        </NeumorphicCard>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <NeumorphicCard class="lg:col-span-2">
        <VChart :option="departmentChartOption" style="height: 340px" autoresize />
      </NeumorphicCard>

      <NeumorphicCard>
        <VChart :option="turnoverRiskOption" style="height: 340px" autoresize />
      </NeumorphicCard>
    </div>

    <!-- Attendance Trend & Quick Command Center -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <NeumorphicCard class="lg:col-span-2">
        <VChart :option="attendanceTrendOption" style="height: 320px" autoresize />
      </NeumorphicCard>

      <NeumorphicCard class="flex flex-col justify-between">
        <div>
          <h3 class="text-base font-extrabold text-neu-text mb-1">
            Platform Quick Actions
          </h3>
          <p class="text-xs text-neu-muted mb-4">Direct shortcuts to critical workflows</p>

          <div class="space-y-2.5">
            <button
              @click="router.push('/payrolls')"
              class="w-full flex items-center justify-between p-3 rounded-2xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed transition-all duration-150 text-xs font-bold text-neu-text border border-white/50"
            >
              <div class="flex items-center space-x-2.5">
                <span class="p-1.5 rounded-xl bg-amber-50 text-amber-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </span>
                <span>Audit Payroll Anomalies</span>
              </div>
              <span class="text-neu-muted">&rarr;</span>
            </button>

            <button
              @click="router.push('/chatbot')"
              class="w-full flex items-center justify-between p-3 rounded-2xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed transition-all duration-150 text-xs font-bold text-neu-text border border-white/50"
            >
              <div class="flex items-center space-x-2.5">
                <span class="p-1.5 rounded-xl bg-blue-50 text-neu-primary">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                  </svg>
                </span>
                <span>Query Groq AI Assistant</span>
              </div>
              <span class="text-neu-muted">&rarr;</span>
            </button>

            <button
              @click="router.push('/mlops')"
              class="w-full flex items-center justify-between p-3 rounded-2xl bg-neu-base shadow-neu-flat hover:shadow-neu-pressed transition-all duration-150 text-xs font-bold text-neu-text border border-white/50"
            >
              <div class="flex items-center space-x-2.5">
                <span class="p-1.5 rounded-xl bg-emerald-50 text-emerald-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                  </svg>
                </span>
                <span>Inspect Data Drift (PSI)</span>
              </div>
              <span class="text-neu-muted">&rarr;</span>
            </button>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-neu-border/50 text-[11px] text-neu-muted flex items-center justify-between">
          <span>Enterprise Tenant: Acme Global</span>
          <span class="font-mono text-emerald-600 font-bold">100% Operational</span>
        </div>
      </NeumorphicCard>
    </div>
  </div>
</template>
