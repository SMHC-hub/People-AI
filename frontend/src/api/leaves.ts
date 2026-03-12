import client from './client'
import type { Leave, LeavePrediction, PaginatedResponse } from '@/types'

export async function getLeaves(params?: {
  page?: number
  page_size?: number
  employee_id?: number
  status?: string
  leave_type?: string
}): Promise<PaginatedResponse<Leave>> {
  const { data } = await client.get<PaginatedResponse<Leave>>('/leaves', { params })
  return data
}

export async function createLeave(payload: {
  leave_type: string
  start_date: string
  end_date: string
  reason: string
}): Promise<Leave> {
  const { data } = await client.post<Leave>('/leaves', payload)
  return data
}

export async function approveLeave(id: number): Promise<Leave> {
  const { data } = await client.post<Leave>(`/leaves/${id}/approve`)
  return data
}

export async function rejectLeave(id: number): Promise<Leave> {
  const { data } = await client.post<Leave>(`/leaves/${id}/reject`)
  return data
}

export async function getPredictions(): Promise<LeavePrediction[]> {
  const { data } = await client.get<LeavePrediction[]>('/leaves/predictions')
  return data
}
