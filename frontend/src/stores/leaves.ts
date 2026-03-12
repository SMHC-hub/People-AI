import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Leave, LeavePrediction } from '@/types'
import * as leavesApi from '@/api/leaves'

export const useLeavesStore = defineStore('leaves', () => {
  const leaves = ref<Leave[]>([])
  const predictions = ref<LeavePrediction[]>([])
  const loading = ref(false)
  const loadingPredictions = ref(false)
  const totalCount = ref(0)
  const currentPage = ref(1)
  const totalPages = ref(1)

  async function fetchLeaves(params?: {
    page?: number
    page_size?: number
    employee_id?: number
    status?: string
    leave_type?: string
  }) {
    loading.value = true
    try {
      const response = await leavesApi.getLeaves(params)
      leaves.value = response.items
      totalCount.value = response.total
      currentPage.value = response.page
      totalPages.value = response.total_pages
    } finally {
      loading.value = false
    }
  }

  async function fetchPredictions() {
    loadingPredictions.value = true
    try {
      predictions.value = await leavesApi.getPredictions()
    } finally {
      loadingPredictions.value = false
    }
  }

  return {
    leaves,
    predictions,
    loading,
    loadingPredictions,
    totalCount,
    currentPage,
    totalPages,
    fetchLeaves,
    fetchPredictions,
  }
})
