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
  const title = payload.title?.trim() || `${payload.type.charAt(0).toUpperCase() + payload.type.slice(1)} Report - ${new Date().toLocaleDateString()}`
  const body = {
    type: payload.type,
    title,
    parameters: {
      date_from: payload.date_range_start || undefined,
      date_to: payload.date_range_end || undefined,
    },
  }
  const { data } = await client.post<Report>('/reports/generate', body)
  return data
}

export async function downloadReport(id: number): Promise<Blob> {
  const { data } = await client.get<Blob>(`/reports/${id}/download`, {
    responseType: 'blob',
  })
  return data
}
