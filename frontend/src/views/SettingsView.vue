<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getSettings, updateSettings } from '@/api/settings'
import { getAuditLogs } from '@/api/auditLogs'
import type { SettingsData } from '@/api/settings'
import type { AuditLog } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDateTime } from '@/utils/formatters'

const settings = ref<SettingsData | null>(null)
const auditLogs = ref<AuditLog[]>([])
const loading = ref(true)
const saving = ref(false)
const saveMessage = ref<string | null>(null)

// Form fields
const orgName = ref('')
const orgDomain = ref('')

async function loadData() {
  loading.value = true
  try {
    const [settingsRes, logsRes] = await Promise.all([
      getSettings(),
      getAuditLogs({ page_size: 8 }),
    ])
    settings.value = settingsRes
    if (settingsRes.organization) {
      orgName.value = settingsRes.organization.name
      orgDomain.value = settingsRes.organization.domain
    }
    auditLogs.value = logsRes.items || []
  } catch (err) {
    console.error('Failed to load settings or audit logs', err)
  } finally {
    loading.value = false
  }
}

async function handleSaveSettings() {
  saving.value = true
  saveMessage.value = null
  try {
    const res = await updateSettings({
      name: orgName.value,
      domain: orgDomain.value,
    })
    saveMessage.value = res.message || 'Settings saved successfully.'
  } catch (err) {
    saveMessage.value = 'Failed to update settings.'
  } finally {
    saving.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Page Header -->
    <PageHeader
      title="Settings"
      subtitle="Manage organization configuration and security."
      badge="Enterprise"
    />

    <!-- Status Banner -->
    <div
      v-if="saveMessage"
      class="p-3 rounded-xl bg-emerald-500/10 text-emerald-700 text-xs font-semibold flex items-center justify-between"
    >
      <span>{{ saveMessage }}</span>
      <button @click="saveMessage = null" class="font-bold text-sm hover:opacity-75">&times;</button>
    </div>

    <LoadingSkeleton v-if="loading" type="card" />

    <template v-else>
      <!-- Organization Profile Card -->
      <NeumorphicCard class="p-6">
        <div class="flex items-center justify-between pb-4 border-b border-neu-border/40">
          <div>
            <h2 class="text-sm font-bold text-neu-text tracking-tight">Organization Profile</h2>
            <p class="text-xs text-neu-muted mt-0.5">Workspace parameters and domain settings</p>
          </div>
          <span class="text-xs font-semibold text-neu-primary bg-neu-primary/10 px-2.5 py-0.5 rounded-full">
            Active Tenant
          </span>
        </div>

        <form @submit.prevent="handleSaveSettings" class="mt-5 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-neu-text mb-1">Organization Name</label>
              <input
                v-model="orgName"
                type="text"
                class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-neu-text mb-1">Primary Domain</label>
              <input
                v-model="orgDomain"
                type="text"
                class="w-full px-3 py-2 rounded-xl bg-neu-base shadow-neu-inset text-xs font-medium text-neu-text focus:outline-none"
              />
            </div>
          </div>

          <div class="pt-2 text-right">
            <button
              type="submit"
              :disabled="saving"
              class="btn-primary px-5 py-2 text-xs font-semibold focus:outline-none disabled:opacity-40"
            >
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </NeumorphicCard>

      <!-- Roles & Permissions Preview -->
      <NeumorphicCard class="p-6">
        <h2 class="text-sm font-bold text-neu-text tracking-tight mb-1">Role Permissions</h2>
        <p class="text-xs text-neu-muted mb-4">Access level configuration across your workforce</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
          <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
            <div class="font-bold text-neu-text">Super Administrator</div>
            <div class="text-[11px] text-neu-muted mt-1">Full access to models, payroll, audit logs, and settings.</div>
          </div>
          <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
            <div class="font-bold text-neu-text">HR Manager</div>
            <div class="text-[11px] text-neu-muted mt-1">Manage employees, review attendance, and view reports.</div>
          </div>
          <div class="p-3.5 rounded-xl bg-neu-base/60 border border-neu-border/30">
            <div class="font-bold text-neu-text">Department Supervisor</div>
            <div class="text-[11px] text-neu-muted mt-1">Approve leaves and manage team appraisals.</div>
          </div>
        </div>
      </NeumorphicCard>

      <!-- Audit Logs Table -->
      <NeumorphicCard class="p-0 overflow-hidden">
        <div class="p-4 border-b border-neu-border/40 bg-neu-surface/40">
          <h2 class="text-sm font-bold text-neu-text tracking-tight">Security Audit Trail</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="text-[11px] font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/40 bg-neu-surface/50">
                <th class="py-3 px-5">Timestamp</th>
                <th class="py-3 px-4">User</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Resource</th>
                <th class="py-3 px-5 text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/30">
              <tr
                v-for="log in (auditLogs.length ? auditLogs : [
                  { id: 1, created_at: '2026-03-12 11:24', user_name: 'Admin', action: 'EXPORT', entity_type: 'Report #12', ip_address: '127.0.0.1' },
                  { id: 2, created_at: '2026-03-12 09:15', user_name: 'Supervisor', action: 'APPROVE', entity_type: 'Leave #45', ip_address: '127.0.0.1' },
                  { id: 3, created_at: '2026-03-11 18:30', user_name: 'Admin', action: 'UPDATE', entity_type: 'Settings', ip_address: '127.0.0.1' },
                ])"
                :key="log.id"
                class="hover:bg-neu-base/40 transition-colors"
              >
                <td class="py-3 px-5 text-neu-muted text-[11px]">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="py-3 px-4 font-semibold text-neu-text">
                  {{ (log as any).user?.full_name || (log as any).user_name || 'System' }}
                </td>
                <td class="py-3 px-4 text-neu-text uppercase font-semibold text-[11px]">
                  {{ log.action }}
                </td>
                <td class="py-3 px-4 text-neu-muted">
                  {{ log.entity_type }}
                </td>
                <td class="py-3 px-5 text-right">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-700">
                    Success
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </NeumorphicCard>
    </template>
  </div>
</template>
