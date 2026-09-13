<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getPortalPerformances } from '@/api/portal'
import type { PerformanceReview, PaginatedResponse } from '@/types'
import { formatDate } from '@/utils/formatters'
import PageHeader from '@/components/PageHeader.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const loading = ref(true)
const performanceData = ref<PaginatedResponse<PerformanceReview> | null>(null)

async function fetchPerformance() {
  loading.value = true
  try {
    performanceData.value = await getPortalPerformances()
  } catch (err: any) {
    console.error('Failed to load performance:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPerformance()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <PageHeader
      title="My Performance"
      subtitle="Track your performance appraisal cycles, manager evaluations, and leadership goals."
    />

    <LoadingSkeleton v-if="loading" :count="3" type="card" />

    <template v-else-if="performanceData && performanceData.items.length > 0">
      <!-- Top Row: Latest Appraisal Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rating Score Card -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
              <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Overall Evaluation</span>
              <span class="text-xs font-bold text-neu-primary">{{ performanceData.items[0].review_period }}</span>
            </div>

            <div class="my-6 text-center">
              <div class="inline-flex items-baseline justify-center">
                <span class="text-5xl font-black text-neu-primary tracking-tight">
                  {{ Number(performanceData.items[0].rating).toFixed(1) }}
                </span>
                <span class="text-sm text-neu-muted font-bold ml-1.5">/ 5.0</span>
              </div>
              <div class="text-sm font-bold text-emerald-600 mt-2">
                Exceeds Expectations
              </div>
              <div class="text-xs text-neu-muted mt-1">
                Reviewed on {{ formatDate(performanceData.items[0].review_date) }}
              </div>
            </div>
          </div>

          <div class="p-3 rounded-2xl bg-neu-base shadow-neu-inset text-xs space-y-1">
            <div class="flex justify-between text-neu-muted">
              <span>Next Review Cycle:</span>
              <span class="font-bold text-neu-text">2026-Q4</span>
            </div>
          </div>
        </div>

        <!-- Competency Breakdown Card -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 lg:col-span-2 space-y-4">
          <div class="pb-3 border-b border-neu-border/40">
            <h3 class="text-sm font-bold text-neu-text">Competency & Impact Dimensions</h3>
            <p class="text-xs text-neu-muted">Evaluation across key core functional criteria.</p>
          </div>

          <div class="space-y-3.5 text-xs">
            <div>
              <div class="flex justify-between font-bold mb-1">
                <span class="text-neu-text">Technical Excellence & Architecture</span>
                <span class="text-neu-primary">95%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-neu-base shadow-neu-inset overflow-hidden">
                <div class="h-full bg-gradient-to-r from-neu-primary to-blue-500 rounded-full" style="width: 95%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between font-bold mb-1">
                <span class="text-neu-text">System Reliability & Execution</span>
                <span class="text-neu-primary">92%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-neu-base shadow-neu-inset overflow-hidden">
                <div class="h-full bg-gradient-to-r from-neu-primary to-blue-500 rounded-full" style="width: 92%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between font-bold mb-1">
                <span class="text-neu-text">Cross-Functional Collaboration</span>
                <span class="text-emerald-600">90%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-neu-base shadow-neu-inset overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: 90%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between font-bold mb-1">
                <span class="text-neu-text">Mentorship & Initiative</span>
                <span class="text-amber-600">88%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-neu-base shadow-neu-inset overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full" style="width: 88%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Manager Feedback Card -->
      <div v-if="performanceData.items[0].feedback" class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-3">
        <div class="flex items-center space-x-2 pb-2 border-b border-neu-border/40">
          <svg class="w-4 h-4 text-neu-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
          </svg>
          <h3 class="text-sm font-bold text-neu-text">Manager Appraisal Notes</h3>
        </div>
        <p class="text-xs text-neu-text leading-relaxed bg-neu-base shadow-neu-inset p-4 rounded-2xl border border-white/40">
          {{ performanceData.items[0].feedback }}
        </p>
      </div>

      <!-- History Table -->
      <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
          <h3 class="text-sm font-bold text-neu-text">Appraisal History</h3>
          <span class="text-xs text-neu-muted">{{ performanceData.items.length }} cycles completed</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-neu-border/40 text-[11px] font-bold uppercase tracking-wider text-neu-muted">
                <th class="py-3 px-4">Review Cycle</th>
                <th class="py-3 px-4">Review Date</th>
                <th class="py-3 px-4">Score</th>
                <th class="py-3 px-4">Evaluation</th>
                <th class="py-3 px-4">Summary</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/20 text-xs">
              <tr
                v-for="item in performanceData.items"
                :key="item.id"
                class="hover:bg-white/40 transition-colors"
              >
                <td class="py-3.5 px-4 font-bold text-neu-text">{{ item.review_period }}</td>
                <td class="py-3.5 px-4 text-neu-muted">{{ formatDate(item.review_date) }}</td>
                <td class="py-3.5 px-4 font-black text-neu-primary">{{ Number(item.rating).toFixed(1) }} / 5.0</td>
                <td class="py-3.5 px-4 font-semibold text-emerald-600">Exceeds Expectations</td>
                <td class="py-3.5 px-4 text-neu-muted max-w-sm truncate" :title="item.feedback">
                  {{ item.feedback || 'Appraisal completed.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <EmptyState
      v-else
      title="No performance reviews recorded"
      description="Your upcoming quarterly performance reviews will appear here."
    />
  </div>
</template>
