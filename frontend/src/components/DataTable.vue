<script setup lang="ts">
import { ref } from 'vue'

export interface Column {
  key: string
  label: string
  sortable?: boolean
  class?: string
}

const props = defineProps<{
  columns: Column[]
  data: Record<string, any>[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'row-click', row: Record<string, any>): void
  (e: 'sort', key: string, direction: 'asc' | 'desc'): void
}>()

const sortKey = ref<string>('')
const sortDirection = ref<'asc' | 'desc'>('asc')

function handleSort(column: Column) {
  if (!column.sortable) return
  if (sortKey.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = column.key
    sortDirection.value = 'asc'
  }
  emit('sort', sortKey.value, sortDirection.value)
}

function handleRowClick(row: Record<string, any>) {
  emit('row-click', row)
}
</script>

<template>
  <div class="overflow-x-auto bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            :class="[
              'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
              col.sortable ? 'cursor-pointer select-none hover:bg-gray-100' : '',
              col.class || '',
            ]"
            @click="handleSort(col)"
          >
            <div class="flex items-center gap-1">
              {{ col.label }}
              <span v-if="col.sortable && sortKey === col.key" class="text-indigo-600">
                {{ sortDirection === 'asc' ? '&#9650;' : '&#9660;' }}
              </span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-if="loading">
          <td :colspan="columns.length" class="px-6 py-12 text-center text-gray-500">
            <div class="flex items-center justify-center gap-2">
              <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Loading...
            </div>
          </td>
        </tr>
        <tr v-else-if="data.length === 0">
          <td :colspan="columns.length" class="px-6 py-12 text-center text-gray-500">
            No data available
          </td>
        </tr>
        <tr
          v-else
          v-for="(row, index) in data"
          :key="index"
          class="hover:bg-gray-50 cursor-pointer transition-colors"
          @click="handleRowClick(row)"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
          >
            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="$slots.pagination" class="px-6 py-3 bg-gray-50 border-t border-gray-200">
      <slot name="pagination" />
    </div>
  </div>
</template>
