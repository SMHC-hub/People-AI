import client from './client'
import type { Report, PaginatedResponse } from '@/types'

export async function getReports(params?: {
  page?: number
  page_size?: number
  type?: string
}): Promise<PaginatedResponse<Report>> {
  const { data } = await client.get<PaginatedResponse<Report>>('/reports', { params })
  return data
}

export async function generateReport(payload: {
  type: string
  date_range_start: string
  date_range_end: string
  title?: string
}): Promise<Report> {
  const { data } = await client.post<Report>('/reports/generate', payload)
  return data
}

export async function downloadReport(id: number): Promise<Blob> {
  const { data } = await client.get<Blob>(`/reports/${id}/download`, {
    responseType: 'blob',
  })
  return data
}
