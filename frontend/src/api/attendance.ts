import client from './client'
import type { Attendance, AttendanceStats, PaginatedResponse } from '@/types'

export async function getAttendance(params?: {
  page?: number
  page_size?: number
  employee_id?: number
  date_from?: string
  date_to?: string
  status?: string
}): Promise<PaginatedResponse<Attendance>> {
  const { data } = await client.get<PaginatedResponse<Attendance>>('/attendance', { params })
  return data
}

export async function checkIn(employeeId: number): Promise<Attendance> {
  const { data } = await client.post<Attendance>('/attendance/check-in', {
    employee_id: employeeId,
  })
  return data
}

export async function checkOut(employeeId: number): Promise<Attendance> {
  const { data } = await client.post<Attendance>('/attendance/check-out', {
    employee_id: employeeId,
  })
  return data
}

export async function getAnomalies(params?: {
  date_from?: string
  date_to?: string
}): Promise<Attendance[]> {
  const { data } = await client.get<Attendance[]>('/attendance/anomalies', { params })
  return data
}

export async function getStats(params?: {
  date_from?: string
  date_to?: string
}): Promise<AttendanceStats> {
  const { data } = await client.get<AttendanceStats>('/attendance/stats', { params })
  return data
}

export const getAttendanceStats = getStats

