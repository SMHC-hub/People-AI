<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create and authenticate a user for testing.
     */
    protected function authenticateUser(string $role = 'admin', int $tenantId = 1): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => "test-{$role}-".uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'tenant_id' => $tenantId,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * Create a department for testing.
     */
    protected function createDepartment(int $tenantId = 1, array $overrides = []): \App\Models\Department
    {
        return \App\Models\Department::create(array_merge([
            'name' => 'Test Department',
            'code' => 'TST'.uniqid(),
            'tenant_id' => $tenantId,
        ], $overrides));
    }

    /**
     * Create a position for testing.
     */
    protected function createPosition(int $departmentId, int $tenantId = 1, array $overrides = []): \App\Models\Position
    {
        return \App\Models\Position::create(array_merge([
            'title' => 'Test Position',
            'level' => 'mid',
            'min_salary' => 50000,
            'max_salary' => 80000,
            'department_id' => $departmentId,
            'tenant_id' => $tenantId,
        ], $overrides));
    }

    /**
     * Create an employee for testing.
     */
    protected function createEmployee(int $tenantId = 1, array $overrides = []): \App\Models\Employee
    {
        $department = $this->createDepartment($tenantId);
        $position = $this->createPosition($department->id, $tenantId);

        return \App\Models\Employee::create(array_merge([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => 'EMP'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'employee-'.uniqid().'@example.com',
            'phone' => '+1-555-000-0000',
            'hire_date' => now()->subYear(),
            'salary' => 65000,
            'status' => 'active',
            'tenant_id' => $tenantId,
        ], $overrides));
    }
}
