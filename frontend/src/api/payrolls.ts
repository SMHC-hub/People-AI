import client from './client'
import type { Payroll, PayrollStats, PaginatedResponse } from '@/types'

export async function getPayrolls(params?: {
  page?: number
  page_size?: number
  month?: string
  is_anomaly?: boolean
  review_status?: string
}): Promise<PaginatedResponse<Payroll>> {
  const { data } = await client.get<PaginatedResponse<Payroll>>('/payrolls', { params })
  return data
}

export async function getPayrollStats(month?: string): Promise<PayrollStats> {
  const { data } = await client.get<PayrollStats>('/payrolls/stats', {
    params: month ? { month } : {},
  })
  return data
}

export async function getPayrollAnomalies(month?: string): Promise<Payroll[]> {
  const { data } = await client.get<Payroll[]>('/payrolls/anomalies', {
    params: month ? { month } : {},
  })
  return data
}

export async function reviewPayrollAnomaly(
  id: number,
  review_status: 'reviewed' | 'escalated',
  notes?: string
): Promise<Payroll> {
  const { data } = await client.post<Payroll>(`/payrolls/${id}/review`, {
    review_status,
    notes,
  })
  return data
}
