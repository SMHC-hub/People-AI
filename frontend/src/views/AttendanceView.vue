<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, ScatterChart } from 'echarts/charts'
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  MarkPointComponent,
} from 'echarts/components'
import VChart from 'vue-echarts'
import { useAttendanceStore } from '@/stores/attendance'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

use([
  CanvasRenderer,
  LineChart,
  ScatterChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  MarkPointComponent,
])

provide('THEME_KEY', 'light')

const store = useAttendanceStore()
const dateFrom = ref('')
const dateTo = ref('')
const filterAnomalyOnly = ref(false)

const attendanceChartOption = ref<any>({
  title: {
    text: 'Attendance Rate Telemetry & Anomaly Outliers',
    textStyle: { color: '#2B3674', fontSize: 14, fontWeight: 'bold', fontFamily: 'Inter, sans-serif' },
    left: 'left',
  },
  tooltip: { trigger: 'axis' },
  grid: { top: 50, right: 20, bottom: 40, left: 40 },
  xAxis: {
    type: 'category',
    data: Array.from({ length: 30 }, (_, i) => {
      const d = new Date()
      d.setDate(d.getDate() - 29 + i)
      return d.toISOString().slice(5, 10)
    }),
    axisLine: { lineStyle: { color: '#A3AED0' } },
    axisLabel: { color: '#707EAE', fontSize: 11 },
  },
  yAxis: {
    type: 'value',
    min: 75,
    max: 100,
    name: 'Rate (%)',
    nameTextStyle: { color: '#707EAE', fontSize: 10 },
    splitLine: { lineStyle: { color: '#E8ECF1', type: 'dashed' } },
  },
  series: [
    {
      name: 'Attendance Rate',
      type: 'line',
      smooth: true,
      data: [94, 96, 95, 93, 95, 96, 94, 95, 96, 94, 95, 96, 95, 93, 94, 96, 95, 94, 95, 96, 93, 94, 96, 95, 94, 96, 95, 94, 95, 96],
      itemStyle: { color: '#2D6CDF' },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(45, 108, 223, 0.25)' },
            { offset: 1, color: 'rgba(45, 108, 223, 0.0)' },
          ],
        },
      },
    },
    {
      name: 'Outlier Alerts',
      type: 'scatter',
      data: [
        [4, 86],
        [14, 82],
        [22, 84],
      ],
      itemStyle: { color: '#EF4444' },
      symbolSize: 10,
    },
  ],
})

function getStatusBadge(status: string): 'success' | 'warning' | 'danger' | 'info' | 'neutral' {
  switch (status) {
    case 'present':
      return 'success'
    case 'late':
      return 'warning'
    case 'absent':
      return 'danger'
    case 'half_day':
      return 'info'
    default:
      return 'neutral'
  }
}

async function applyFilter() {
  await store.fetchRecords({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  })
}

onMounted(async () => {
  await store.fetchRecords()
  await store.fetchStats()
  await store.fetchAnomalies()
})
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <h2 class="text-2xl font-black tracking-tight text-neu-text">
            Attendance Analytics & Isolation Forest Engine
          </h2>
          <NeumorphicBadge variant="primary" size="sm">Unsupervised Outlier Detection</NeumorphicBadge>
        </div>
        <p class="text-sm text-neu-muted mt-1">
          Daily timesheet verification, multi-dimensional check-in clustering, and automated absence alerts.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton variant="default" size="sm" @click="applyFilter">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Re-sync Records
        </NeumorphicButton>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <NeumorphicStatCard
        title="Total Workforce"
        :value="store.stats?.total_employees || 1000"
        subtitle="Roster Size"
        trend="Continuous Monitoring"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Present Today"
        :value="store.stats?.present_today || 945"
        subtitle="Checked in via kiosk / app"
        trend="On-schedule arrivals"
        iconBg="success"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Attendance Rate"
        :value="store.stats ? `${store.stats.attendance_rate}%` : '96.2%'"
        subtitle="Monthly Average"
        trend="Within SLA bounds"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Anomalies Flagged"
        :value="store.stats?.anomaly_count || 32"
        subtitle="Isolation Forest Outliers"
        trend="Unusual Check-in Variance"
        iconBg="danger"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- Chart Card -->
    <NeumorphicCard>
      <VChart :option="attendanceChartOption" style="height: 340px" autoresize />
    </NeumorphicCard>

    <!-- Records Table Card -->
    <NeumorphicCard>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-neu-border/50">
        <div>
          <h3 class="text-base font-extrabold text-neu-text">Timesheet & Anomaly Queue</h3>
          <p class="text-xs text-neu-muted">Real-time attendance events with outlier detection.</p>
        </div>

        <div class="flex items-center space-x-3">
          <button
            @click="filterAnomalyOnly = !filterAnomalyOnly"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200"
            :class="filterAnomalyOnly ? 'bg-rose-500 text-white shadow-neu-pressed' : 'bg-neu-base text-neu-muted shadow-neu-flat hover:text-neu-text'"
          >
            Anomalies Only
          </button>
        </div>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Employee</th>
              <th class="py-3.5 px-4">Date</th>
              <th class="py-3.5 px-4">Check In</th>
              <th class="py-3.5 px-4">Check Out</th>
              <th class="py-3.5 px-4 text-center">Hours Worked</th>
              <th class="py-3.5 px-4 text-center">Status</th>
              <th class="py-3.5 px-4 text-right">Anomaly Reason</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="record in (filterAnomalyOnly ? store.records.filter(r => r.is_anomaly) : store.records)"
              :key="record.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
              :class="{ 'bg-rose-50/20': record.is_anomaly }"
            >
              <td class="py-3.5 px-4 font-extrabold text-neu-text">
                {{ record.employee_name }}
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-neu-muted">
                {{ record.date }}
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-neu-text">
                {{ record.check_in || '--:--' }}
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-neu-text">
                {{ record.check_out || '--:--' }}
              </td>
              <td class="py-3.5 px-4 text-center font-mono font-bold text-xs">
                {{ record.hours_worked ? `${record.hours_worked} hrs` : '-' }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge :variant="getStatusBadge(record.status)" size="sm">
                  {{ record.status.toUpperCase() }}
                </NeumorphicBadge>
              </td>
              <td class="py-3.5 px-4 text-right">
                <span v-if="record.is_anomaly" class="text-xs font-bold text-rose-600">
                  {{ record.anomaly_reason || 'Unusual departure pattern' }}
                </span>
                <span v-else class="text-xs text-neu-muted font-mono">Normal</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>
  </div>
</template>
