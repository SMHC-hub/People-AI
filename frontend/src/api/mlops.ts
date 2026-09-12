import client from './client'
import type { ModelVersion } from '@/types'

export interface MLOpsDashboardData {
  models: ModelVersion[]
  drift_status: {
    overall_status: string
    calculated_at: string
    features: Record<string, {
      psi: number
      status: string
      baseline_mean: number
      current_mean: number
    }>
  }
  benchmark_metrics: {
    dataset_records: number
    feature_count: number
    comparison: {
      model: string
      roc_auc: number
      f1_score: number
      precision: number
      recall: number
      latency_ms: number
    }[]
  }
}

export async function getModels(): Promise<ModelVersion[]> {
  const { data } = await client.get<ModelVersion[]>('/mlops/models')
  return data
}

export async function getMLOpsMetrics(): Promise<MLOpsDashboardData> {
  const { data } = await client.get<MLOpsDashboardData>('/mlops/metrics')
  return data
}

export async function triggerRetraining(model_name: string): Promise<{ success: boolean; message: string; task_id?: string }> {
  const { data } = await client.post<{ success: boolean; message: string; task_id?: string }>('/mlops/retrain', {
    model_name,
  })
  return data
}

export async function promoteModel(id: number): Promise<ModelVersion> {
  const { data } = await client.post<ModelVersion>(`/mlops/models/${id}/promote`)
  return data
}
