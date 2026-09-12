import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Employee, PaginatedResponse } from '@/types'
import * as employeesApi from '@/api/employees'

export const useEmployeesStore = defineStore('employees', () => {
  const employees = ref<Employee[]>([])
  const currentEmployee = ref<Employee | null>(null)
  const loading = ref(false)
  const totalCount = ref(0)
  const currentPage = ref(1)
  const totalPages = ref(1)

  async function fetchEmployees(params?: {
    page?: number
    page_size?: number
    search?: string
    department?: string
    status?: string
  }) {
    loading.value = true
    try {
      const response: any = await employeesApi.getEmployees(params)
      employees.value = response.items || response.data || (Array.isArray(response) ? response : [])
      totalCount.value = response.total ?? employees.value.length
      currentPage.value = response.page ?? response.current_page ?? 1
      totalPages.value = response.total_pages ?? response.last_page ?? 1
    } finally {
      loading.value = false
    }
  }

  async function fetchEmployee(id: number) {
    loading.value = true
    try {
      currentEmployee.value = await employeesApi.getEmployee(id)
    } finally {
      loading.value = false
    }
  }

  return {
    employees,
    currentEmployee,
    loading,
    totalCount,
    currentPage,
    totalPages,
    fetchEmployees,
    fetchEmployee,
  }
})
