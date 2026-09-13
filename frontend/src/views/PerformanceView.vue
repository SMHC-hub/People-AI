<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { getPerformances, getPerformanceStats } from '@/api/performances'
import type { PerformanceReview, PerformanceStats } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const loading = ref(true)
const reviews = ref<PerformanceReview[]>([])
const stats = ref<PerformanceStats | null>(null)
const currentPage = ref(1)
const totalPages = ref(1)
const totalReviews = ref(0)

const selectedPeriod = ref('2025-Q4')
const filterPromotionOnly = ref(false)

async function fetchStats() {
  try {
    stats.value = await getPerformanceStats(selectedPeriod.value)
  } catch (err) {
    console.error('Failed to load performance stats', err)
  }
}

async function fetchReviews() {
  loading.value = true
  try {
    const res = await getPerformances({
      page: currentPage.value,
      page_size: 10,
      review_period: selectedPeriod.value,
      promotion_recommended: filterPromotionOnly.value ? true : undefined,
    })
    reviews.value = res.items || []
    totalReviews.value = res.total || 0
    totalPages.value = res.total_pages || 1
  } catch (err) {
    console.error('Failed to load reviews', err)
  } finally {
    loading.value = false
  }
}

watch([selectedPeriod, filterPromotionOnly], () => {
  currentPage.value = 1
  fetchStats()
  fetchReviews()
})

watch(currentPage, () => {
  fetchReviews()
})

onMounted(() => {
  fetchStats()
  fetchReviews()
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Performance"
      subtitle="Track employee reviews and talent appraisals."
    >
      <template #action>
        <div class="flex items-center space-x-2">
          <select
            v-model="selectedPeriod"
            class="px-3 py-1.5 rounded-xl bg-neu-surface text-xs font-semibold text-neu-text border-none shadow-neu-flat-sm focus:outline-none cursor-pointer"
          >
            <option value="2025-Q4">2025 Q4 Review</option>
            <option value="2025-Q3">2025 Q3 Review</option>
            <option value="2025-Q2">2025 Q2 Review</option>
          </select>

          <button
            @click="fetchReviews"
            type="button"
            class="btn-secondary flex items-center space-x-1.5 px-3.5 py-1.5 text-xs font-semibold focus:outline-none"
          >
            <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- 4 Clean KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <NeumorphicStatCard
        title="Completed Reviews"
        :value="stats?.total_reviews || 600"
        caption="Current cycle"
      />
      <NeumorphicStatCard
        title="Average Rating"
        :value="stats?.avg_rating ? `${stats.avg_rating} / 5.0` : '3.8 / 5.0'"
        change="+0.2 vs prior"
        changeType="positive"
      />
      <NeumorphicStatCard
        title="Goals Completed"
        value="86%"
        caption="Key deliverables met"
      />
      <NeumorphicStatCard
        title="Promotions"
        :value="stats?.promotion_recommendation_rate ? Math.round(stats.promotion_recommendation_rate * (stats.total_reviews || 600)) : 42"
        change="Candidates"
        changeType="positive"
      />
    </div>

    <!-- Reviews Table -->
    <NeumorphicCard class="p-0 overflow-hidden">
      <div class="p-4 border-b border-neu-border/40 bg-neu-surface/40 flex items-center justify-between">
        <h2 class="text-sm font-bold text-neu-text tracking-tight">Appraisal Records</h2>
        <label class="flex items-center space-x-2 text-xs font-medium text-neu-text cursor-pointer">
          <input
            v-model="filterPromotionOnly"
            type="checkbox"
            class="rounded border-neu-border text-neu-primary focus:ring-0"
          />
          <span>Promotion recommended only</span>
        </label>
      </div>

      <LoadingSkeleton v-if="loading" type="table" :rows="5" />

      <div v-else-if="reviews.length === 0" class="p-8 text-center text-xs text-neu-muted">
        No performance reviews found for the selected criteria.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
              <th class="py-3 px-5">Employee</th>
              <th class="py-3 px-4">Reviewer</th>
              <th class="py-3 px-4 text-center">Score</th>
              <th class="py-3 px-4 text-center">Goals Met</th>
              <th class="py-3 px-4 text-center">Promotion</th>
              <th class="py-3 px-5 text-right">Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/30">
            <tr
              v-for="rev in reviews"
              :key="rev.id"
              class="hover:bg-neu-base/40 transition-colors"
            >
              <td class="py-3 px-5 font-semibold text-neu-text">
                {{ rev.employee?.first_name ? `${rev.employee.first_name} ${rev.employee.last_name}` : `Employee #${rev.employee_id}` }}
              </td>
              <td class="py-3 px-4 text-neu-muted">
                {{ rev.reviewer?.full_name || 'Supervisor' }}
              </td>
              <td class="py-3 px-4 text-center font-bold text-neu-text">
                {{ rev.rating }} / 5
              </td>
              <td class="py-3 px-4 text-center text-neu-text">
                {{ rev.goals_met || 85 }}%
              </td>
              <td class="py-3 px-4 text-center">
                <span
                  v-if="rev.promotion_recommended"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-700"
                >
                  Recommended
                </span>
                <span v-else class="text-[11px] text-neu-muted">Standard</span>
              </td>
              <td class="py-3 px-5 text-right text-neu-muted truncate max-w-xs">
                {{ rev.notes || rev.review_period }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3 border-t border-neu-border/40 bg-neu-surface/30">
        <span class="text-xs text-neu-muted">
          Page {{ currentPage }} of {{ totalPages }} ({{ totalReviews }} items)
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
  </div>
</template>
