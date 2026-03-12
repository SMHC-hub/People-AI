import client from './client'
import type { Employee, PaginatedResponse, RiskScore } from '@/types'

export async function getEmployees(params?: {
  page?: number
  page_size?: number
  search?: string
  department?: string
  status?: string
}): Promise<PaginatedResponse<Employee>> {
  const { data } = await client.get<PaginatedResponse<Employee>>('/employees', { params })
  return data
}

export async function getEmployee(id: number): Promise<Employee> {
  const { data } = await client.get<Employee>(`/employees/${id}`)
  return data
}

export async function createEmployee(payload: Partial<Employee>): Promise<Employee> {
  const { data } = await client.post<Employee>('/employees', payload)
  return data
}

export async function updateEmployee(id: number, payload: Partial<Employee>): Promise<Employee> {
  const { data } = await client.put<Employee>(`/employees/${id}`, payload)
  return data
}

export async function deleteEmployee(id: number): Promise<void> {
  await client.delete(`/employees/${id}`)
}

export async function getRiskScore(employeeId: number): Promise<RiskScore> {
  const { data } = await client.get<RiskScore>(`/employees/${employeeId}/risk-score`)
  return data
}
