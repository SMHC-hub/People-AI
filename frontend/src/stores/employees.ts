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
      const response: PaginatedResponse<Employee> = await employeesApi.getEmployees(params)
      employees.value = response.items
      totalCount.value = response.total
      currentPage.value = response.page
      totalPages.value = response.total_pages
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
