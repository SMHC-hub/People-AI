<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Admin can view all employees in their tenant
        if ($user->isAdmin() && $user->tenant_id === $employee->tenant_id) {
            return true;
        }

        // Manager can view employees in their department
        if ($user->isManager()) {
            $userEmployee = $user->employee;
            if ($userEmployee && $userEmployee->department_id === $employee->department_id) {
                return true;
            }
        }

        // Employee can view their own record
        if ($user->isEmployee() && $user->employee && $user->employee->id === $employee->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        // Admin can update all employees in their tenant
        if ($user->isAdmin() && $user->tenant_id === $employee->tenant_id) {
            return true;
        }

        // Manager can update employees in their department
        if ($user->isManager()) {
            $userEmployee = $user->employee;
            if ($userEmployee && $userEmployee->department_id === $employee->department_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Only admin can delete employees in their tenant
        return $user->isAdmin() && $user->tenant_id === $employee->tenant_id;
    }

    /**
     * Determine whether the user can restore the employee.
     */
    public function restore(User $user, Employee $employee): bool
    {
        return $user->isAdmin() && $user->tenant_id === $employee->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the employee.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->isAdmin() && $user->tenant_id === $employee->tenant_id;
    }
}
