<script setup lang="ts">
import { ref, onMounted, provide } from 'vue'
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
import StatCard from '@/components/StatCard.vue'

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

const totalEmployees = ref(248)
const attendanceRate = ref(94.2)
const pendingLeaves = ref(12)
const highRiskCount = ref(8)

const departmentChartOption = ref({
  title: { text: 'Department Headcount', left: 'center' },
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance', 'Operations'],
    axisLabel: { rotate: 30 },
  },
  yAxis: { type: 'value', name: 'Employees' },
  series: [
    {
      type: 'bar',
      data: [68, 45, 32, 18, 25, 60],
      itemStyle: { color: '#4F46E5' },
      barWidth: '50%',
    },
  ],
})

const attendanceTrendOption = ref({
  title: { text: 'Monthly Attendance Trend', left: 'center' },
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  },
  yAxis: { type: 'value', name: 'Rate (%)', min: 80, max: 100 },
  series: [
    {
      type: 'line',
      data: [95.1, 93.8, 94.5, 96.2, 95.0, 93.2, 91.8, 94.7, 95.3, 94.2, 96.1, 94.8],
      smooth: true,
      itemStyle: { color: '#10B981' },
      areaStyle: { color: 'rgba(16, 185, 129, 0.1)' },
    },
  ],
})

const turnoverRiskOption = ref({
  title: { text: 'Turnover Risk Distribution', left: 'center' },
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { bottom: 0 },
  series: [
    {
      type: 'pie',
      radius: ['40%', '70%'],
      data: [
        { value: 180, name: 'Low Risk', itemStyle: { color: '#10B981' } },
        { value: 42, name: 'Medium Risk', itemStyle: { color: '#F59E0B' } },
        { value: 18, name: 'High Risk', itemStyle: { color: '#F97316' } },
        { value: 8, name: 'Critical', itemStyle: { color: '#EF4444' } },
      ],
      emphasis: {
        itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)' },
      },
    },
  ],
})

provide('THEME_KEY', 'light')

onMounted(() => {
  // In production, these would fetch real data from the API
})
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard title="Total Employees" :value="totalEmployees" :change="3.2" change-label="vs last month">
        <template #icon>
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </template>
      </StatCard>

      <StatCard title="Attendance Rate" :value="`${attendanceRate}%`" :change="1.5" change-label="vs last week">
        <template #icon>
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </StatCard>

      <StatCard title="Pending Leaves" :value="pendingLeaves" :change="-8" change-label="vs last week">
        <template #icon>
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </StatCard>

      <StatCard title="High Risk Employees" :value="highRiskCount" :change="2" change-label="vs last month">
        <template #icon>
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </template>
      </StatCard>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-4">
        <VChart :option="departmentChartOption" style="height: 350px" autoresize />
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <VChart :option="attendanceTrendOption" style="height: 350px" autoresize />
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-4">
        <VChart :option="turnoverRiskOption" style="height: 350px" autoresize />
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-3">
          <router-link
            to="/attendance"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-sm text-gray-700">View Attendance Analytics</span>
          </router-link>
          <router-link
            to="/leaves"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="text-sm text-gray-700">Manage Leave Requests</span>
          </router-link>
          <router-link
            to="/reports"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm text-gray-700">Generate Reports</span>
          </router-link>
          <router-link
            to="/chatbot"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="text-sm text-gray-700">Ask AI Chatbot</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
