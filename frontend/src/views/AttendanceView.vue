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
import StatCard from '@/components/StatCard.vue'
import { useAttendanceStore } from '@/stores/attendance'

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

const store = useAttendanceStore()

const dateFrom = ref('')
const dateTo = ref('')

const attendanceChartOption = ref({
  title: { text: 'Daily Attendance Rate', left: 'center' },
  tooltip: { trigger: 'axis' },
  legend: { bottom: 0, data: ['Attendance Rate', 'Anomalies'] },
  xAxis: {
    type: 'category',
    data: Array.from({ length: 30 }, (_, i) => {
      const d = new Date()
      d.setDate(d.getDate() - 29 + i)
      return d.toISOString().slice(5, 10)
    }),
  },
  yAxis: { type: 'value', name: 'Rate (%)', min: 70, max: 100 },
  series: [
    {
      name: 'Attendance Rate',
      type: 'line',
      smooth: true,
      data: Array.from({ length: 30 }, () => Math.round(88 + Math.random() * 10)),
      itemStyle: { color: '#4F46E5' },
      areaStyle: { color: 'rgba(79, 70, 229, 0.1)' },
      markPoint: {
        data: [
          { type: 'min', name: 'Min' },
          { type: 'max', name: 'Max' },
        ],
      },
    },
    {
      name: 'Anomalies',
      type: 'scatter',
      data: [
        [3, 82],
        [12, 78],
        [21, 80],
      ],
      itemStyle: { color: '#EF4444' },
      symbolSize: 12,
    },
  ],
})

function getStatusBadgeClass(status: string): string {
  switch (status) {
    case 'present':
      return 'bg-green-100 text-green-800'
    case 'absent':
      return 'bg-red-100 text-red-800'
    case 'late':
      return 'bg-yellow-100 text-yellow-800'
    case 'half_day':
      return 'bg-blue-100 text-blue-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

async function applyFilter() {
  await store.fetchRecords({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  })
  await store.fetchStats({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
  })
}

provide('THEME_KEY', 'light')

onMounted(async () => {
  await store.fetchRecords()
  await store.fetchStats()
  await store.fetchAnomalies()
})
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Attendance Analytics</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard
        title="Total Employees"
        :value="store.stats?.total_employees ?? '-'"
      >
        <template #icon>
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </template>
      </StatCard>
      <StatCard
        title="Present Today"
        :value="store.stats?.present_today ?? '-'"
      >
        <template #icon>
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </StatCard>
      <StatCard
        title="Attendance Rate"
        :value="store.stats ? `${store.stats.attendance_rate}%` : '-'"
      >
        <template #icon>
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </template>
      </StatCard>
      <StatCard
        title="Anomalies Detected"
        :value="store.stats?.anomaly_count ?? '-'"
      >
        <template #icon>
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </template>
      </StatCard>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input
            v-model="dateFrom"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          />
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input
            v-model="dateTo"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          />
        </div>
        <button
          @click="applyFilter"
          class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
        >
          Apply
        </button>
      </div>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-lg shadow p-4">
      <VChart :option="attendanceChartOption" style="height: 400px" autoresize />
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Attendance Records</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anomaly</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="store.loading">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">Loading...</td>
            </tr>
            <tr v-else-if="store.records.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">No records found</td>
            </tr>
            <tr
              v-else
              v-for="record in store.records"
              :key="record.id"
              :class="{ 'bg-red-50': record.is_anomaly }"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.employee_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.date }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.check_in ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.check_out ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.hours_worked ?? '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusBadgeClass(record.status),
                  ]"
                >
                  {{ record.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span v-if="record.is_anomaly" class="text-red-600 font-medium">
                  {{ record.anomaly_reason }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
