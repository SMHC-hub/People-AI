import client from './client'
import type {
  PortalDashboardData,
  Employee,
  Attendance,
  Leave,
  LeaveBalances,
  Payroll,
  PerformanceReview,
  PaginatedResponse,
} from '@/types'

export async function getPortalDashboard(): Promise<PortalDashboardData> {
  const { data } = await client.get<PortalDashboardData>('/portal/dashboard')
  return data
}

export async function getPortalProfile(): Promise<Employee> {
  const { data } = await client.get<Employee>('/portal/profile')
  return data
}

export async function getPortalAttendance(params?: {
  page?: number
  per_page?: number
  date_from?: string
  date_to?: string
}): Promise<PaginatedResponse<Attendance>> {
  const { data } = await client.get<PaginatedResponse<Attendance>>('/portal/attendance', { params })
  return data
}

export async function portalCheckIn(): Promise<Attendance> {
  const { data } = await client.post<Attendance>('/portal/check-in')
  return data
}

export async function portalCheckOut(): Promise<Attendance> {
  const { data } = await client.post<Attendance>('/portal/check-out')
  return data
}

export async function getPortalLeaves(params?: {
  page?: number
  per_page?: number
}): Promise<{ balances: LeaveBalances; leaves: PaginatedResponse<Leave> }> {
  const { data } = await client.get<{ balances: LeaveBalances; leaves: PaginatedResponse<Leave> }>('/portal/leaves', { params })
  return data
}

export async function submitPortalLeave(payload: {
  type: string
  start_date: string
  end_date: string
  reason: string
}): Promise<Leave> {
  const { data } = await client.post<Leave>('/portal/leaves', payload)
  return data
}

export async function getPortalPayrolls(params?: {
  page?: number
  per_page?: number
}): Promise<PaginatedResponse<Payroll>> {
  const { data } = await client.get<PaginatedResponse<Payroll>>('/portal/payrolls', { params })
  return data
}

export async function getPortalPerformances(params?: {
  page?: number
  per_page?: number
}): Promise<PaginatedResponse<PerformanceReview>> {
  const { data } = await client.get<PaginatedResponse<PerformanceReview>>('/portal/performances', { params })
  return data
}
