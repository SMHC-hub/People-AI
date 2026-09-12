<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Report } from '@/types'
import { getReports, generateReport, downloadReport } from '@/api/reports'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const reports = ref<Report[]>([])
const loading = ref(false)
const generating = ref(false)

const reportForm = ref({
  type: 'turnover' as string,
  date_range_start: '2026-01-01',
  date_range_end: '2026-03-31',
  title: '',
})

const reportTypes = [
  { value: 'turnover', label: 'Workforce Attrition Risk Brief' },
  { value: 'attendance', label: 'Attendance & Overtime Telemetry' },
  { value: 'leave', label: 'Leave Liability & Absence Summary' },
  { value: 'performance', label: 'Quarterly Appraisal Distribution' },
  { value: 'department', label: 'Department Headcount & Compensation' },
]

function getStatusBadge(status: string): 'success' | 'warning' | 'danger' | 'neutral' {
  switch (status) {
    case 'ready':
      return 'success'
    case 'generating':
      return 'warning'
    case 'failed':
      return 'danger'
    default:
      return 'neutral'
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
    reportForm.value = {
      type: 'turnover',
      date_range_start: '2026-01-01',
      date_range_end: '2026-03-31',
      title: '',
    }
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
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-neu-text">
          Workforce Reports & Executive Briefs
        </h2>
        <p class="text-sm text-neu-muted mt-1">
          Automated export engine for compliance audits, board presentations, and department head counts.
        </p>
      </div>
    </div>

    <!-- Generator Card -->
    <NeumorphicCard>
      <div class="pb-4 border-b border-neu-border/50">
        <h3 class="text-base font-extrabold text-neu-text">Generate Analytical Brief</h3>
        <p class="text-xs text-neu-muted mt-0.5">Select parameter horizons to build automated summaries.</p>
      </div>

      <form @submit.prevent="handleGenerate" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <div class="space-y-1">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">Report Type</label>
          <select
            v-model="reportForm.type"
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
          >
            <option v-for="rt in reportTypes" :key="rt.value" :value="rt.value">{{ rt.label }}</option>
          </select>
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">Start Date</label>
          <input
            v-model="reportForm.date_range_start"
            type="date"
            required
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
          />
        </div>

        <div class="space-y-1">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">End Date</label>
          <input
            v-model="reportForm.date_range_end"
            type="date"
            required
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-xs font-semibold text-neu-text border border-white/40 focus:outline-none"
          />
        </div>

        <div class="flex items-end">
          <NeumorphicButton
            variant="primary"
            size="md"
            type="submit"
            :loading="generating"
            class="w-full"
          >
            Generate Brief
          </NeumorphicButton>
        </div>
      </form>
    </NeumorphicCard>

    <!-- Generated Reports Card -->
    <NeumorphicCard>
      <div class="pb-4 border-b border-neu-border/50 flex items-center justify-between">
        <h3 class="text-base font-extrabold text-neu-text">Generated Reports Archive</h3>
        <span class="text-xs text-neu-muted font-medium">{{ reports.length }} available</span>
      </div>

      <div v-if="loading" class="py-12 text-center text-neu-muted font-bold text-xs">
        Loading reports...
      </div>

      <div v-else-if="reports.length === 0" class="py-12 text-center text-neu-muted text-xs">
        No reports generated yet. Use the form above to generate your first workforce brief.
      </div>

      <div v-else class="divide-y divide-neu-border/40 mt-2">
        <div
          v-for="report in reports"
          :key="report.id"
          class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-neu-base/40 px-2 rounded-2xl transition-colors duration-150"
        >
          <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-2xl bg-neu-base shadow-neu-flat flex items-center justify-center text-neu-primary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h4 class="font-extrabold text-neu-text text-sm">{{ report.title || 'Workforce Analytics Brief' }}</h4>
              <p class="text-xs text-neu-muted font-mono mt-0.5">
                {{ report.type.toUpperCase() }} &bull; {{ report.date_range_start }} to {{ report.date_range_end }}
              </p>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <NeumorphicBadge :variant="getStatusBadge(report.status)" size="sm">
              {{ report.status.toUpperCase() }}
            </NeumorphicBadge>

            <NeumorphicButton
              v-if="report.status === 'ready'"
              variant="default"
              size="sm"
              @click="handleDownload(report)"
            >
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download PDF
            </NeumorphicButton>
          </div>
        </div>
      </div>
    </NeumorphicCard>
  </div>
</template>
