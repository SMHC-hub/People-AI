import client from './client'
import type { WorkforceStats, DepartmentRisk, WorkforceInsight } from '@/types'

export async function getWorkforceStats(): Promise<WorkforceStats> {
  const { data } = await client.get<WorkforceStats>('/workforce/stats')
  return data
}

export async function getWorkforceHeatmap(): Promise<DepartmentRisk[]> {
  const { data } = await client.get<DepartmentRisk[]>('/workforce/heatmap')
  return data
}

export async function getWorkforceInsights(): Promise<WorkforceInsight[]> {
  const { data } = await client.get<WorkforceInsight[]>('/workforce/insights')
  return data
}
