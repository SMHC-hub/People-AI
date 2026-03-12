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
  }) {
    loading.value = true
    try {
      const response = await attendanceApi.getAttendance(params)
      records.value = response.items
      totalCount.value = response.total
      currentPage.value = response.page
      totalPages.value = response.total_pages
    } finally {
      loading.value = false
    }
  }

  async function fetchAnomalies(params?: { date_from?: string; date_to?: string }) {
    loadingAnomalies.value = true
    try {
      anomalies.value = await attendanceApi.getAnomalies(params)
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
    totalCount,
    currentPage,
    totalPages,
    fetchRecords,
    fetchAnomalies,
    fetchStats,
  }
})
