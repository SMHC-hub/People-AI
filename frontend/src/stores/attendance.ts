import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Attendance, AttendanceStats } from '@/types'
import * as attendanceApi from '@/api/attendance'

export const useAttendanceStore = defineStore('attendance', () => {
  const records = ref<Attendance[]>([])
  const anomalies = ref<Attendance[]>([])
  const stats = ref<AttendanceStats | null>(null)
  const loading = ref(false)
  const loadingStats = ref(false)
  const loadingAnomalies = ref(false)
  const error = ref<string | null>(null)
  const totalCount = ref(0)
  const currentPage = ref(1)
  const totalPages = ref(1)

  async function fetchRecords(params?: {
    page?: number
    page_size?: number
    employee_id?: number
    date_from?: string
    date_to?: string
    status?: string
    is_anomaly?: boolean
    anomalies_only?: boolean
  }) {
    loading.value = true
    error.value = null
    try {
      const response: any = await attendanceApi.getAttendance(params)
      records.value = response.items || response.data || (Array.isArray(response) ? response : [])
      totalCount.value = response.total ?? records.value.length
      currentPage.value = response.page ?? response.current_page ?? 1
      totalPages.value = response.total_pages ?? response.last_page ?? 1
    } catch (err) {
      error.value = "Couldn't load attendance records."
      records.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchAnomalies(params?: { date_from?: string; date_to?: string }) {
    loadingAnomalies.value = true
    try {
      const response: any = await attendanceApi.getAnomalies(params)
      anomalies.value = Array.isArray(response) ? response : (response?.items || response?.data || [])
    } catch {
      anomalies.value = []
    } finally {
      loadingAnomalies.value = false
    }
  }

  async function fetchStats(params?: { date_from?: string; date_to?: string }) {
    loadingStats.value = true
    try {
      stats.value = await attendanceApi.getStats(params)
    } finally {
      loadingStats.value = false
    }
  }

  return {
    records,
    anomalies,
    stats,
    loading,
    loadingStats,
    loadingAnomalies,
    error,
    totalCount,
    currentPage,
    totalPages,
    fetchRecords,
    fetchAnomalies,
    fetchStats,
  }
})
