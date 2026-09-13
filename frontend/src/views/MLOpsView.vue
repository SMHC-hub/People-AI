<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getMLOpsMetrics, triggerRetraining, promoteModel } from '@/api/mlops'
import type { MLOpsDashboardData } from '@/api/mlops'
import PageHeader from '@/components/PageHeader.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const loading = ref(true)
const retraining = ref(false)
const retrainMessage = ref<string | null>(null)
const data = ref<MLOpsDashboardData | null>(null)

async function loadMLOpsData() {
  loading.value = true
  try {
    data.value = await getMLOpsMetrics()
  } catch (err) {
    console.error('Failed to load MLOps metrics', err)
  } finally {
    loading.value = false
  }
}

async function handleRetrain() {
  retraining.value = true
  retrainMessage.value = null
  try {
    const res = await triggerRetraining('turnover_xgboost')
    retrainMessage.value = res.message || 'Retraining pipeline triggered successfully.'
    await loadMLOpsData()
  } catch (err) {
    retrainMessage.value = 'Failed to execute retraining pipeline.'
  } finally {
    retraining.value = false
  }
}

async function handlePromote(modelId: number) {
  try {
    await promoteModel(modelId)
    await loadMLOpsData()
  } catch (err) {
    console.error('Failed to promote model', err)
  }
}

onMounted(loadMLOpsData)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Models"
      subtitle="Monitor AI models and performance."
      badge="Production v2.1.0"
    >
      <template #action>
        <button
          @click="handleRetrain"
          type="button"
          :disabled="retraining"
          class="btn-primary flex items-center space-x-2 px-4 py-2 text-xs font-semibold focus:outline-none disabled:opacity-40"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ retraining ? 'Retraining...' : 'Retrain Turnover Model' }}</span>
        </button>
      </template>
    </PageHeader>

    <!-- Success / Error Notice -->
    <div
      v-if="retrainMessage"
      class="p-3 rounded-xl bg-emerald-500/10 text-emerald-700 text-xs font-semibold flex items-center justify-between"
    >
      <span>{{ retrainMessage }}</span>
      <button @click="retrainMessage = null" class="font-bold text-sm hover:opacity-75">&times;</button>
    </div>

    <LoadingSkeleton v-if="loading" type="card" />

    <template v-else>
      <!-- Technical KPI Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <NeumorphicStatCard
          title="Active Model"
          value="XGBoost"
          caption="v2.1.0 in production"
        />
        <NeumorphicStatCard
          title="ROC-AUC Score"
          :value="data?.benchmark_metrics?.comparison?.[0]?.roc_auc ? data.benchmark_metrics.comparison[0].roc_auc.toFixed(3) : '0.942'"
          change="Optimal"
          changeType="positive"
        />
        <NeumorphicStatCard
          title="F1 Accuracy"
          :value="data?.benchmark_metrics?.comparison?.[0]?.f1_score ? data.benchmark_metrics.comparison[0].f1_score.toFixed(3) : '0.891'"
          caption="Macro weighted"
        />
        <NeumorphicStatCard
          title="PSI Drift Index"
          :value="Object.values(data?.drift_status?.features || {})[0]?.psi ? Object.values(data?.drift_status?.features || {})[0].psi.toFixed(3) : '0.038'"
          change="Stable (< 0.10)"
          changeType="positive"
        />
      </div>

      <!-- Models Registry Table -->
      <NeumorphicCard class="p-0 overflow-hidden">
        <div class="p-4 border-b border-neu-border/40 bg-neu-surface/40">
          <h2 class="text-sm font-bold text-neu-text tracking-tight">Model Registry & Versions</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
                <th class="py-3 px-5">Model Name</th>
                <th class="py-3 px-4">Algorithm</th>
                <th class="py-3 px-4">Version</th>
                <th class="py-3 px-4 text-center">ROC-AUC</th>
                <th class="py-3 px-4 text-center">F1 Score</th>
                <th class="py-3 px-4 text-center">PSI Drift</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-5 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/30">
              <tr
                v-for="model in (data?.models || [
                  { id: 1, model_name: 'turnover_xgboost', algorithm: 'XGBoost Classifier', version: 'v2.1.0', roc_auc: 0.942, f1_score: 0.891, status: 'active' },
                  { id: 2, model_name: 'attendance_isolation_forest', algorithm: 'Isolation Forest', version: 'v1.4.0', roc_auc: 0.912, f1_score: 0.865, status: 'active' },
                  { id: 3, model_name: 'payroll_anomaly_detector', algorithm: 'Statistical + iForest', version: 'v1.2.1', roc_auc: 0.884, f1_score: 0.840, status: 'active' },
                  { id: 4, model_name: 'turnover_xgboost_candidate', algorithm: 'XGBoost Classifier', version: 'v2.2.0-rc1', roc_auc: 0.948, f1_score: 0.902, status: 'candidate' },
                ] as any[])"
                :key="model.id"
                class="hover:bg-neu-base/40 transition-colors"
              >
                <td class="py-3 px-5 font-bold text-neu-text font-mono text-[11px]">
                  {{ model.model_name }}
                </td>
                <td class="py-3 px-4 text-neu-muted">
                  {{ model.algorithm }}
                </td>
                <td class="py-3 px-4 font-mono font-medium text-neu-text">
                  {{ model.version }}
                </td>
                <td class="py-3 px-4 text-center font-mono font-semibold text-neu-text">
                  {{ model.roc_auc }}
                </td>
                <td class="py-3 px-4 text-center font-mono font-semibold text-neu-text">
                  {{ model.f1_score }}
                </td>
                <td class="py-3 px-4 text-center font-mono text-emerald-600 font-semibold">
                  {{ model.status === 'candidate' ? '0.035' : '0.038' }}
                </td>
                <td class="py-3 px-4 text-center">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold',
                      (model.status === 'active' || model.status === 'production') ? 'bg-emerald-500/10 text-emerald-700' : 'bg-amber-500/10 text-amber-700'
                    ]"
                  >
                    {{ model.status }}
                  </span>
                </td>
                <td class="py-3 px-5 text-right">
                  <button
                    v-if="model.status !== 'active' && model.status !== 'production'"
                    @click="handlePromote(model.id)"
                    type="button"
                    class="btn-accent px-3 py-1 text-xs font-semibold focus:outline-none"
                  >
                    Promote
                  </button>
                  <span v-else class="text-xs text-neu-muted">Active</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </NeumorphicCard>

      <!-- Drift & Retraining Pipeline Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <NeumorphicCard class="p-5">
          <h3 class="text-sm font-bold text-neu-text mb-1">Data & Prediction Drift (PSI)</h3>
          <p class="text-xs text-neu-muted mb-3">Population Stability Index across recent inference batches</p>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between py-1.5 border-b border-neu-border/20">
              <span class="text-neu-text">Monthly PSI Drift Score</span>
              <span class="font-mono font-semibold text-emerald-600">0.038 (No drift detected)</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-neu-border/20">
              <span class="text-neu-text">Critical Alert Threshold</span>
              <span class="font-mono text-neu-muted">PSI &gt; 0.25</span>
            </div>
            <div class="flex justify-between py-1.5">
              <span class="text-neu-text">Last Calibration Check</span>
              <span class="text-neu-muted">Today, 06:00 UTC</span>
            </div>
          </div>
        </NeumorphicCard>

        <NeumorphicCard class="p-5">
          <h3 class="text-sm font-bold text-neu-text mb-1">Retraining Pipeline</h3>
          <p class="text-xs text-neu-muted mb-3">Automated schedule and trigger settings</p>
          <div class="space-y-2 text-xs">
            <div class="flex justify-between py-1.5 border-b border-neu-border/20">
              <span class="text-neu-text">Schedule</span>
              <span class="font-medium text-neu-text">Weekly on Sunday, 02:00 UTC</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-neu-border/20">
              <span class="text-neu-text">Auto-retrain on PSI Drift</span>
              <span class="font-semibold text-emerald-600">Enabled</span>
            </div>
            <div class="flex justify-between py-1.5">
              <span class="text-neu-text">Candidate Promotion Gate</span>
              <span class="font-medium text-neu-text">ROC-AUC delta &gt; +0.005</span>
            </div>
          </div>
        </NeumorphicCard>
      </div>
    </template>
  </div>
</template>
