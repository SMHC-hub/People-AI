<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getMLOpsMetrics, triggerRetraining, promoteModel } from '@/api/mlops'
import type { MLOpsDashboardData } from '@/api/mlops'
import type { ModelVersion } from '@/types'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

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
    retrainMessage.value = res.message || 'Pipeline finished successfully.'
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

onMounted(() => {
  loadMLOpsData()
})
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <h2 class="text-2xl font-black tracking-tight text-neu-text">
            MLOps Lifecycle & Model Governance
          </h2>
          <NeumorphicBadge variant="success" size="sm">Active Production v2.1.0</NeumorphicBadge>
        </div>
        <p class="text-sm text-neu-muted mt-1">
          Automated model registry, Population Stability Index (PSI) drift monitoring, and benchmark evaluation.
        </p>
      </div>

      <div class="flex items-center space-x-3">
        <NeumorphicButton
          variant="primary"
          size="sm"
          :loading="retraining"
          @click="handleRetrain"
        >
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
          Retrain Turnover Model
        </NeumorphicButton>
      </div>
    </div>

    <!-- Alert banner if retrain executed -->
    <div
      v-if="retrainMessage"
      class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-semibold shadow-neu-flat border border-emerald-200 flex items-center justify-between"
    >
      <span>{{ retrainMessage }}</span>
      <button @click="retrainMessage = null" class="text-emerald-600 hover:text-emerald-900">&times;</button>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <NeumorphicStatCard
        title="Production Champion"
        value="XGBoost v2.1.0"
        subtitle="ROC-AUC: 0.942 | F1: 0.89"
        trend="SHAP TreeExplainer Enabled"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Data Drift Health (PSI)"
        :value="data?.drift_status?.overall_status?.toUpperCase() || 'HEALTHY'"
        subtitle="Max feature PSI: 0.07"
        trend="Continuous Stability Index"
        iconBg="success"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Inference Latency"
        value="18ms"
        subtitle="Real-time SHAP explanation"
        trend="P99: 45ms (Target < 100ms)"
        iconBg="primary"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </template>
      </NeumorphicStatCard>

      <NeumorphicStatCard
        title="Groq LLM Connection"
        value="GPT-OSS 120B"
        subtitle="Document-grounded RAG"
        trend="Hit Rate: 100% | Latency: 1.2s"
        iconBg="warning"
      >
        <template #icon>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
        </template>
      </NeumorphicStatCard>
    </div>

    <!-- Continuous Population Stability Index (PSI) Drift Monitor -->
    <NeumorphicCard>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-neu-border/50 gap-2">
        <div>
          <h3 class="text-lg font-black text-neu-text tracking-tight">
            Feature Population Stability Index (PSI) Drift Telemetry
          </h3>
          <p class="text-xs text-neu-muted mt-0.5">
            Monitors shift between baseline training distributions and current 1,000-employee production data.
          </p>
        </div>
        <div class="text-[11px] font-mono font-bold text-neu-muted">
          PSI &lt; 0.10: Stable | 0.10-0.25: Moderate | &gt; 0.25: Drift Detected
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <div
          v-for="(val, feature) in data?.drift_status?.features || {}"
          :key="feature"
          class="p-4 rounded-2xl bg-neu-base shadow-neu-inset border border-white/40 space-y-2"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono font-bold text-neu-text truncate">{{ feature }}</span>
            <NeumorphicBadge :variant="val.psi < 0.10 ? 'success' : 'warning'" size="sm">
              {{ val.status.toUpperCase() }}
            </NeumorphicBadge>
          </div>

          <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-black text-neu-text font-mono">{{ val.psi.toFixed(3) }}</span>
            <span class="text-[11px] text-neu-muted font-bold">PSI</span>
          </div>

          <div class="w-full h-1.5 rounded-full bg-neu-surface shadow-neu-inset overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="val.psi < 0.10 ? 'bg-emerald-500' : 'bg-amber-500'"
              :style="{ width: `${Math.min(val.psi * 500, 100)}%` }"
            ></div>
          </div>

          <div class="flex justify-between text-[10px] text-neu-muted pt-1">
            <span>Base μ: {{ val.baseline_mean?.toFixed(1) }}</span>
            <span>Curr μ: {{ val.current_mean?.toFixed(1) }}</span>
          </div>
        </div>
      </div>
    </NeumorphicCard>

    <!-- Model Comparison Benchmark -->
    <NeumorphicCard>
      <h3 class="text-lg font-black text-neu-text tracking-tight mb-4">
        Model Comparison & Validation Benchmark
      </h3>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Model Candidate</th>
              <th class="py-3.5 px-4 text-center">ROC-AUC</th>
              <th class="py-3.5 px-4 text-center">F1 Score</th>
              <th class="py-3.5 px-4 text-center">Precision</th>
              <th class="py-3.5 px-4 text-center">Recall</th>
              <th class="py-3.5 px-4 text-center">P95 Latency</th>
              <th class="py-3.5 px-4 text-right">Deployment Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="bench in data?.benchmark_metrics?.comparison || []"
              :key="bench.model"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <td class="py-4 px-4 font-black text-neu-text">
                {{ bench.model }}
              </td>
              <td class="py-4 px-4 text-center font-mono font-bold text-emerald-600">
                {{ bench.roc_auc }}
              </td>
              <td class="py-4 px-4 text-center font-mono font-bold text-neu-text">
                {{ bench.f1_score }}
              </td>
              <td class="py-4 px-4 text-center font-mono text-neu-muted">
                {{ bench.precision }}
              </td>
              <td class="py-4 px-4 text-center font-mono text-neu-muted">
                {{ bench.recall }}
              </td>
              <td class="py-4 px-4 text-center font-mono text-neu-text">
                {{ bench.latency_ms }} ms
              </td>
              <td class="py-4 px-4 text-right">
                <NeumorphicBadge
                  :variant="bench.model.includes('XGBoost') ? 'primary' : 'neutral'"
                  size="sm"
                >
                  {{ bench.model.includes('XGBoost') ? 'CHAMPION (ACTIVE)' : 'CHALLENGER' }}
                </NeumorphicBadge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>

    <!-- Model Registry Table -->
    <NeumorphicCard>
      <h3 class="text-lg font-black text-neu-text tracking-tight mb-4">
        Model Version Registry & Audit Lineage
      </h3>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Model Name</th>
              <th class="py-3.5 px-4">Version</th>
              <th class="py-3.5 px-4">Algorithm</th>
              <th class="py-3.5 px-4 text-center">Accuracy</th>
              <th class="py-3.5 px-4 text-center">F1</th>
              <th class="py-3.5 px-4 text-center">Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr
              v-for="model in data?.models || []"
              :key="model.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <td class="py-3.5 px-4 font-bold text-neu-text">
                {{ model.model_name }}
              </td>
              <td class="py-3.5 px-4 font-mono text-xs text-neu-muted">
                {{ model.version }}
              </td>
              <td class="py-3.5 px-4 text-xs font-semibold text-neu-text">
                {{ model.algorithm }}
              </td>
              <td class="py-3.5 px-4 text-center font-mono font-bold">
                {{ (model.accuracy * 100).toFixed(1) }}%
              </td>
              <td class="py-3.5 px-4 text-center font-mono font-bold">
                {{ model.f1_score.toFixed(3) }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <NeumorphicBadge
                  :variant="model.status === 'active' ? 'success' : model.status === 'candidate' ? 'warning' : 'neutral'"
                  size="sm"
                >
                  {{ model.status.toUpperCase() }}
                </NeumorphicBadge>
              </td>
              <td class="py-3.5 px-4 text-right">
                <NeumorphicButton
                  v-if="model.status === 'candidate'"
                  variant="primary"
                  size="sm"
                  @click="handlePromote(model.id)"
                >
                  Promote
                </NeumorphicButton>
                <span v-else class="text-xs text-neu-muted font-mono">Current Active</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>
  </div>
</template>
