<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\MLServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function __construct(
        protected MLServiceClient $mlService
    ) {}

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['employee.department'])
            ->where('tenant_id', $request->user()->tenant_id);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('is_anomaly') || $request->input('anomalies_only') === 'true' || $request->input('anomalies_only') === '1') {
            $query->anomalies();
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($attendances);
    }

    /**
     * Record a check-in for an employee.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $now = Carbon::now();
        $today = $now->toDateString();

        // Check if already checked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return $this->error('Employee already checked in today', 422);
        }

        // Determine if late (after 9:00 AM)
        $status = $now->hour >= 9 && $now->minute > 0 ? 'late' : 'present';

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'check_in' => $now,
            'status' => $status,
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $request->user()->tenant_id,
        ]);

        return $this->success($attendance, 'Check-in recorded successfully', 201);
    }

    /**
     * Record a check-out for an employee.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ]);

        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $validated['employee_id'])
            ->where('date', $today)
            ->first();

        if (! $attendance) {
            return $this->error('No check-in record found for today', 422);
        }

        if ($attendance->check_out) {
            return $this->error('Employee already checked out today', 422);
        }

        $now = Carbon::now();
        $checkIn = Carbon::parse($attendance->check_in);
        $hoursWorked = $checkIn->diffInMinutes($now) / 60;

        $attendance->update([
            'check_out' => $now,
            'hours_worked' => round($hoursWorked, 2),
        ]);

        return $this->success($attendance, 'Check-out recorded successfully');
    }

    /**
     * Get attendance anomalies detected by the ML service.
     */
    public function anomalies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
        ]);

        $tenantId = $request->user()->tenant_id;

        // Get recent attendance data
        $query = Attendance::with('employee.department')
            ->where('tenant_id', $tenantId);

        if (isset($validated['date_from']) && isset($validated['date_to'])) {
            $query->dateRange($validated['date_from'], $validated['date_to']);
        } else {
            $query->where('date', '>=', Carbon::now()->subDays(30)->toDateString());
        }

        $attendances = $query->get();

        try {
            $anomalies = $this->mlService->detectAnomalies([
                'attendance_records' => $attendances->toArray(),
                'tenant_id' => $tenantId,
            ]);

            // Update anomaly flags in database
            if (isset($anomalies['anomaly_ids']) && is_array($anomalies['anomaly_ids'])) {
                Attendance::whereIn('id', $anomalies['anomaly_ids'])
                    ->update(['is_anomaly' => true]);
            }

            return $this->success($anomalies);
        } catch (\Exception $e) {
            // Fallback: return database-flagged anomalies
            $dbAnomalies = Attendance::with('employee.department')
                ->where('tenant_id', $tenantId)
                ->anomalies()
                ->orderBy('date', 'desc')
                ->paginate(15);

            return $this->success($dbAnomalies, 'ML service unavailable, showing cached anomalies');
        }
    }

    /**
     * Get attendance statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $dateFrom = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->toDateString());

        $query = Attendance::where('tenant_id', $tenantId)
            ->dateRange($dateFrom, $dateTo);

        $totalRecords = (clone $query)->count();
        $presentCount = (clone $query)->where('status', 'present')->count();
        $lateCount = (clone $query)->where('status', 'late')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();
        $anomalyCount = (clone $query)->where('is_anomaly', true)->count();
        $avgHoursWorked = (clone $query)->whereNotNull('hours_worked')->avg('hours_worked');

        // Aggregated daily trend for the 30-day trend chart
        $dailyTrend = (clone $query)
            ->selectRaw('date, count(*) as total, sum(case when status in ("present", "late") then 1 else 0 end) as attended, sum(case when is_anomaly = 1 then 1 else 0 end) as anomalies')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total;
                $attended = (int) $row->attended;
                $rate = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
                return [
                    'date' => Carbon::parse($row->date)->toDateString(),
                    'attendance_rate' => $rate,
                    'anomaly_count' => (int) $row->anomalies,
                    'total' => $total,
                ];
            });

        return $this->success([
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'total_records' => $totalRecords,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'anomalies' => $anomalyCount,
            'average_hours_worked' => round((float) $avgHoursWorked, 2),
            'attendance_rate' => $totalRecords > 0
                ? round(($presentCount + $lateCount) / $totalRecords * 100, 2)
                : 0,
            'daily_trend' => $dailyTrend,
        ]);
    }
}
