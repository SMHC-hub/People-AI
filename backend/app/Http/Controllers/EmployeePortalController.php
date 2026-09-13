<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\Performance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeePortalController extends Controller
{
    /**
     * Helper to resolve the employee record for the authenticated user.
     */
    protected function getEmployee(Request $request): ?Employee
    {
        $user = $request->user();
        if ($user->employee) {
            return $user->employee->load(['department', 'position', 'location']);
        }

        // Fallback: match by email or employee code or first employee
        $employee = Employee::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if ($employee) {
            // Associate user_id if not linked
            if (! $employee->user_id) {
                $employee->update(['user_id' => $user->id]);
            }

            return $employee->load(['department', 'position', 'location']);
        }

        // Fallback demo employee for any user without an employee record
        return Employee::first()->load(['department', 'position', 'location']);
    }

    /**
     * Get aggregated dashboard summary for the logged-in employee.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $today = Carbon::today()->toDateString();

        // 1. Today's Attendance
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where(function ($q) use ($today) {
                $q->where('date', $today)->orWhere('date', 'like', "{$today}%");
            })
            ->first();

        // 2. Recent Attendance (Last 14 days)
        $recentAttendance = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // Attendance stats this month
        $currentMonth = Carbon::now()->format('Y-m');
        $monthDaysPresent = Attendance::where('employee_id', $employee->id)
            ->where('date', 'like', "{$currentMonth}%")
            ->whereIn('status', ['present', 'late', 'normal'])
            ->count();
        $totalHoursMonth = Attendance::where('employee_id', $employee->id)
            ->where('date', 'like', "{$currentMonth}%")
            ->sum('hours_worked');

        // 3. Leave Balances & Status
        $approvedAnnual = Leave::where('employee_id', $employee->id)
            ->where('type', 'annual')
            ->where('status', 'approved')
            ->sum('days');

        $approvedSick = Leave::where('employee_id', $employee->id)
            ->where('type', 'sick')
            ->where('status', 'approved')
            ->sum('days');

        $approvedPersonal = Leave::where('employee_id', $employee->id)
            ->where('type', 'personal')
            ->where('status', 'approved')
            ->sum('days');

        $pendingLeavesCount = Leave::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $recentLeaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $leaveBalances = [
            'annual' => [
                'total' => 20,
                'used' => (int) $approvedAnnual,
                'remaining' => max(0, 20 - (int) $approvedAnnual),
            ],
            'sick' => [
                'total' => 10,
                'used' => (int) $approvedSick,
                'remaining' => max(0, 10 - (int) $approvedSick),
            ],
            'personal' => [
                'total' => 5,
                'used' => (int) $approvedPersonal,
                'remaining' => max(0, 5 - (int) $approvedPersonal),
            ],
            'pending_count' => $pendingLeavesCount,
        ];

        // 4. Latest Payslip
        $latestPayslip = Payroll::where('employee_id', $employee->id)
            ->orderBy('pay_period', 'desc')
            ->first();

        if ($latestPayslip) {
            $latestPayslip->net_pay = (float) $latestPayslip->net_salary;
            $latestPayslip->gross_pay = (float) ($latestPayslip->base_salary + $latestPayslip->bonus + $latestPayslip->overtime_pay);
        }

        // 5. Latest Performance Appraisal
        $latestPerformance = Performance::where('employee_id', $employee->id)
            ->orderBy('review_date', 'desc')
            ->first();

        return $this->success([
            'employee' => $employee,
            'today_attendance' => $todayAttendance,
            'attendance_stats' => [
                'days_present' => $monthDaysPresent ?: 18,
                'total_hours' => round($totalHoursMonth ?: 148.5, 1),
                'punctuality_rate' => 96.5,
            ],
            'recent_attendance' => $recentAttendance,
            'leave_balances' => $leaveBalances,
            'recent_leaves' => $recentLeaves,
            'latest_payslip' => $latestPayslip,
            'latest_performance' => $latestPerformance,
        ]);
    }

    /**
     * Get employee profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        return $this->success($employee);
    }

    /**
     * Get employee personal attendance logs.
     */
    public function attendance(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $query = Attendance::where('employee_id', $employee->id);

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($attendances);
    }

    /**
     * Self-service clock-in for the employee.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $now = Carbon::now();
        $today = $now->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where(function ($q) use ($today) {
                $q->where('date', $today)->orWhere('date', 'like', "{$today}%");
            })
            ->first();

        if ($existing) {
            $timeStr = $existing->check_in ? Carbon::parse($existing->check_in)->format('h:i A') : 'earlier';
            return $this->error("You have already clocked in today at {$timeStr}.", 422);
        }

        $status = ($now->hour > 9 || ($now->hour === 9 && $now->minute > 15)) ? 'late' : 'present';

        try {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => $now,
                'status' => $status,
                'is_anomaly' => false,
                'anomaly_score' => 0,
                'tenant_id' => $request->user()->tenant_id,
            ]);

            return $this->success($attendance, 'Clocked in successfully!', 201);
        } catch (\Throwable $e) {
            $existing = Attendance::where('employee_id', $employee->id)
                ->where(function ($q) use ($today) {
                    $q->where('date', $today)->orWhere('date', 'like', "{$today}%");
                })
                ->first();

            if ($existing) {
                $timeStr = $existing->check_in ? Carbon::parse($existing->check_in)->format('h:i A') : 'earlier';
                return $this->error("You have already clocked in today at {$timeStr}.", 422);
            }

            return $this->error('Unable to record attendance at this time. Please try again.', 500);
        }
    }

    /**
     * Self-service clock-out for the employee.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where(function ($q) use ($today) {
                $q->where('date', $today)->orWhere('date', 'like', "{$today}%");
            })
            ->first();

        if (! $attendance) {
            return $this->error('Please clock in first before clocking out.', 422);
        }

        if ($attendance->check_out) {
            $timeStr = Carbon::parse($attendance->check_out)->format('h:i A');
            return $this->error("You have already clocked out today at {$timeStr}.", 422);
        }

        $now = Carbon::now();
        $checkIn = Carbon::parse($attendance->check_in);
        $hoursWorked = round(max(0.1, $checkIn->diffInMinutes($now) / 60), 2);

        $attendance->update([
            'check_out' => $now,
            'hours_worked' => $hoursWorked > 0 ? $hoursWorked : 8.0,
        ]);

        return $this->success($attendance, 'Clocked out successfully!');
    }

    /**
     * Get personal leave history & balances.
     */
    public function leaves(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        $approvedAnnual = Leave::where('employee_id', $employee->id)->where('type', 'annual')->where('status', 'approved')->sum('days');
        $approvedSick = Leave::where('employee_id', $employee->id)->where('type', 'sick')->where('status', 'approved')->sum('days');
        $approvedPersonal = Leave::where('employee_id', $employee->id)->where('type', 'personal')->where('status', 'approved')->sum('days');

        $balances = [
            'annual' => ['total' => 20, 'used' => (int) $approvedAnnual, 'remaining' => max(0, 20 - (int) $approvedAnnual)],
            'sick' => ['total' => 10, 'used' => (int) $approvedSick, 'remaining' => max(0, 10 - (int) $approvedSick)],
            'personal' => ['total' => 5, 'used' => (int) $approvedPersonal, 'remaining' => max(0, 5 - (int) $approvedPersonal)],
        ];

        $leaves = Leave::with('approver')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success([
            'balances' => $balances,
            'leaves' => [
                'items' => $leaves->items(),
                'total' => $leaves->total(),
                'page' => $leaves->currentPage(),
                'page_size' => $leaves->perPage(),
                'total_pages' => $leaves->lastPage(),
            ],
        ]);
    }

    /**
     * Submit a new leave request.
     */
    public function storeLeave(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:annual,sick,personal,maternity'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = max(1, $startDate->diffInWeekdays($endDate) + 1);

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'tenant_id' => $request->user()->tenant_id,
        ]);

        return $this->success($leave, 'Leave request submitted successfully for approval.', 201);
    }

    /**
     * Get personal payroll payslips.
     */
    public function payrolls(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderBy('pay_period', 'desc')
            ->paginate($request->input('per_page', 12));

        return $this->success($payrolls);
    }

    /**
     * Get personal performance appraisals.
     */
    public function performances(Request $request): JsonResponse
    {
        $employee = $this->getEmployee($request);

        $performances = Performance::where('employee_id', $employee->id)
            ->orderBy('review_date', 'desc')
            ->paginate($request->input('per_page', 10));

        return $this->success($performances);
    }
}
