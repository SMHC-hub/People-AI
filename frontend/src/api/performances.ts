import client from './client'
import type { PerformanceReview, PerformanceStats, PaginatedResponse } from '@/types'

export async function getPerformances(params?: {
  page?: number
  page_size?: number
  review_period?: string
  promotion_recommended?: boolean
}): Promise<PaginatedResponse<PerformanceReview>> {
  const { data } = await client.get<PaginatedResponse<PerformanceReview>>('/performances', { params })
  return data
}

export async function getPerformanceStats(review_period?: string): Promise<PerformanceStats> {
  const { data } = await client.get<PerformanceStats>('/performances/stats', {
    params: review_period ? { review_period } : {},
  })
  return data
}
