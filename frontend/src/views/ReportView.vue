<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Report } from '@/types'
import { getReports, generateReport, downloadReport } from '@/api/reports'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const reports = ref<Report[]>([])
const loading = ref(false)
const generating = ref<string | null>(null)

const standardReports = [
  {
    type: 'turnover',
    title: 'Workforce Report',
    desc: 'Comprehensive overview of headcount and departmental staffing.',
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
  },
  {
    type: 'turnover',
    title: 'Attrition Report',
    desc: 'Turnover risk scores and high-probability departure forecasts.',
    icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
  },
  {
    type: 'attendance',
    title: 'Attendance Report',
    desc: 'Daily check-in adherence and verified anomaly logs.',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  },
  {
    type: 'payroll',
    title: 'Payroll Report',
    desc: 'Monthly compensation totals and supervisor-reviewed adjustments.',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  {
    type: 'performance',
    title: 'Performance Report',
    desc: 'Quarterly review distribution and talent appraisal ratings.',
    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
  },
]

async function fetchReports() {
  loading.value = true
  try {
    const response = await getReports()
    reports.value = response.items || []
  } finally {
    loading.value = false
  }
}

async function triggerReport(report: typeof standardReports[0]) {
  generating.value = report.title
  try {
    await generateReport({
      type: report.type,
      date_range_start: '2026-01-01',
      date_range_end: '2026-03-31',
      title: `${report.title} - Q1 2026`,
    })
    await fetchReports()
  } finally {
    generating.value = null
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
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch {
    // Handled
  }
}

onMounted(fetchReports)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Reports"
      subtitle="Create and review HR reports."
    >
      <template #action>
        <button
          @click="fetchReports"
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

    <!-- 5 Clean Report Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <NeumorphicCard
        v-for="card in standardReports"
        :key="card.title"
        class="p-5 flex flex-col justify-between"
      >
        <div>
          <div class="w-9 h-9 rounded-xl bg-neu-primary/10 text-neu-primary flex items-center justify-center mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="card.icon" />
            </svg>
          </div>
          <h3 class="text-sm font-bold text-neu-text tracking-tight mb-1">
            {{ card.title }}
          </h3>
          <p class="text-xs text-neu-muted leading-relaxed">
            {{ card.desc }}
          </p>
        </div>

        <div class="mt-4 pt-3 border-t border-neu-border/30">
          <button
            @click="triggerReport(card)"
            type="button"
            :disabled="generating === card.title"
            class="btn-secondary hover:border-neu-primary hover:text-neu-primary w-full py-2 text-xs font-semibold transition-colors focus:outline-none disabled:opacity-40"
          >
            {{ generating === card.title ? 'Generating...' : 'Generate Report' }}
          </button>
        </div>
      </NeumorphicCard>
    </div>

    <!-- Generated Reports History Table -->
    <NeumorphicCard class="p-0 overflow-hidden">
      <div class="p-4 border-b border-neu-border/40 bg-neu-surface/40">
        <h2 class="text-sm font-bold text-neu-text tracking-tight">Recent Reports</h2>
      </div>

      <LoadingSkeleton v-if="loading" type="table" :rows="4" />

      <div v-else-if="reports.length === 0" class="p-8 text-center text-xs text-neu-muted">
        No reports generated yet. Click "Generate" on any report above to create one.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
              <th class="py-3 px-5">Report Title</th>
              <th class="py-3 px-4">Period</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/30">
            <tr
              v-for="rep in reports"
              :key="rep.id"
              class="hover:bg-neu-base/40 transition-colors"
            >
              <td class="py-3 px-5 font-semibold text-neu-text">
                {{ rep.title || 'Workforce Report' }}
              </td>
              <td class="py-3 px-4 text-neu-muted">
                {{ rep.date_range_start }} to {{ rep.date_range_end }}
              </td>
              <td class="py-3 px-4 text-center">
                <StatusBadge :status="rep.status" size="sm" />
              </td>
              <td class="py-3 px-5 text-right">
                <button
                  v-if="rep.status === 'ready'"
                  @click="handleDownload(rep)"
                  type="button"
                  class="btn-primary px-3 py-1 text-xs font-semibold focus:outline-none"
                >
                  Download
                </button>
                <span v-else class="text-xs text-neu-muted">Processing</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>
  </div>
</template>
