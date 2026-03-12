<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useEmployeesStore } from '@/stores/employees'
import DataTable from '@/components/DataTable.vue'
import type { Column } from '@/components/DataTable.vue'

const router = useRouter()
const store = useEmployeesStore()

const search = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')
const page = ref(1)
const pageSize = 10

const columns: Column[] = [
  { key: 'employee_id', label: 'ID', sortable: true },
  { key: 'full_name', label: 'Name', sortable: true },
  { key: 'department', label: 'Department', sortable: true },
  { key: 'position', label: 'Position', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'hire_date', label: 'Hire Date', sortable: true },
]

const departments = [
  'Engineering',
  'Sales',
  'Marketing',
  'HR',
  'Finance',
  'Operations',
]

const statuses = ['active', 'inactive', 'on_leave', 'terminated']

const tableData = ref<Record<string, any>[]>([])

async function fetchData() {
  await store.fetchEmployees({
    page: page.value,
    page_size: pageSize,
    search: search.value || undefined,
    department: departmentFilter.value || undefined,
    status: statusFilter.value || undefined,
  })
  tableData.value = store.employees.map((emp) => ({
    ...emp,
    full_name: `${emp.first_name} ${emp.last_name}`,
  }))
}

function handleRowClick(row: Record<string, any>) {
  router.push({ name: 'employee-detail', params: { id: row.id } })
}

function handleSort(key: string, direction: 'asc' | 'desc') {
  // Sorting would be handled by API in production
  console.log('Sort by', key, direction)
}

function goToPage(p: number) {
  page.value = p
}

function getStatusClass(status: string): string {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'inactive':
      return 'bg-gray-100 text-gray-800'
    case 'on_leave':
      return 'bg-yellow-100 text-yellow-800'
    case 'terminated':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

watch([search, departmentFilter, statusFilter], () => {
  page.value = 1
  fetchData()
})

watch(page, fetchData)

onMounted(fetchData)
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
      <button
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Employee
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4">
      <div class="flex-1">
        <input
          v-model="search"
          type="text"
          placeholder="Search employees..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
        />
      </div>
      <select
        v-model="departmentFilter"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
      >
        <option value="">All Departments</option>
        <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
      </select>
      <select
        v-model="statusFilter"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
      >
        <option value="">All Statuses</option>
        <option v-for="s in statuses" :key="s" :value="s">
          {{ s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') }}
        </option>
      </select>
    </div>

    <!-- Table -->
    <DataTable
      :columns="columns"
      :data="tableData"
      :loading="store.loading"
      @row-click="handleRowClick"
      @sort="handleSort"
    >
      <template #cell-status="{ value }">
        <span
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            getStatusClass(value),
          ]"
        >
          {{ value?.charAt(0).toUpperCase() + value?.slice(1).replace('_', ' ') }}
        </span>
      </template>

      <template #pagination>
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-700">
            Showing page <span class="font-medium">{{ store.currentPage }}</span> of
            <span class="font-medium">{{ store.totalPages }}</span>
            ({{ store.totalCount }} total)
          </p>
          <div class="flex gap-2">
            <button
              :disabled="page <= 1"
              @click="goToPage(page - 1)"
              class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              :disabled="page >= store.totalPages"
              @click="goToPage(page + 1)"
              class="px-3 py-1 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </template>
    </DataTable>
  </div>
</template>
