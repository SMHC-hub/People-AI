<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): JsonResponse
    {
        $departments = Department::with(['manager', 'employees'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->withCount('employees')
            ->get();

        return $this->success($departments);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments'],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $department = Department::create($validated);
        $department->load('manager');

        return $this->success($department, 'Department created successfully', 201);
    }

    /**
     * Display the specified department.
     */
    public function show(Request $request, Department $department): JsonResponse
    {
        if ($department->tenant_id !== $request->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        $department->load(['manager', 'employees.position', 'positions']);
        $department->loadCount('employees');

        return $this->success($department);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        if ($department->tenant_id !== $request->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:20', 'unique:departments,code,'.$department->id],
            'manager_id' => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        $department->update($validated);
        $department->load('manager');

        return $this->success($department, 'Department updated successfully');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Request $request, Department $department): JsonResponse
    {
        if ($department->tenant_id !== $request->user()->tenant_id) {
            return $this->error('Unauthorized', 403);
        }

        if ($department->employees()->count() > 0) {
            return $this->error('Cannot delete department with active employees', 422);
        }

        $department->delete();

        return $this->success(null, 'Department deleted successfully');
    }
}
