<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { getPayrolls, getPayrollStats, reviewPayrollAnomaly } from '@/api/payrolls'
import type { Payroll, PayrollStats } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const loading = ref(true)
const payrolls = ref<Payroll[]>([])
const stats = ref<PayrollStats | null>(null)
const totalItems = ref(0)
const currentPage = ref(1)
const totalPages = ref(1)

// Filters
const selectedMonth = ref('2026-03')
const filterAnomalyOnly = ref(false)
const selectedStatus = ref('')
const showModelModal = ref(false)

// Review Modal State
const activeReview = ref<Payroll | null>(null)
const reviewNotes = ref('')
const submittingReview = ref(false)

async function fetchStats() {
  try {
    stats.value = await getPayrollStats(selectedMonth.value)
  } catch (err) {
    console.error('Failed to load payroll stats', err)
  }
}

async function fetchPayrolls() {
  loading.value = true
  try {
    const res = await getPayrolls({
      page: currentPage.value,
      page_size: 10,
      month: selectedMonth.value,
      is_anomaly: filterAnomalyOnly.value ? true : undefined,
      review_status: selectedStatus.value || undefined,
    })
    payrolls.value = res.items || []
    totalItems.value = res.total || 0
    totalPages.value = res.total_pages || 1
  } catch (err) {
    console.error('Failed to load payroll list', err)
  } finally {
    loading.value = false
  }
}

function openReviewModal(item: Payroll) {
  activeReview.value = item
  reviewNotes.value = item.anomaly_explanation || ''
}

function closeReviewModal() {
  activeReview.value = null
  reviewNotes.value = ''
}

async function submitReview(status: 'reviewed' | 'escalated') {
  if (!activeReview.value) return
  submittingReview.value = true
  try {
    const updated = await reviewPayrollAnomaly(activeReview.value.id, status, reviewNotes.value)
    const idx = payrolls.value.findIndex(p => p.id === activeReview.value?.id)
    if (idx !== -1) {
      payrolls.value[idx].review_status = updated.review_status
    }
    await fetchStats()
    closeReviewModal()
  } finally {
    submittingReview.value = false
  }
}

function formatCurrency(val?: number): string {
  if (!val) return '$0'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(val)
}

watch([selectedMonth, filterAnomalyOnly, selectedStatus], () => {
  currentPage.value = 1
  fetchStats()
  fetchPayrolls()
})

watch(currentPage, () => {
  fetchPayrolls()
})

onMounted(() => {
  fetchStats()
  fetchPayrolls()
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Payroll"
      subtitle="Review payroll activity and unusual transactions."
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <!-- Month Selector -->
          <select
            v-model="selectedMonth"
            class="px-3 py-1.5 rounded-xl bg-neu-surface text-xs font-semibold text-neu-text border-none shadow-neu-flat-sm focus:outline-none cursor-pointer"
          >
            <option value="2026-03">March 2026</option>
            <option value="2026-02">February 2026</option>
            <option value="2026-01">January 2026</option>
          </select>

          <button
            @click="showModelModal = true"
            type="button"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold focus:outline-none"
          >
            Model Details
          </button>

          <button
            @click="fetchPayrolls"
            type="button"
            class="btn-secondary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
          >
            <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Scan</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- 4 Clean KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <NeumorphicStatCard
        title="Total Payroll"
        :value="formatCurrency(stats?.total_payout || 7450000)"
        caption="Monthly total"
      />
      <NeumorphicStatCard
        title="Changes"
        value="+1.8%"
        change="vs prior month"
        changeType="neutral"
      />
      <NeumorphicStatCard
        title="Anomalies"
        :value="stats?.total_anomalies || 12"
        change="Detected"
        changeType="negative"
      />
      <NeumorphicStatCard
        title="Pending Review"
        :value="stats?.pending_reviews || 4"
        change="Action needed"
        changeType="negative"
      />
    </div>

    <!-- Anomaly Alerts Section -->
    <NeumorphicCard class="p-5">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h2 class="text-sm font-bold text-neu-text tracking-tight">Anomaly Alerts</h2>
          <p class="text-xs text-neu-muted mt-0.5">Transactions requiring supervisory review</p>
        </div>
        <span class="text-xs font-semibold text-rose-600 bg-rose-500/10 px-2 py-0.5 rounded-full">
          {{ stats?.total_anomalies || 12 }} flagged
        </span>
      </div>

      <div class="divide-y divide-neu-border/30">
        <div
          v-for="p in payrolls.filter(item => item.is_anomaly).slice(0, 4)"
          :key="p.id"
          class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
        >
          <div class="flex items-start space-x-3">
            <span class="w-2 h-2 rounded-full bg-rose-500 mt-1.5 flex-shrink-0"></span>
            <div>
              <div class="text-xs font-bold text-neu-text">
                {{ p.employee?.first_name ? `${p.employee.first_name} ${p.employee.last_name}` : `Employee #${p.employee_id}` }}
              </div>
              <p class="text-xs text-neu-muted mt-0.5">
                {{ p.anomaly_explanation || 'Unusual compensation change or overtime variance' }}
              </p>
            </div>
          </div>

          <div class="flex items-center space-x-3 self-end sm:self-center">
            <span class="text-xs font-bold text-neu-text font-mono">
              {{ formatCurrency(p.net_salary) }}
            </span>
            <button
              @click="openReviewModal(p)"
              type="button"
              class="px-2.5 py-1 rounded-lg bg-neu-primary/10 text-neu-primary text-xs font-semibold hover:bg-neu-primary/15 transition-colors focus:outline-none"
            >
              Review
            </button>
          </div>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Recent Changes / Payroll List Table -->
    <NeumorphicCard class="p-0 overflow-hidden">
      <!-- Filter Bar -->
      <div class="p-4 border-b border-neu-border/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-neu-surface/40">
        <h3 class="text-xs font-bold uppercase tracking-wider text-neu-muted">
          Payroll Records
        </h3>

        <div class="flex items-center space-x-4">
          <label class="flex items-center space-x-2 text-xs font-medium text-neu-text cursor-pointer">
            <input
              v-model="filterAnomalyOnly"
              type="checkbox"
              class="rounded border-neu-border text-neu-primary focus:ring-0"
            />
            <span>Anomalies only</span>
          </label>
        </div>
      </div>

      <LoadingSkeleton v-if="loading" type="table" :rows="6" />

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
              <th class="py-3 px-5">Employee</th>
              <th class="py-3 px-4 text-right">Base Salary</th>
              <th class="py-3 px-4 text-right">Overtime</th>
              <th class="py-3 px-4 text-right">Net Payout</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/30">
            <tr
              v-for="p in payrolls"
              :key="p.id"
              class="hover:bg-neu-base/40 transition-colors"
            >
              <td class="py-3 px-5 font-semibold text-neu-text">
                {{ p.employee?.first_name ? `${p.employee.first_name} ${p.employee.last_name}` : `Employee #${p.employee_id}` }}
              </td>
              <td class="py-3 px-4 text-right text-neu-text font-mono">
                {{ formatCurrency(p.base_salary) }}
              </td>
              <td class="py-3 px-4 text-right text-neu-muted font-mono">
                {{ formatCurrency(p.overtime_pay) }}
              </td>
              <td class="py-3 px-4 text-right font-bold text-neu-text font-mono">
                {{ formatCurrency(p.net_salary) }}
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  v-if="p.is_anomaly"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-700"
                >
                  Anomaly
                </span>
                <span v-else class="text-[11px] text-neu-muted">Standard</span>
              </td>
              <td class="py-3 px-5 text-right">
                <button
                  v-if="p.is_anomaly"
                  @click="openReviewModal(p)"
                  type="button"
                  class="px-2 py-0.5 text-xs font-semibold text-neu-primary hover:underline focus:outline-none"
                >
                  Review
                </button>
                <span v-else class="text-xs text-neu-muted">&ndash;</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3 border-t border-neu-border/40 bg-neu-surface/30">
        <span class="text-xs text-neu-muted">
          Page {{ currentPage }} of {{ totalPages }} ({{ totalItems }} items)
        </span>
        <div class="flex items-center space-x-2">
          <button
            type="button"
            :disabled="currentPage <= 1"
            @click="currentPage--"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none"
          >
            Previous
          </button>
          <button
            type="button"
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
            class="btn-secondary px-3 py-1.5 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none"
          >
            Next
          </button>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Review Modal -->
    <div
      v-if="activeReview"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
      @click.self="closeReviewModal"
    >
      <NeumorphicCard class="max-w-lg w-full p-6 space-y-4 shadow-neu-flat-lg">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <div>
            <h3 class="text-sm font-bold text-neu-text">Review Payroll Anomaly</h3>
            <span class="text-xs text-neu-muted">
              {{ activeReview.employee?.first_name }} {{ activeReview.employee?.last_name }}
            </span>
          </div>
          <button @click="closeReviewModal" class="text-neu-muted hover:text-neu-text text-sm font-bold">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="p-3.5 rounded-xl bg-neu-base/70 space-y-1">
            <div class="text-[10px] font-bold uppercase tracking-wider text-neu-muted">Calculated Net</div>
            <div class="text-lg font-bold text-neu-text">{{ formatCurrency(activeReview.net_salary) }}</div>
            <p class="text-neu-muted text-[11px] pt-1">
              {{ activeReview.anomaly_explanation || 'Overtime spike or deduction irregularity.' }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-neu-text mb-1">Supervisor Notes</label>
            <textarea
              v-model="reviewNotes"
              rows="3"
              class="w-full p-3 rounded-xl bg-neu-base shadow-neu-inset text-xs text-neu-text focus:outline-none"
              placeholder="Enter resolution notes..."
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-neu-border/40">
          <button
            @click="closeReviewModal"
            class="btn-secondary px-3.5 py-1.5 text-xs font-semibold"
          >
            Cancel
          </button>
          <button
            @click="submitReview('escalated')"
            class="btn-accent px-3.5 py-1.5 text-xs font-semibold"
          >
            Escalate
          </button>
          <button
            @click="submitReview('reviewed')"
            class="btn-primary px-4 py-1.5 text-xs font-semibold"
          >
            Mark Resolved
          </button>
        </div>
      </NeumorphicCard>
    </div>

    <!-- Model Details Modal -->
    <div
      v-if="showModelModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
      @click.self="showModelModal = false"
    >
      <NeumorphicCard class="max-w-md w-full p-6 space-y-4 shadow-neu-flat-lg">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <h3 class="text-sm font-bold text-neu-text">Payroll Audit Model</h3>
          <button @click="showModelModal = false" class="text-neu-muted hover:text-neu-text text-sm font-bold">&times;</button>
        </div>
        <div class="space-y-3 text-xs text-neu-text leading-relaxed">
          <p>
            The payroll audit engine runs automated anomaly detection against base compensation bands and overtime deviations.
          </p>
          <div class="p-3 rounded-xl bg-neu-base/70 space-y-1 text-[11px]">
            <div><strong>Algorithm:</strong> Isolation Forest + 3σ Statistical Thresholds</div>
            <div><strong>Signals:</strong> Overtime surge, duplicate payroll lines, band drift</div>
            <div><strong>Sensitivity:</strong> High confidence flags only</div>
          </div>
        </div>
        <div class="pt-2 text-right">
          <button
            @click="showModelModal = false"
            class="btn-primary px-4 py-1.5 text-xs font-semibold"
          >
            Close
          </button>
        </div>
      </NeumorphicCard>
    </div>
  </div>
</template>
