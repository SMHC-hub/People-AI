<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getPortalProfile } from '@/api/portal'
import type { Employee } from '@/types'
import { formatDate } from '@/utils/formatters'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const loading = ref(true)
const profile = ref<Employee | null>(null)

async function fetchProfile() {
  loading.value = true
  try {
    profile.value = await getPortalProfile()
  } catch (err: any) {
    console.error('Failed to load profile:', err)
  } finally {
    loading.value = false
  }
}

function getDepartmentName(dep: any): string {
  if (!dep) return 'Engineering'
  return typeof dep === 'object' ? dep.name || 'Engineering' : String(dep)
}

function getPositionTitle(pos: any): string {
  if (!pos) return 'Staff Engineer'
  return typeof pos === 'object' ? pos.title || 'Staff Engineer' : String(pos)
}

onMounted(() => {
  fetchProfile()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <PageHeader
      title="My Profile"
      subtitle="View your employee credentials, job title, and organization assignments."
    />

    <LoadingSkeleton v-if="loading" :count="2" type="card" />

    <template v-else-if="profile">
      <!-- Profile Header Hero -->
      <div class="neu-card p-8 shadow-neu-flat border border-white/60 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-neu-primary to-blue-600 shadow-neu-flat-md flex items-center justify-center text-white font-black text-2xl select-none">
          {{ profile.first_name?.charAt(0) }}{{ profile.last_name?.charAt(0) }}
        </div>

        <div class="space-y-2 text-center sm:text-left flex-1">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <h2 class="text-2xl font-black text-neu-text tracking-tight">
                {{ profile.first_name }} {{ profile.last_name }}
              </h2>
              <p class="text-xs font-semibold text-neu-primary mt-0.5">
                {{ getPositionTitle(profile.position) }} &bull; {{ getDepartmentName(profile.department) }}
              </p>
            </div>
            <div>
              <StatusBadge :status="profile.status || 'active'" />
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 pt-2 text-xs text-neu-muted">
            <span class="flex items-center space-x-1.5 font-medium">
              <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
              </svg>
              <span>Employee ID: <strong class="text-neu-text">{{ profile.employee_code || profile.employee_id }}</strong></span>
            </span>
            <span class="flex items-center space-x-1.5 font-medium">
              <svg class="w-3.5 h-3.5 text-neu-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Joined: <strong class="text-neu-text">{{ formatDate(profile.hire_date) }}</strong></span>
            </span>
          </div>
        </div>
      </div>

      <!-- Information Grids -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Organization & Role -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
          <div class="pb-3 border-b border-neu-border/40">
            <h3 class="text-sm font-bold text-neu-text">Organizational Details</h3>
            <p class="text-xs text-neu-muted">Internal hierarchy and role placement.</p>
          </div>

          <div class="space-y-3 text-xs">
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Department:</span>
              <span class="font-bold text-neu-text">{{ getDepartmentName(profile.department) }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Job Title:</span>
              <span class="font-bold text-neu-text">{{ getPositionTitle(profile.position) }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Position Level:</span>
              <span class="font-bold text-neu-text">{{ (typeof profile.position === 'object' && profile.position?.level) || 'L6 - Principal/Staff' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Location / Branch:</span>
              <span class="font-bold text-neu-text">{{ profile.location?.name || 'San Francisco HQ (Main Campus)' }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-neu-muted">Employment Type:</span>
              <span class="font-bold text-neu-text">Full-Time Regular</span>
            </div>
          </div>
        </div>

        <!-- Contact & Security -->
        <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
          <div class="pb-3 border-b border-neu-border/40">
            <h3 class="text-sm font-bold text-neu-text">Contact Information</h3>
            <p class="text-xs text-neu-muted">Primary communication channels on record.</p>
          </div>

          <div class="space-y-3 text-xs">
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Corporate Email:</span>
              <span class="font-bold text-neu-text">{{ profile.email }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Phone Number:</span>
              <span class="font-bold text-neu-text">{{ profile.phone || '+1 (555) 234-8901' }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Desk Allocation:</span>
              <span class="font-bold text-neu-text">Building 4, Floor 3, Desk 312</span>
            </div>
            <div class="flex justify-between py-1 border-b border-neu-border/20">
              <span class="text-neu-muted">Timezone:</span>
              <span class="font-bold text-neu-text">America/Los_Angeles (PST)</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-neu-muted">Emergency Contact:</span>
              <span class="font-bold text-neu-text">Sarah Watson (+1 555-901-2345)</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
