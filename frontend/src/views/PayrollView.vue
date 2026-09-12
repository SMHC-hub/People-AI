<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { getPayrolls, getPayrollStats, reviewPayrollAnomaly } from '@/api/payrolls'
import type { Payroll, PayrollStats } from '@/types'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

const loading = ref(true)
const payrolls = ref<Payroll[]>([])
const stats = ref<PayrollStats | null>(null)
const totalItems = ref(0)
const currentPage = ref(1)
const totalPages = ref(1)

// Filters
const selectedMonth = ref('2026-03')
const filterAnomalyOnly = ref(true)
const selectedStatus = ref('')

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
      page_size: 15,
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
    // Update locally
    const idx = payrolls.value.findIndex(p => p.id === activeReview.value?.id)
    if (idx !== -1) {
      payrolls.value[idx].review_status = updated.review_status
    }
    await fetchStats()
    closeReviewModal()
  } catch (err) {
    console.error('Failed to update review status', err)
  } finally {
    submittingReview.value = false
  }
}

function formatCurrency(val: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(val)
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
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <h2 class="text-2xl font-black tracking-tight text-neu-text">
            Payroll Intelligence & Anomaly Audit Engine
          </h2>
          <NeumorphicBadge variant="primary" size="sm">Isolation Forest + 3σ Rules</NeumorphicBadge>
        </div>
        <p class="text-sm text-neu-muted mt-1">
          Automated auditing for abnormal overtime spikes, duplicate payouts, and executive compensation anomalies.
        </p>
      </div>

      <!-- Month Selector -->
      <div class="flex items-center space-x-3">
        <select
          v-model="selectedMonth"
          class="px-4 py-2.5 rounded-2xl bg-neu-surface shadow-neu-inset text-sm font-bold text-neu-text border border-white/40 focus:outline-none"
        >
          <option value="2026-03">March 2026</option>
          <option value="2026-02">February 2026</option>
          <option value="2026-01">January 2026</option>
        </select>

        <NeumorphicButton variant="default" size="sm" @click="fetchPayrolls">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Re-scan Batch
        </NeumorphicButton>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <NeumorphicStatCard
        title="Total Monthly Payout"
        :value="formatCurrency(stats?.total_payout || 7450000)"
        subtitle="1,000 corporate payroll lines"
        trend="Calculated & audited"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Average Net Salary"
        :value="formatCurrency(stats?.avg_net_salary || 7450)"
        subtitle="Standard monthly package"
        trend="Base + Allowances"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Total Overtime Disbursed"
        :value="formatCurrency(stats?.total_overtime_pay || 215000)"
        subtitle="Across all engineering & ops"
        trend="Regulated hourly rate"
        iconBg="warning"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Flagged Anomalies"
        :value="stats?.total_anomalies || 35"
        subtitle="Requires human audit"
        :trend="stats?.pending_reviews ? `${stats.pending_reviews} Pending Review` : 'All Reviewed'"
        iconBg="danger"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- Filter Bar Card -->
    <NeumorphicCard>
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Filters:</span>

          <!-- Anomaly Toggle -->
          <button
            @click="filterAnomalyOnly = !filterAnomalyOnly"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200"
            :class="filterAnomalyOnly ? 'bg-rose-500 text-white shadow-neu-pressed' : 'bg-neu-surface text-neu-muted shadow-neu-flat hover:text-neu-text'"
          >
            Anomalies Only ({{ stats?.total_anomalies || 0 }})
          </button>

          <!-- Status Filter -->
          <select
            v-model="selectedStatus"
            class="px-3 py-1.5 rounded-xl bg-neu-surface shadow-neu-inset text-xs font-bold text-neu-text border border-white/40 focus:outline-none"
          >
            <option value="">All Review Statuses</option>
            <option value="pending">Pending Review</option>
            <option value="reviewed">Reviewed & Approved</option>
            <option value="escalated">Escalated to Finance</option>
          </select>
        </div>

        <div class="text-xs font-medium text-neu-muted">
          Showing {{ payrolls.length }} of {{ totalItems }} records
        </div>
      </div>
    </NeumorphicCard>

    <!-- Payroll Table Card -->
    <NeumorphicCard>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Employee</th>
              <th class="py-3.5 px-4">Department</th>
              <th class="py-3.5 px-4 text-right">Base Salary</th>
              <th class="py-3.5 px-4 text-right">Overtime Pay</th>
              <th class="py-3.5 px-4 text-right">Bonus</th>
              <th class="py-3.5 px-4 text-right">Net Payout</th>
              <th class="py-3.5 px-4 text-center">Anomaly Flag</th>
              <th class="py-3.5 px-4 text-center">Audit Status</th>
              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="item in payrolls"
              :key="item.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <!-- Employee Info -->
              <td class="py-3.5 px-4">
                <div class="font-extrabold text-neu-text">
                  {{ item.employee ? `${item.employee.first_name} ${item.employee.last_name}` : `EMP-${item.employee_id}` }}
                </div>
                <div class="text-[11px] text-neu-muted font-mono">
                  {{ item.employee?.employee_id || `ID: ${item.employee_id}` }}
                </div>
              </td>

              <!-- Department -->
              <td class="py-3.5 px-4 text-xs font-semibold text-neu-text">
                {{ item.employee?.department || 'General' }}
              </td>

              <!-- Base Salary -->
              <td class="py-3.5 px-4 text-right font-medium text-neu-muted">
                {{ formatCurrency(item.base_salary) }}
              </td>

              <!-- Overtime -->
              <td class="py-3.5 px-4 text-right font-semibold" :class="item.overtime_pay > 1000 ? 'text-rose-600' : 'text-neu-text'">
                {{ formatCurrency(item.overtime_pay) }}
                <div v-if="item.overtime_hours" class="text-[10px] text-neu-muted font-normal">
                  {{ item.overtime_hours }} hrs
                </div>
              </td>

              <!-- Bonus -->
              <td class="py-3.5 px-4 text-right font-semibold" :class="item.bonus > 4000 ? 'text-amber-600' : 'text-neu-text'">
                {{ formatCurrency(item.bonus) }}
              </td>

              <!-- Net Payout -->
              <td class="py-3.5 px-4 text-right font-black text-neu-text">
                {{ formatCurrency(item.net_salary) }}
              </td>

              <!-- Anomaly Flag & Score -->
              <td class="py-3.5 px-4 text-center">
                <div v-if="item.is_anomaly" class="flex flex-col items-center space-y-1">
                  <NeumorphicBadge
                    :variant="item.anomaly_type?.includes('overtime') || item.anomaly_type?.includes('duplicate') ? 'danger' : 'warning'"
                    size="sm"
                  >
                    {{ item.anomaly_type ? item.anomaly_type.replace('_', ' ').toUpperCase() : 'ANOMALY' }}
                  </NeumorphicBadge>
                  <span v-if="item.anomaly_score" class="text-[10px] font-mono font-bold text-rose-600">
                    Score: {{ item.anomaly_score }}/100
                  </span>
                </div>
                <span v-else class="text-xs text-neu-muted">Normal</span>
              </td>

              <!-- Audit Status -->
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge
                  :variant="item.review_status === 'reviewed' ? 'success' : item.review_status === 'escalated' ? 'danger' : 'neutral'"
                  size="sm"
                >
                  {{ item.review_status.toUpperCase() }}
                </NeumorphicBadge>
              </td>

              <!-- Action Button -->
              <td class="py-3.5 px-4 text-right">
                <NeumorphicButton
                  variant="default"
                  size="sm"
                  @click="openReviewModal(item)"
                >
                  Review
                </NeumorphicButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between pt-6 border-t border-neu-border/50">
        <span class="text-xs font-medium text-neu-muted">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <div class="flex items-center space-x-2">
          <NeumorphicButton
            variant="default"
            size="sm"
            :disabled="currentPage <= 1"
            @click="currentPage--"
          >
            Previous
          </NeumorphicButton>
          <NeumorphicButton
            variant="default"
            size="sm"
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
          >
            Next
          </NeumorphicButton>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Review / Audit Modal -->
    <div
      v-if="activeReview"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm animate-fadeIn"
    >
      <div class="w-full max-w-lg rounded-3xl bg-neu-surface shadow-neu-raised border border-white/80 p-6 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-neu-border/50">
          <div>
            <h3 class="text-lg font-black text-neu-text tracking-tight">
              Payroll Anomaly Audit Review
            </h3>
            <p class="text-xs text-neu-muted mt-0.5">
              Record: {{ activeReview.employee ? `${activeReview.employee.first_name} ${activeReview.employee.last_name}` : `EMP-${activeReview.employee_id}` }} ({{ activeReview.month }})
            </p>
          </div>
          <button @click="closeReviewModal" class="text-neu-muted hover:text-neu-text">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Anomaly Summary -->
        <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset space-y-2 border border-white/40">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-neu-muted uppercase">Detected Anomaly Type</span>
            <NeumorphicBadge variant="danger" size="sm">
              {{ activeReview.anomaly_type?.replace('_', ' ').toUpperCase() || 'UNUSUAL DISBURSEMENT' }}
            </NeumorphicBadge>
          </div>
          <p class="text-xs text-neu-text leading-relaxed">
            {{ activeReview.anomaly_explanation || 'Isolation forest statistical variance exceeds the 99th percentile threshold.' }}
          </p>
          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-neu-border/40 text-xs">
            <div>
              <span class="text-neu-muted">Base Salary:</span>
              <span class="font-bold ml-1">{{ formatCurrency(activeReview.base_salary) }}</span>
            </div>
            <div>
              <span class="text-neu-muted">Overtime Pay:</span>
              <span class="font-bold ml-1 text-rose-600">{{ formatCurrency(activeReview.overtime_pay) }}</span>
            </div>
            <div>
              <span class="text-neu-muted">Bonus:</span>
              <span class="font-bold ml-1 text-amber-600">{{ formatCurrency(activeReview.bonus) }}</span>
            </div>
            <div>
              <span class="text-neu-muted">Net Payout:</span>
              <span class="font-bold ml-1 text-neu-primary">{{ formatCurrency(activeReview.net_salary) }}</span>
            </div>
          </div>
        </div>

        <!-- Auditor Notes -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">
            Auditor Notes & Justification
          </label>
          <textarea
            v-model="reviewNotes"
            rows="3"
            placeholder="Explain approval rationale or reason for escalation to corporate finance..."
            class="w-full px-4 py-3 rounded-2xl bg-neu-base shadow-neu-inset text-xs text-neu-text border border-white/40 focus:outline-none resize-none"
          ></textarea>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center justify-end space-x-3 pt-2">
          <NeumorphicButton variant="default" size="sm" @click="closeReviewModal">
            Cancel
          </NeumorphicButton>

          <NeumorphicButton
            variant="danger"
            size="sm"
            :loading="submittingReview"
            @click="submitReview('escalated')"
          >
            Escalate to Finance
          </NeumorphicButton>

          <NeumorphicButton
            variant="primary"
            size="sm"
            :loading="submittingReview"
            @click="submitReview('reviewed')"
          >
            Approve & Mark Reviewed
          </NeumorphicButton>
        </div>
      </div>
    </div>
  </div>
</template>
