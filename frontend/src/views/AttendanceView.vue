<script setup lang="ts">
import { ref, computed, onMounted, provide } from 'vue'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, ScatterChart } from 'echarts/charts'
import {
  TooltipComponent,
  GridComponent,
} from 'echarts/components'
import VChart from 'vue-echarts'
import { useAttendanceStore } from '@/stores/attendance'
import type { Attendance } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import {
  formatDate,
  formatTime,
  formatDuration,
  formatStatus,
  humanizeAnomalyReason,
} from '@/utils/formatters'

use([
  CanvasRenderer,
  LineChart,
  ScatterChart,
  TooltipComponent,
  GridComponent,
])

provide('THEME_KEY', 'light')

const store = useAttendanceStore()

// Clean initial date range (matches actual active attendance period)
const dateFrom = ref('2026-09-01')
const dateTo = ref('2026-09-11')
const filterAnomalyOnly = ref(false)

// Row detail modal
const selectedRecord = ref<AttendanceRow | null>(null)
const showAdvancedInDetail = ref(false)

interface AttendanceRow {
  id: number
  employeeId: number
  employeeName: string
  employeeDept: string
  employeeCode: string
  dateFormatted: string
  rawDate: string
  checkInFormatted: string
  checkOutFormatted: string
  durationFormatted: string
  status: string
  activity: 'Anomaly' | 'Normal'
  isAnomaly: boolean
  anomalyReason: string
  anomalyScore?: number
  rawRecord: Attendance
}

// Map backend API data to clean presentation layer
const attendanceRows = computed<AttendanceRow[]>(() => {
  return store.records.map(rec => {
    // Resolve employee name and details from relations or fallbacks
    const emp = rec.employee
    const resolvedName =
      emp?.full_name ||
      (emp?.first_name ? `${emp.first_name} ${emp.last_name || ''}`.trim() : null) ||
      rec.employee_name ||
      (rec.employee_id ? `Employee #${rec.employee_id}` : 'Employee')

    const resolvedDept = emp?.department?.name || ''
    const resolvedCode = emp?.employee_code || (rec.employee_id ? `EMP-${rec.employee_id}` : '')

    return {
      id: rec.id,
      employeeId: rec.employee_id,
      employeeName: resolvedName,
      employeeDept: resolvedDept,
      employeeCode: resolvedCode,
      dateFormatted: formatDate(rec.date),
      rawDate: rec.date,
      checkInFormatted: formatTime(rec.check_in),
      checkOutFormatted: formatTime(rec.check_out),
      durationFormatted: formatDuration(rec.hours_worked, rec.check_in, rec.check_out),
      status: formatStatus(rec.status),
      activity: rec.is_anomaly ? 'Anomaly' : 'Normal',
      isAnomaly: Boolean(rec.is_anomaly),
      anomalyReason: humanizeAnomalyReason(rec.anomaly_reason, rec.status, rec.hours_worked),
      anomalyScore: rec.anomaly_score !== undefined ? Number(rec.anomaly_score) : undefined,
      rawRecord: rec,
    }
  })
})

// 30-Day Attendance Trend chart data
const chartDates = computed(() => {
  if (store.stats?.daily_trend && store.stats.daily_trend.length > 0) {
    return store.stats.daily_trend.map(t => formatDate(t.date).replace(/ \d{4}$/, ''))
  }
  return Array.from({ length: 30 }, (_, i) => {
    const d = new Date(2026, 8, 11)
    d.setDate(d.getDate() - 29 + i)
    return formatDate(d).replace(/ \d{4}$/, '')
  })
})

const chartRates = computed(() => {
  if (store.stats?.daily_trend && store.stats.daily_trend.length > 0) {
    return store.stats.daily_trend.map(t => t.attendance_rate)
  }
  return [94, 95, 96, 94, 95, 96, 93, 95, 96, 94, 95, 96, 94, 93, 95, 96, 95, 94, 95, 96, 93, 94, 96, 95, 94, 96, 95, 94, 95, 96]
})

const chartScatterAnomalies = computed(() => {
  if (store.stats?.daily_trend && store.stats.daily_trend.length > 0) {
    const points: [number, number][] = []
    store.stats.daily_trend.forEach((t, idx) => {
      if (t.anomaly_count > 0) {
        points.push([idx, t.attendance_rate])
      }
    })
    return points
  }
  return [
    [4, 95],
    [14, 93],
    [22, 94],
  ]
})

const attendanceChartOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    backgroundColor: 'rgba(255, 255, 255, 0.96)',
    borderColor: '#D8DFE8',
    borderWidth: 1,
    padding: [8, 12],
    textStyle: { color: '#2E3440', fontSize: 12 },
    formatter: (params: any) => {
      if (!Array.isArray(params) || params.length === 0) return ''
      const index = params[0].dataIndex
      const dateLabel = chartDates.value[index] || params[0].name
      const rate = chartRates.value[index] ?? params[0].value

      let anomalyCount = 0
      if (store.stats?.daily_trend && store.stats.daily_trend[index]) {
        anomalyCount = store.stats.daily_trend[index].anomaly_count
      } else {
        const found = chartScatterAnomalies.value.find(p => p[0] === index)
        if (found) anomalyCount = 2
      }

      return `
        <div style="font-family: inherit; font-size: 11px; line-height: 1.4;">
          <div style="font-weight: 700; color: #2E3440; margin-bottom: 4px;">${dateLabel}</div>
          <div style="display: flex; justify-content: space-between; gap: 16px; color: #64748B;">
            <span>Attendance rate:</span>
            <strong style="color: #2D6CDF;">${rate}%</strong>
          </div>
          <div style="display: flex; justify-content: space-between; gap: 16px; color: #64748B; margin-top: 2px;">
            <span>Anomaly count:</span>
            <strong style="color: ${anomalyCount > 0 ? '#EF4444' : '#8A94A6'};">${anomalyCount}</strong>
          </div>
        </div>
      `
    },
  },
  grid: {
    top: 20,
    right: 16,
    bottom: 24,
    left: 54,
  },
  xAxis: {
    type: 'category',
    data: chartDates.value,
    axisLine: { lineStyle: { color: '#D8DFE8' } },
    axisTick: { show: false },
    axisLabel: {
      color: '#8A94A6',
      fontSize: 10,
      interval: 4,
    },
  },
  yAxis: {
    type: 'value',
    min: 80,
    max: 100,
    splitLine: { lineStyle: { color: '#EAEEF2', type: 'dashed' } },
    axisLabel: {
      color: '#8A94A6',
      fontSize: 10,
      formatter: '{value}%',
    },
  },
  series: [
    {
      name: 'Attendance Rate',
      type: 'line',
      smooth: true,
      symbol: 'none',
      data: chartRates.value,
      itemStyle: { color: '#2D6CDF' },
      lineStyle: { width: 2, color: '#2D6CDF' },
      areaStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 0,
          y2: 1,
          colorStops: [
            { offset: 0, color: 'rgba(45, 108, 223, 0.16)' },
            { offset: 1, color: 'rgba(45, 108, 223, 0.0)' },
          ],
        },
      },
    },
    {
      name: 'Anomalies',
      type: 'scatter',
      data: chartScatterAnomalies.value,
      itemStyle: { color: '#EF4444' },
      symbolSize: 6,
    },
  ],
}))

async function applyFilter() {
  await store.fetchRecords({
    page: 1,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    anomalies_only: filterAnomalyOnly.value ? true : undefined,
  })
}

async function changePage(page: number) {
  if (page < 1 || page > store.totalPages) return
  await store.fetchRecords({
    page,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    anomalies_only: filterAnomalyOnly.value ? true : undefined,
  })
}

function resetDateFilter() {
  dateFrom.value = ''
  dateTo.value = ''
  applyFilter()
}

function openRowDetail(row: AttendanceRow) {
  selectedRecord.value = row
  showAdvancedInDetail.value = false
}

function closeRowDetail() {
  selectedRecord.value = null
}

async function loadData() {
  await Promise.all([
    store.fetchStats({
      date_from: '2026-08-14',
      date_to: '2026-09-11',
    }),
    store.fetchRecords({
      page: 1,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      anomalies_only: filterAnomalyOnly.value ? true : undefined,
    }),
  ])
}

onMounted(loadData)
</script>

<template>
  <div class="space-y-4 pb-10">
    <!-- Header -->
    <PageHeader
      title="Attendance"
      subtitle="Monitor attendance and unusual activity."
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

    <!-- Compact Filter Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-2.5 px-4 rounded-2xl bg-neu-surface shadow-neu-flat-sm border border-neu-border/20">
      <div class="flex flex-wrap items-center gap-2 text-xs">
        <!-- Start Date Control -->
        <div class="relative flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-neu-base shadow-neu-inset group">
          <span class="text-[11px] font-medium text-neu-muted whitespace-nowrap">Start date</span>
          <span class="text-xs font-semibold text-neu-text tracking-tight min-w-[76px]">
            {{ dateFrom ? formatDate(dateFrom) : 'Select' }}
          </span>
          <svg class="w-3.5 h-3.5 text-neu-muted group-hover:text-neu-primary transition-colors pointer-events-none ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <input
            type="date"
            v-model="dateFrom"
            @change="applyFilter"
            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
            title="Select start date"
          />
        </div>

        <span class="text-xs text-neu-muted font-medium px-0.5">→</span>

        <!-- End Date Control -->
        <div class="relative flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-neu-base shadow-neu-inset group">
          <span class="text-[11px] font-medium text-neu-muted whitespace-nowrap">End date</span>
          <span class="text-xs font-semibold text-neu-text tracking-tight min-w-[76px]">
            {{ dateTo ? formatDate(dateTo) : 'Select' }}
          </span>
          <svg class="w-3.5 h-3.5 text-neu-muted group-hover:text-neu-primary transition-colors pointer-events-none ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <input
            type="date"
            v-model="dateTo"
            @change="applyFilter"
            class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
            title="Select end date"
          />
        </div>

        <!-- Reset Button if filtered -->
        <button
          v-if="dateFrom || dateTo"
          @click="resetDateFilter"
          type="button"
          title="Reset date range"
          class="p-1.5 rounded-lg text-neu-muted hover:text-neu-text hover:bg-neu-base transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Anomalies Checkbox -->
      <label class="flex items-center space-x-2 text-xs font-medium text-neu-text cursor-pointer select-none px-2 py-1 rounded-lg hover:bg-neu-base/40 transition-colors">
        <input
          type="checkbox"
          v-model="filterAnomalyOnly"
          @change="applyFilter"
          class="w-3.5 h-3.5 rounded border-neu-border text-neu-primary focus:ring-0 cursor-pointer"
        />
        <span :class="filterAnomalyOnly ? 'text-neu-primary font-semibold' : 'text-neu-text'">Anomalies only</span>
      </label>
    </div>

    <!-- Attendance Trend Chart -->
    <NeumorphicCard class="p-4 sm:p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-bold text-neu-text tracking-tight">Attendance Trend</h2>
          <p class="text-xs text-neu-muted mt-0.5">Last 30 days</p>
        </div>
      </div>
      <VChart :option="attendanceChartOption" style="height: 200px" autoresize />
    </NeumorphicCard>

    <!-- Attendance Records Table -->
    <NeumorphicCard class="p-0 overflow-hidden">
      <div class="px-5 py-3.5 border-b border-neu-border/30 flex items-center justify-between bg-neu-surface/50">
        <div>
          <h2 class="text-sm font-bold text-neu-text">Attendance Records</h2>
          <span v-if="store.totalCount > 0" class="text-[11px] text-neu-muted">
            {{ store.totalCount.toLocaleString() }} total records
          </span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="store.loading" class="p-5">
        <LoadingSkeleton type="table" :rows="6" />
      </div>

      <!-- Error State -->
      <div v-else-if="store.error" class="p-10 text-center space-y-3">
        <div class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center mx-auto">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <p class="text-xs font-semibold text-neu-text">Couldn't load attendance records.</p>
        <button
          @click="loadData"
          type="button"
          class="btn-primary px-4 py-2 text-xs font-semibold focus:outline-none"
        >
          Try again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="attendanceRows.length === 0" class="p-12 text-center space-y-1">
        <div class="w-10 h-10 rounded-2xl bg-neu-base text-neu-muted flex items-center justify-center mx-auto mb-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <p class="text-sm font-semibold text-neu-text">No attendance records</p>
        <p class="text-xs text-neu-muted">Try changing the date range.</p>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/30 bg-neu-surface/40">
              <th class="py-3 px-5">Employee</th>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4 text-center hidden md:table-cell">Check In</th>
              <th class="py-3 px-4 text-center hidden md:table-cell">Check Out</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-4 text-center">Activity</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/25">
            <tr
              v-for="row in attendanceRows"
              :key="row.id"
              @click="openRowDetail(row)"
              class="hover:bg-neu-base/40 transition-colors cursor-pointer group"
            >
              <!-- Employee (Full Name + Dept/Code) -->
              <td class="py-3 px-5">
                <div class="flex items-center space-x-2.5">
                  <div class="w-7 h-7 rounded-full bg-neu-primary/10 text-neu-primary font-bold text-xs flex items-center justify-center shrink-0">
                    {{ row.employeeName.charAt(0) }}
                  </div>
                  <div>
                    <span class="font-semibold text-neu-text group-hover:text-neu-primary transition-colors block">
                      {{ row.employeeName }}
                    </span>
                    <span v-if="row.employeeDept || row.employeeCode" class="text-[11px] text-neu-muted block font-normal leading-tight">
                      {{ row.employeeDept || row.employeeCode }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Date -->
              <td class="py-3 px-4 text-neu-text font-medium whitespace-nowrap">
                {{ row.dateFormatted }}
              </td>

              <!-- Check In -->
              <td class="py-3 px-4 text-center text-neu-text font-mono hidden md:table-cell">
                {{ row.checkInFormatted }}
              </td>

              <!-- Check Out -->
              <td class="py-3 px-4 text-center text-neu-text font-mono hidden md:table-cell">
                {{ row.checkOutFormatted }}
              </td>

              <!-- Status -->
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="row.status" size="sm" />
              </td>

              <!-- Activity -->
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <span
                  v-if="row.isAnomaly"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-500/10 text-rose-700 border border-rose-200/40"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                  Anomaly
                </span>
                <span v-else class="inline-flex items-center text-[11px] text-neu-muted font-medium">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500/70 mr-1.5"></span>
                  Normal
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Compact Pagination Footer -->
        <div
          v-if="store.totalPages > 1"
          class="p-3 px-5 border-t border-neu-border/30 flex items-center justify-between text-xs text-neu-muted bg-neu-surface/30"
        >
          <span>
            Page {{ store.currentPage }} of {{ store.totalPages }}
          </span>
          <div class="flex items-center space-x-2">
            <button
              :disabled="store.currentPage <= 1"
              @click="changePage(store.currentPage - 1)"
              class="px-3 py-1 rounded-lg bg-neu-surface shadow-neu-flat-xs disabled:opacity-40 disabled:cursor-not-allowed hover:text-neu-text font-medium transition-all"
            >
              Previous
            </button>
            <button
              :disabled="store.currentPage >= store.totalPages"
              @click="changePage(store.currentPage + 1)"
              class="px-3 py-1 rounded-lg bg-neu-surface shadow-neu-flat-xs disabled:opacity-40 disabled:cursor-not-allowed hover:text-neu-text font-medium transition-all"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Attendance Detail Modal -->
    <div
      v-if="selectedRecord"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
      @click.self="closeRowDetail"
    >
      <NeumorphicCard class="max-w-md w-full p-6 space-y-5 shadow-neu-flat-lg animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/30">
          <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-full bg-neu-primary/10 text-neu-primary font-bold text-sm flex items-center justify-center">
              {{ selectedRecord.employeeName.charAt(0) }}
            </div>
            <div>
              <h3 class="text-sm font-bold text-neu-text">{{ selectedRecord.employeeName }}</h3>
              <p class="text-[11px] text-neu-muted">
                {{ selectedRecord.employeeDept }}
                <span v-if="selectedRecord.employeeDept && selectedRecord.employeeCode">&bull;</span>
                {{ selectedRecord.employeeCode }}
              </p>
            </div>
          </div>
          <button
            @click="closeRowDetail"
            class="text-neu-muted hover:text-neu-text text-lg font-bold p-1 leading-none"
          >
            &times;
          </button>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Date</span>
            <span class="font-semibold text-neu-text">{{ selectedRecord.dateFormatted }}</span>
          </div>
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Duration</span>
            <span class="font-semibold text-neu-text">{{ selectedRecord.durationFormatted }}</span>
          </div>
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Check In</span>
            <span class="font-semibold font-mono text-neu-text">{{ selectedRecord.checkInFormatted }}</span>
          </div>
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Check Out</span>
            <span class="font-semibold font-mono text-neu-text">{{ selectedRecord.checkOutFormatted }}</span>
          </div>
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Status</span>
            <StatusBadge :status="selectedRecord.status" size="sm" />
          </div>
          <div class="p-3 rounded-xl bg-neu-base/60 space-y-1">
            <span class="text-[11px] text-neu-muted block">Activity</span>
            <span
              v-if="selectedRecord.isAnomaly"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-500/10 text-rose-700"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
              Anomaly
            </span>
            <span v-else class="inline-flex items-center text-[11px] text-neu-muted font-medium">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500/70 mr-1.5"></span>
              Normal
            </span>
          </div>
        </div>

        <!-- Anomaly Explanation in Human Language -->
        <div
          v-if="selectedRecord.isAnomaly"
          class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-200/40 text-xs space-y-1"
        >
          <div class="font-semibold text-rose-800 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Unusual Activity Notice</span>
          </div>
          <p class="text-rose-700 leading-relaxed">
            {{ selectedRecord.anomalyReason }}
          </p>

          <!-- Advanced Details Collapsible (Hidden by default, for technical review) -->
          <div class="pt-2 border-t border-rose-200/40 mt-2">
            <button
              @click="showAdvancedInDetail = !showAdvancedInDetail"
              type="button"
              class="text-[11px] font-medium text-rose-600 hover:text-rose-800 underline transition-colors"
            >
              {{ showAdvancedInDetail ? 'Hide technical details' : 'Advanced details' }}
            </button>
            <div v-if="showAdvancedInDetail" class="mt-2 text-[11px] font-mono text-neu-muted space-y-0.5 bg-white/60 p-2 rounded-lg">
              <div>Record ID: {{ selectedRecord.id }}</div>
              <div>Employee ID: {{ selectedRecord.employeeId }}</div>
              <div v-if="selectedRecord.anomalyScore !== undefined">
                Anomaly Score: {{ selectedRecord.anomalyScore.toFixed(4) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="pt-2 text-right">
          <button
            @click="closeRowDetail"
            class="btn-primary px-5 py-2 text-xs font-semibold focus:outline-none"
          >
            Done
          </button>
        </div>
      </NeumorphicCard>
    </div>
  </div>
</template>
