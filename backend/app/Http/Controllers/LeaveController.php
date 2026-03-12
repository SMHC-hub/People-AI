<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Services\MLServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function __construct(
        protected MLServiceClient $mlService
    ) {}

    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Leave::with(['employee.department', 'approver'])
            ->where('tenant_id', $request->user()->tenant_id);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        $leaves = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($leaves);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'type' => ['required', 'string', 'in:annual,sick,personal,maternity'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInWeekdays($endDate) + 1;

        $leave = Leave::create([
            'employee_id' => $validated['employee_id'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'tenant_id' => $request->user()->tenant_id,
        ]);

        $leave->load(['employee', 'approver']);

        return $this->success($leave, 'Leave request submitted successfully', 201);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, Leave $leave): JsonResponse
    {
        if ($leave->status !== 'pending') {
            return $this->error('This leave request has already been processed', 422);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        $leave->load(['employee', 'approver']);

        return $this->success($leave, 'Leave request approved');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, Leave $leave): JsonResponse
    {
        if ($leave->status !== 'pending') {
            return $this->error('This leave request has already been processed', 422);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
        ]);

        $leave->load(['employee', 'approver']);

        return $this->success($leave, 'Leave request rejected');
    }

    /**
     * Get leave predictions from ML service.
     */
    public function predictions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'period' => ['sometimes', 'string', 'in:week,month,quarter'],
        ]);

        $tenantId = $request->user()->tenant_id;

        // Get historical leave data
        $historicalLeaves = Leave::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->where('start_date', '>=', Carbon::now()->subYear()->toDateString())
            ->with('employee.department')
            ->get();

        try {
            $predictions = $this->mlService->predictLeave([
                'historical_data' => $historicalLeaves->toArray(),
                'department_id' => $validated['department_id'] ?? null,
                'period' => $validated['period'] ?? 'month',
                'tenant_id' => $tenantId,
            ]);

            return $this->success($predictions);
        } catch (\Exception $e) {
            return $this->error('Failed to generate leave predictions: '.$e->getMessage(), 503);
        }
    }
}
