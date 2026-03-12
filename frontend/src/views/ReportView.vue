<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Report } from '@/types'
import { getReports, generateReport, downloadReport } from '@/api/reports'

const reports = ref<Report[]>([])
const loading = ref(false)
const generating = ref(false)

const reportForm = ref({
  type: 'attendance' as string,
  date_range_start: '',
  date_range_end: '',
  title: '',
})

const reportTypes = [
  { value: 'attendance', label: 'Attendance Report' },
  { value: 'turnover', label: 'Turnover Analysis' },
  { value: 'leave', label: 'Leave Summary' },
  { value: 'performance', label: 'Performance Report' },
  { value: 'department', label: 'Department Report' },
]

function getStatusClass(status: string): string {
  switch (status) {
    case 'ready':
      return 'bg-green-100 text-green-800'
    case 'generating':
      return 'bg-yellow-100 text-yellow-800'
    case 'failed':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

function getStatusIcon(status: string): string {
  switch (status) {
    case 'ready':
      return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    case 'generating':
      return 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
    case 'failed':
      return 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
    default:
      return ''
  }
}

async function fetchReports() {
  loading.value = true
  try {
    const response = await getReports()
    reports.value = response.items
  } finally {
    loading.value = false
  }
}

async function handleGenerate() {
  generating.value = true
  try {
    await generateReport({
      type: reportForm.value.type,
      date_range_start: reportForm.value.date_range_start,
      date_range_end: reportForm.value.date_range_end,
      title: reportForm.value.title || undefined,
    })
    reportForm.value = { type: 'attendance', date_range_start: '', date_range_end: '', title: '' }
    await fetchReports()
  } finally {
    generating.value = false
  }
}

async function handleDownload(report: Report) {
  try {
    const blob = await downloadReport(report.id)
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${report.title || 'report'}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
  } catch (error) {
    console.error('Download failed:', error)
  }
}

onMounted(fetchReports)
</script>

<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Reports</h1>

    <!-- Report Generation Form -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Generate New Report</h2>
      <form @submit.prevent="handleGenerate" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select
            v-model="reportForm.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          >
            <option v-for="rt in reportTypes" :key="rt.value" :value="rt.value">{{ rt.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            v-model="reportForm.date_range_start"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            v-model="reportForm.date_range_end"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
          />
        </div>
        <div class="flex items-end">
          <button
            type="submit"
            :disabled="generating"
            class="w-full px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition-colors text-sm font-medium"
          >
            {{ generating ? 'Generating...' : 'Generate Report' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Generated Reports</h2>
      </div>
      <div v-if="loading" class="px-6 py-12 text-center text-gray-500">Loading...</div>
      <div v-else-if="reports.length === 0" class="px-6 py-12 text-center text-gray-500">
        No reports generated yet. Create your first report above.
      </div>
      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="report in reports"
          :key="report.id"
          class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-gray-50"
        >
          <div class="flex items-center gap-4">
            <div class="p-2 bg-indigo-50 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ report.title }}</p>
              <p class="text-sm text-gray-500">
                {{ report.type.charAt(0).toUpperCase() + report.type.slice(1) }} |
                {{ report.date_range_start }} to {{ report.date_range_end }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span
              :class="[
                'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium',
                getStatusClass(report.status),
              ]"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon(report.status)" />
              </svg>
              {{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
            </span>
            <button
              v-if="report.status === 'ready'"
              @click="handleDownload(report)"
              class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
