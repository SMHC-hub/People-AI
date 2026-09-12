<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getSettings, updateSettings } from '@/api/settings'
import { getAuditLogs } from '@/api/auditLogs'
import type { SettingsData } from '@/api/settings'
import type { AuditLog } from '@/types'
import NeumorphicCard from '@/components/NeumorphicCard.vue'
import NeumorphicStatCard from '@/components/NeumorphicStatCard.vue'
import NeumorphicButton from '@/components/NeumorphicButton.vue'
import NeumorphicBadge from '@/components/NeumorphicBadge.vue'

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
      getAuditLogs({ page_size: 10 }),
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
    saveMessage.value = res.message || 'Settings updated successfully.'
  } catch (err) {
    saveMessage.value = 'Failed to update settings.'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-neu-text">
          Enterprise Settings & RBAC Governance
        </h2>
        <p class="text-sm text-neu-muted mt-1">
          Multi-tenant configuration, role-based security policies, and immutable system audit trail.
        </p>
      </div>
    </div>

    <!-- Banner -->
    <div
      v-if="saveMessage"
      class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-semibold shadow-neu-flat border border-emerald-200 flex items-center justify-between"
    >
      <span>{{ saveMessage }}</span>
      <button @click="saveMessage = null" class="text-emerald-600 hover:text-emerald-900">&times;</button>
    </div>

    <!-- Organization Configuration Card -->
    <NeumorphicCard>
      <div class="flex items-center justify-between pb-4 border-b border-neu-border/50">
        <div>
          <h3 class="text-lg font-black text-neu-text tracking-tight">Organization Profile</h3>
          <p class="text-xs text-neu-muted">Enterprise tenant parameters and primary domain routing.</p>
        </div>
        <NeumorphicBadge variant="primary" size="sm">Enterprise Plan (Unlimited Seats)</NeumorphicBadge>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">
            Organization Legal Name
          </label>
          <input
            v-model="orgName"
            type="text"
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-sm font-semibold text-neu-text border border-white/40 focus:outline-none"
          />
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-neu-muted">
            Primary Corporate Domain
          </label>
          <input
            v-model="orgDomain"
            type="text"
            class="w-full px-4 py-2.5 rounded-2xl bg-neu-base shadow-neu-inset text-sm font-semibold text-neu-text border border-white/40 focus:outline-none"
          />
        </div>
      </div>

      <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-neu-border/40">
        <NeumorphicButton
          variant="primary"
          size="sm"
          :loading="saving"
          @click="handleSaveSettings"
        >
          Save Configuration
        </NeumorphicButton>
      </div>
    </NeumorphicCard>

    <!-- RBAC Matrix Card -->
    <NeumorphicCard>
      <div class="pb-4 border-b border-neu-border/50">
        <h3 class="text-lg font-black text-neu-text tracking-tight">
          Role-Based Access Control (RBAC) Matrix
        </h3>
        <p class="text-xs text-neu-muted mt-0.5">
          Explicit security boundary enforcement according to SOC-2 & ISO 27001 HR compliance standards.
        </p>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3.5 px-4">Role</th>
              <th class="py-3.5 px-4 text-center">Dashboard & Reports</th>
              <th class="py-3.5 px-4 text-center">Employee Records</th>
              <th class="py-3.5 px-4 text-center">Payroll Anomaly Audit</th>
              <th class="py-3.5 px-4 text-center">ML Retrain / Promote</th>
              <th class="py-3.5 px-4 text-center">Audit Trail View</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40">
            <tr class="hover:bg-neu-base/60">
              <td class="py-3.5 px-4 font-extrabold text-neu-text flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Administrator (admin)</span>
              </td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Full Access</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Read / Write / Delete</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Approve & Escalate</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Authorized</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Full Audit Access</td>
            </tr>

            <tr class="hover:bg-neu-base/60">
              <td class="py-3.5 px-4 font-extrabold text-neu-text flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-neu-primary"></span>
                <span>HR Manager (hr_manager)</span>
              </td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Full Access</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Read / Write</td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Review & Comment</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">Read Only</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">Restricted</td>
            </tr>

            <tr class="hover:bg-neu-base/60">
              <td class="py-3.5 px-4 font-extrabold text-neu-text flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>HR Analyst (hr_analyst)</span>
              </td>
              <td class="py-3.5 px-4 text-center text-emerald-600 font-bold">Full Access</td>
              <td class="py-3.5 px-4 text-center text-neu-text">Read Only</td>
              <td class="py-3.5 px-4 text-center text-neu-text">Read Only</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">View Metrics</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">None</td>
            </tr>

            <tr class="hover:bg-neu-base/60">
              <td class="py-3.5 px-4 font-extrabold text-neu-text flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                <span>Employee (employee)</span>
              </td>
              <td class="py-3.5 px-4 text-center text-neu-muted">Self Portal</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">Self Only</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">None</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">None</td>
              <td class="py-3.5 px-4 text-center text-neu-muted">None</td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>

    <!-- Live Audit Trail Card -->
    <NeumorphicCard>
      <div class="flex items-center justify-between pb-4 border-b border-neu-border/50">
        <div>
          <h3 class="text-lg font-black text-neu-text tracking-tight">System Audit Log</h3>
          <p class="text-xs text-neu-muted">Tamper-evident log of administrative modifications and model runs.</p>
        </div>
        <NeumorphicButton variant="default" size="sm" @click="loadData">
          Refresh Logs
        </NeumorphicButton>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-xs font-bold uppercase tracking-wider text-neu-muted border-b border-neu-border/60">
              <th class="py-3 px-4">Timestamp</th>
              <th class="py-3 px-4">Action</th>
              <th class="py-3 px-4">Entity</th>
              <th class="py-3 px-4">IP Address</th>
              <th class="py-3 px-4">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neu-border/40 font-mono text-xs">
            <tr
              v-for="log in auditLogs"
              :key="log.id"
              class="hover:bg-neu-base/60 transition-colors duration-150"
            >
              <td class="py-3 px-4 text-neu-muted whitespace-nowrap">
                {{ new Date(log.created_at).toLocaleString() }}
              </td>
              <td class="py-3 px-4 font-bold text-neu-text">
                <NeumorphicBadge variant="info" size="sm">
                  {{ log.action }}
                </NeumorphicBadge>
              </td>
              <td class="py-3 px-4 font-semibold text-neu-muted">
                {{ log.entity_type }} #{{ log.entity_id || '-' }}
              </td>
              <td class="py-3 px-4 text-neu-muted">
                {{ log.ip_address }}
              </td>
              <td class="py-3 px-4 text-neu-text truncate max-w-xs font-sans text-xs">
                {{ log.details ? JSON.stringify(log.details) : 'Administrative action recorded' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </NeumorphicCard>
  </div>
</template>
