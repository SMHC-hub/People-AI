<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll records.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $query = Payroll::with(['employee.department', 'employee.position'])
            ->where('tenant_id', $tenantId);

        if ($request->has('pay_period')) {
            $query->where('pay_period', $request->input('pay_period'));
        }

        if ($request->boolean('is_anomaly')) {
            $query->where('is_anomaly', true);
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('is_anomaly', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($payrolls);
    }

    /**
     * Get payroll intelligence and anomaly summary stats.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $period = $request->input('pay_period', '2026-08');

        $totalRecords = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->count();
        $totalPayout = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->sum('net_salary');
        $totalOvertime = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->sum('overtime_pay');
        $anomalyCount = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->where('is_anomaly', true)->count();
        $flaggedAmount = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->where('is_anomaly', true)->sum('net_salary');

        $byType = Payroll::where('tenant_id', $tenantId)
            ->where('pay_period', $period)
            ->where('is_anomaly', true)
            ->groupBy('anomaly_type')
            ->selectRaw('anomaly_type, count(*) as count')
            ->get();

        $pendingCount = Payroll::where('tenant_id', $tenantId)->where('pay_period', $period)->where('is_anomaly', true)->where('review_status', 'pending')->count();

        return $this->success([
            'pay_period' => $period,
            'total_records' => $totalRecords,
            'total_payrolls_period' => $totalRecords,
            'total_payout' => (float) $totalPayout,
            'avg_net_salary' => $totalRecords > 0 ? round($totalPayout / $totalRecords, 2) : 0,
            'total_overtime' => (float) $totalOvertime,
            'total_overtime_pay' => (float) $totalOvertime,
            'anomaly_count' => $anomalyCount,
            'total_anomalies' => $anomalyCount,
            'anomaly_rate' => $totalRecords > 0 ? round(($anomalyCount / $totalRecords) * 100, 2) : 0,
            'flagged_amount' => (float) $flaggedAmount,
            'pending_reviews' => $pendingCount,
            'anomalies_by_type' => $byType,
        ]);
    }

    /**
     * Get list of high-priority payroll anomalies for review.
     */
    public function anomalies(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $anomalies = Payroll::with(['employee.department', 'employee.position', 'employee.location'])
            ->where('tenant_id', $tenantId)
            ->where('is_anomaly', true)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return $this->success($anomalies);
    }

    /**
     * Review/update a payroll anomaly status.
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $payroll = Payroll::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,flagged,processed',
            'notes' => 'nullable|string',
        ]);

        $payroll->update([
            'status' => $validated['status'],
        ]);

        return $this->success($payroll, 'Payroll review status updated successfully');
    }
}
