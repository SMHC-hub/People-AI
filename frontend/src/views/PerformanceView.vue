<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { getPerformances, getPerformanceStats } from '@/api/performances'
import type { PerformanceReview, PerformanceStats } from '@/types'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

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
      page_size: 15,
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
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-neu-text">
          Performance & Appraisal Intelligence
        </h2>
        <p class="text-sm text-neu-muted mt-1">
          Quarterly review appraisals, goal completion telemetry, and AI-assisted promotion recommendations.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <select
          v-model="selectedPeriod"
          class="px-4 py-2.5 rounded-2xl bg-neu-surface shadow-neu-inset text-sm font-bold text-neu-text border border-white/40 focus:outline-none"
        >
          <option value="2025-Q4">2025 Q4 Appraisal</option>
          <option value="2025-Q3">2025 Q3 Appraisal</option>
          <option value="2025-Q2">2025 Q2 Appraisal</option>
        </select>

        <NeumorphicButton variant="default" size="sm" @click="fetchReviews">
          Refresh
        </NeumorphicButton>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <NeumorphicStatCard
        title="Completed Appraisals"
        :value="stats?.total_reviews || 600"
        subtitle="Current review cycle"
        trend="98.2% completion rate"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Average Rating"
        :value="stats?.avg_rating ? stats.avg_rating + ' / 5.0' : '3.8 / 5.0'"
        subtitle="Normal distribution curve"
        trend="+0.2 vs prior quarter"
        iconBg="success"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Promotion Recommended"
        :value="stats?.promotion_recommendation_rate ? stats.promotion_recommendation_rate + '%' : '18.3%'"
        subtitle="Qualifying high-impact talent"
        trend="Calibrated with salary band"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Leadership Pipeline"
        value="48 Leaders"
        subtitle="Rating >= 4.5 & Tenure > 2y"
        trend="Succession Planning"
        iconBg="warning"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- Rating Distribution Card -->
    <NeumorphicCard>
      <h3 class="text-base font-extrabold text-neu-text mb-4">
        Talent Rating Distribution (Bell Curve Calibration)
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40 text-center">
          <div class="text-xs font-bold text-neu-muted uppercase">Outstanding (4.5 - 5.0)</div>
          <div class="text-2xl font-black text-emerald-600 mt-1">18%</div>
          <div class="text-[11px] text-neu-muted mt-0.5">108 employees</div>
        </div>

        <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40 text-center">
          <div class="text-xs font-bold text-neu-muted uppercase">Exceeds (3.8 - 4.4)</div>
          <div class="text-2xl font-black text-neu-primary mt-1">42%</div>
          <div class="text-[11px] text-neu-muted mt-0.5">252 employees</div>
        </div>

        <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40 text-center">
          <div class="text-xs font-bold text-neu-muted uppercase">Meets (3.0 - 3.7)</div>
          <div class="text-2xl font-black text-neu-text mt-1">32%</div>
          <div class="text-[11px] text-neu-muted mt-0.5">192 employees</div>
        </div>

        <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40 text-center">
          <div class="text-xs font-bold text-neu-muted uppercase">Needs Coaching (&lt; 3.0)</div>
          <div class="text-2xl font-black text-rose-600 mt-1">8%</div>
          <div class="text-[11px] text-neu-muted mt-0.5">48 employees</div>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Reviews Table Card -->
    <NeumorphicCard>
      <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-neu-border/50">
        <div class="flex items-center space-x-3">
          <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Filter:</span>
          <button
            @click="filterPromotionOnly = !filterPromotionOnly"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200"
            :class="filterPromotionOnly ? 'bg-neu-primary text-white shadow-neu-pressed' : 'bg-neu-surface text-neu-muted shadow-neu-flat hover:text-neu-text'"
          >
            Promotion Recommended Only
          </button>
        </div>
        <div class="text-xs text-neu-muted">
          Showing {{ reviews.length }} reviews
        </div>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Employee</th>
              <th class="py-3.5 px-4">Department</th>
              <th class="py-3.5 px-4 text-center">Rating</th>
              <th class="py-3.5 px-4 text-center">Goals Met</th>
              <th class="py-3.5 px-4 text-center">Promotion</th>
              <th class="py-3.5 px-4">Appraisal Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="r in reviews"
              :key="r.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <td class="py-3.5 px-4 font-extrabold text-neu-text">
                {{ r.employee ? `${r.employee.first_name} ${r.employee.last_name}` : `EMP-${r.employee_id}` }}
              </td>
              <td class="py-3.5 px-4 text-xs font-semibold text-neu-text">
                {{ r.employee?.department || 'R&D' }}
              </td>
              <td class="py-3.5 px-4 text-center font-black">
                <span :class="r.rating >= 4.0 ? 'text-emerald-600' : r.rating >= 3.0 ? 'text-neu-text' : 'text-rose-600'">
                  {{ r.rating }} / 5.0
                </span>
              </td>
              <td class="py-3.5 px-4 text-center font-semibold text-neu-muted">
                {{ r.goals_met }}%
              </td>
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge
                  :variant="r.promotion_recommended ? 'success' : 'neutral'"
                  size="sm"
                >
                  {{ r.promotion_recommended ? 'RECOMMENDED' : 'NO' }}
                </NeumorphicBadge>
              </td>
              <td class="py-3.5 px-4 text-xs text-neu-muted max-w-xs truncate">
                {{ r.notes || 'Consistently delivers on sprint deliverables and demonstrates leadership.' }}
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
  </div>
</template>
