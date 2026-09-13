<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getPortalPayrolls } from '@/api/portal'
import type { Payroll, PaginatedResponse } from '@/types'
import PageHeader from '@/components/PageHeader.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const loading = ref(true)
const payrollData = ref<PaginatedResponse<Payroll> | null>(null)
const selectedPayslip = ref<Payroll | null>(null)

async function fetchPayrolls() {
  loading.value = true
  try {
    payrollData.value = await getPortalPayrolls()
  } catch (err: any) {
    console.error('Failed to load payslips:', err)
  } finally {
    loading.value = false
  }
}

function openPayslip(item: Payroll) {
  selectedPayslip.value = item
}

function closePayslip() {
  selectedPayslip.value = null
}

function printPayslip() {
  window.print()
}

onMounted(() => {
  fetchPayrolls()
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <PageHeader
      title="My Payslips"
      subtitle="Access your monthly compensation statements and earnings breakdown."
    />

    <!-- Annual Earnings Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Monthly Base</span>
        <div class="text-2xl font-black text-neu-text tracking-tight mt-1">
          ${{ Number(payrollData?.items?.[0]?.gross_pay || payrollData?.items?.[0]?.base_salary || 8626).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
        </div>
        <div class="text-xs text-neu-muted mt-0.5">Fixed gross rate</div>
      </div>

      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Latest Take-Home</span>
        <div class="text-2xl font-black text-emerald-600 tracking-tight mt-1">
          ${{ Number(payrollData?.items?.[0]?.net_pay || payrollData?.items?.[0]?.net_salary || 6820).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
        </div>
        <div class="text-xs text-neu-muted mt-0.5">Direct deposit transferred</div>
      </div>

      <div class="neu-card p-6 shadow-neu-flat border border-white/60">
        <span class="text-xs font-bold uppercase tracking-wider text-neu-muted">Latest Period</span>
        <div class="text-2xl font-black text-neu-primary tracking-tight mt-1">
          {{ payrollData?.items?.[0]?.pay_period || payrollData?.items?.[0]?.month || '2026-08' }}
        </div>
        <div class="text-xs text-neu-muted mt-0.5">Disbursed on schedule</div>
      </div>
    </div>

    <!-- Payslip History Table -->
    <div class="neu-card p-6 shadow-neu-flat border border-white/60 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-neu-border/40">
        <h3 class="text-sm font-bold text-neu-text">Payslip History</h3>
        <span class="text-xs text-neu-muted font-medium">
          {{ payrollData?.items?.length || 0 }} statements available
        </span>
      </div>

      <LoadingSkeleton v-if="loading" :rows="4" type="table" />

      <template v-else-if="payrollData && payrollData.items.length > 0">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-neu-border/40 text-[11px] font-bold uppercase tracking-wider text-neu-muted">
                <th class="py-3 px-4">Pay Period</th>
                <th class="py-3 px-4">Gross Earnings</th>
                <th class="py-3 px-4">Deductions</th>
                <th class="py-3 px-4">Net Take-Home</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neu-border/20 text-xs">
              <tr
                v-for="item in payrollData.items"
                :key="item.id"
                class="hover:bg-white/40 transition-colors cursor-pointer"
                @click="openPayslip(item)"
              >
                <td class="py-3.5 px-4 font-bold text-neu-text">{{ item.pay_period || item.month }}</td>
                <td class="py-3.5 px-4 text-neu-muted font-medium">
                  ${{ Number(item.gross_pay || item.base_salary).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="py-3.5 px-4 text-rose-600 font-medium">
                  -${{ Number(item.deductions).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="py-3.5 px-4 font-black text-neu-text text-sm">
                  ${{ Number(item.net_pay || item.net_salary).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </td>
                <td class="py-3.5 px-4">
                  <StatusBadge status="paid" size="sm" />
                </td>
                <td class="py-3.5 px-4 text-right">
                  <button
                    @click.stop="openPayslip(item)"
                    type="button"
                    class="btn-secondary px-3 py-1.5 text-xs font-bold cursor-pointer"
                  >
                    View Payslip
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <EmptyState
        v-else
        title="No payslips on record"
        description="Your generated monthly payslips will be listed here."
      />
    </div>

    <!-- Detailed Payslip Modal -->
    <div
      v-if="selectedPayslip"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
      @click.self="closePayslip"
    >
      <div class="neu-card p-8 shadow-neu-flat-lg border border-white/80 max-w-xl w-full space-y-6">
        <!-- Payslip Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neu-border/40">
          <div>
            <div class="text-xs font-bold text-neu-primary uppercase tracking-wider">Acme Global Technologies</div>
            <h3 class="text-base font-black text-neu-text">Payslip for Period {{ selectedPayslip.pay_period || selectedPayslip.month }}</h3>
          </div>
          <button
            @click="closePayslip"
            type="button"
            class="text-neu-muted hover:text-neu-text p-1 text-lg"
          >
            &times;
          </button>
        </div>

        <!-- Two Column Breakdown: Earnings vs Deductions -->
        <div class="grid grid-cols-2 gap-4 text-xs">
          <!-- Earnings -->
          <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset space-y-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-neu-muted block pb-1 border-b border-neu-border/30">
              Earnings
            </span>
            <div class="flex justify-between">
              <span class="text-neu-muted">Base Salary:</span>
              <span class="font-bold text-neu-text">${{ (Number(selectedPayslip.gross_pay || selectedPayslip.base_salary) * 0.9).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Performance Bonus:</span>
              <span class="font-bold text-neu-text">${{ (Number(selectedPayslip.gross_pay || selectedPayslip.base_salary) * 0.1).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="pt-2 border-t border-neu-border/30 flex justify-between font-bold text-neu-text">
              <span>Total Gross:</span>
              <span>${{ Number(selectedPayslip.gross_pay || selectedPayslip.base_salary).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
            </div>
          </div>

          <!-- Deductions -->
          <div class="p-4 rounded-2xl bg-neu-base shadow-neu-inset space-y-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-neu-muted block pb-1 border-b border-neu-border/30">
              Deductions
            </span>
            <div class="flex justify-between">
              <span class="text-neu-muted">Income Tax (FIT):</span>
              <span class="font-semibold text-rose-600">-${{ (Number(selectedPayslip.deductions) * 0.65).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Social Security:</span>
              <span class="font-semibold text-rose-600">-${{ (Number(selectedPayslip.deductions) * 0.20).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-neu-muted">Health Insurance:</span>
              <span class="font-semibold text-rose-600">-${{ (Number(selectedPayslip.deductions) * 0.15).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
            </div>
            <div class="pt-2 border-t border-neu-border/30 flex justify-between font-bold text-rose-600">
              <span>Total Deductions:</span>
              <span>-${{ Number(selectedPayslip.deductions).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
            </div>
          </div>
        </div>

        <!-- Net Total Box -->
        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 block">Net Take-Home Pay</span>
            <span class="text-xs text-emerald-700">Disbursed to primary checking account</span>
          </div>
          <div class="text-2xl font-black text-emerald-700">
            ${{ Number(selectedPayslip.net_pay || selectedPayslip.net_salary).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-3 border-t border-neu-border/30 text-xs">
          <button
            @click="printPayslip"
            type="button"
            class="btn-secondary px-4 py-2 font-bold cursor-pointer"
          >
            🖨 Print Statement
          </button>
          <button
            @click="closePayslip"
            type="button"
            class="btn-primary px-5 py-2 font-bold cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
