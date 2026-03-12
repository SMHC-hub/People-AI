<?php

namespace Tests\Feature;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    /**
     * Test listing employees.
     */
    public function test_admin_can_list_employees(): void
    {
        $user = $this->authenticateUser('admin');
        $this->createEmployee($user->tenant_id);
        $this->createEmployee($user->tenant_id);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'first_name', 'last_name', 'email', 'employee_code', 'status'],
                    ],
                ],
            ]);
    }

    /**
     * Test creating an employee.
     */
    public function test_admin_can_create_employee(): void
    {
        $user = $this->authenticateUser('admin');
        $department = $this->createDepartment($user->tenant_id);
        $position = $this->createPosition($department->id, $user->tenant_id);

        $response = $this->postJson('/api/employees', [
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => 'EMP9999',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone' => '+1-555-123-4567',
            'hire_date' => '2024-01-15',
            'salary' => 75000,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Employee created successfully',
            ])
            ->assertJsonPath('data.first_name', 'Jane')
            ->assertJsonPath('data.last_name', 'Doe')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP9999',
            'email' => 'jane.doe@example.com',
        ]);
    }

    /**
     * Test creating employee with invalid data fails.
     */
    public function test_create_employee_fails_with_invalid_data(): void
    {
        $this->authenticateUser('admin');

        $response = $this->postJson('/api/employees', [
            'first_name' => '',
            'email' => 'not-valid',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test showing a specific employee.
     */
    public function test_admin_can_view_employee(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'first_name', 'last_name', 'email',
                    'department', 'position',
                ],
            ]);
    }

    /**
     * Test updating an employee.
     */
    public function test_admin_can_update_employee(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'salary' => 80000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Employee updated successfully',
            ])
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.last_name', 'Name');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Updated',
            'salary' => 80000,
        ]);
    }

    /**
     * Test deleting an employee.
     */
    public function test_admin_can_delete_employee(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);

        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }

    /**
     * Test employee listing with status filter.
     */
    public function test_can_filter_employees_by_status(): void
    {
        $user = $this->authenticateUser('admin');
        $this->createEmployee($user->tenant_id, ['status' => 'active']);
        $this->createEmployee($user->tenant_id, ['status' => 'resigned', 'resign_date' => now()]);

        $response = $this->getJson('/api/employees?status=active');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        foreach ($data as $employee) {
            $this->assertEquals('active', $employee['status']);
        }
    }

    /**
     * Test employee listing with search.
     */
    public function test_can_search_employees(): void
    {
        $user = $this->authenticateUser('admin');
        $this->createEmployee($user->tenant_id, [
            'first_name' => 'UniqueSearchName',
            'last_name' => 'TestPerson',
        ]);

        $response = $this->getJson('/api/employees?search=UniqueSearchName');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertEquals('UniqueSearchName', $data[0]['first_name']);
    }

    /**
     * Test employee creation requires unique employee code.
     */
    public function test_employee_code_must_be_unique(): void
    {
        $user = $this->authenticateUser('admin');
        $department = $this->createDepartment($user->tenant_id);
        $position = $this->createPosition($department->id, $user->tenant_id);

        // Create first employee
        Employee::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => 'DUPLICATE',
            'first_name' => 'First',
            'last_name' => 'Employee',
            'email' => 'first@example.com',
            'hire_date' => now(),
            'salary' => 50000,
            'status' => 'active',
            'tenant_id' => $user->tenant_id,
        ]);

        // Attempt duplicate
        $response = $this->postJson('/api/employees', [
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => 'DUPLICATE',
            'first_name' => 'Second',
            'last_name' => 'Employee',
            'email' => 'second@example.com',
            'hire_date' => '2024-01-15',
            'salary' => 55000,
        ]);

        $response->assertStatus(422);
    }
}
