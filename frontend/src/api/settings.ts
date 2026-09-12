import client from './client'
import type { Organization } from '@/types'

export interface SettingsData {
  organization: Organization
  active_users_count: number
  ml_status: {
    service_healthy: boolean
    active_model: string
    groq_llm_connected: boolean
    last_drift_check: string
  }
}

export async function getSettings(): Promise<SettingsData> {
  const { data } = await client.get<SettingsData>('/settings')
  return data
}

export async function updateSettings(payload: Partial<Organization>): Promise<{ message: string; organization: Organization }> {
  const { data } = await client.put<{ message: string; organization: Organization }>('/settings', payload)
  return data
}
