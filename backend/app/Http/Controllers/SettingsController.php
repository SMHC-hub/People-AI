<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get organization settings, role definitions, and users.
     */
    public function show(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $org = Organization::find($tenantId) ?? Organization::first();
        $users = User::where('tenant_id', $tenantId)->get();

        $roles = [
            [
                'id' => 'admin',
                'name' => 'Super / Organization Admin',
                'description' => 'Full unrestricted enterprise control across all entities, MLOps, and configurations.',
                'permissions' => ['all_access', 'manage_users', 'train_models', 'manage_payrolls', 'view_audit_logs'],
            ],
            [
                'id' => 'hr_manager',
                'name' => 'HR Manager',
                'description' => 'Comprehensive HR operations, compensation reviews, and retention intervention management.',
                'permissions' => ['view_employees', 'edit_employees', 'view_payrolls', 'approve_payrolls', 'view_predictions'],
            ],
            [
                'id' => 'hr_analyst',
                'name' => 'HR Data Analyst',
                'description' => 'Analytical access to workforce metrics, model monitoring, reports, and AI query assistant.',
                'permissions' => ['view_analytics', 'view_reports', 'generate_reports', 'view_predictions'],
            ],
            [
                'id' => 'manager',
                'name' => 'People Manager',
                'description' => 'Departmental access to team performance, attendance records, and leave approvals.',
                'permissions' => ['view_team_employees', 'approve_leaves', 'submit_reviews'],
            ],
            [
                'id' => 'employee',
                'name' => 'Standard Employee',
                'description' => 'Personal self-service profile, attendance check-in/out, and leave requests.',
                'permissions' => ['view_self', 'request_leave', 'view_own_attendance'],
            ],
        ];

        return $this->success([
            'organization' => $org,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Update organization settings.
     */
    public function update(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $org = Organization::find($tenantId) ?? Organization::first();

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'settings' => 'sometimes|array',
        ]);

        if ($org) {
            $org->update($validated);
        }

        return $this->success($org, 'Organization settings updated successfully');
    }
}
