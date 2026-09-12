import client from './client'
import type { AuditLog, PaginatedResponse } from '@/types'

export async function getAuditLogs(params?: {
  page?: number
  page_size?: number
  action?: string
  entity_type?: string
}): Promise<PaginatedResponse<AuditLog>> {
  const { data } = await client.get<PaginatedResponse<AuditLog>>('/audit-logs', { params })
  return data
}
