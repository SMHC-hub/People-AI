<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\MLServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected MLServiceClient $mlService
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['department', 'position', 'user'])
            ->where('tenant_id', $request->user()->tenant_id);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate($request->input('per_page', 15));

        return $this->success($employees);
    }

    /**
     * Store a newly created employee.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'employee_code' => ['required', 'string', 'max:50', 'unique:employees'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees'],
            'phone' => ['nullable', 'string', 'max:20'],
            'hire_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,resigned,terminated'],
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['status'] = $validated['status'] ?? 'active';

        $employee = Employee::create($validated);
        $employee->load(['department', 'position', 'user']);

        return $this->success($employee, 'Employee created successfully', 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee->load(['department', 'position', 'user', 'attendances' => function ($query) {
            $query->orderBy('date', 'desc')->limit(30);
        }, 'leaves' => function ($query) {
            $query->orderBy('start_date', 'desc')->limit(10);
        }]);

        return $this->success($employee);
    }

    /**
     * Update the specified employee.
     */
    public function update(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'position_id' => ['sometimes', 'integer', 'exists:positions,id'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:employees,email,'.$employee->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'salary' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:active,resigned,terminated'],
            'resign_date' => ['nullable', 'date'],
        ]);

        $employee->update($validated);
        $employee->load(['department', 'position', 'user']);

        return $this->success($employee, 'Employee updated successfully');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return $this->success(null, 'Employee deleted successfully');
    }

    /**
     * Get the risk score for an employee from the ML service.
     */
    public function riskScore(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee->load(['attendances' => function ($query) {
            $query->orderBy('date', 'desc')->limit(90);
        }, 'leaves', 'department', 'position']);

        try {
            $riskData = $this->mlService->predictTurnover([
                'employee_id' => $employee->id,
                'department' => $employee->department->name ?? null,
                'position' => $employee->position->title ?? null,
                'salary' => $employee->salary,
                'hire_date' => $employee->hire_date?->toDateString(),
                'attendance_records' => $employee->attendances->toArray(),
                'leave_records' => $employee->leaves->toArray(),
            ]);

            return $this->success([
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'risk_score' => $riskData,
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to calculate risk score: '.$e->getMessage(), 503);
        }
    }
}
