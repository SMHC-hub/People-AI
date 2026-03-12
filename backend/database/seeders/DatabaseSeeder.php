<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenantId = 1;

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hranalytics.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tenant_id' => $tenantId,
            'email_verified_at' => now(),
        ]);

        // Create departments
        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG', 'tenant_id' => $tenantId],
            ['name' => 'Human Resources', 'code' => 'HR', 'tenant_id' => $tenantId],
            ['name' => 'Marketing', 'code' => 'MKT', 'tenant_id' => $tenantId],
            ['name' => 'Finance', 'code' => 'FIN', 'tenant_id' => $tenantId],
            ['name' => 'Operations', 'code' => 'OPS', 'tenant_id' => $tenantId],
        ];

        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[] = Department::create($dept);
        }

        // Create positions
        $positions = [
            ['title' => 'Software Engineer', 'level' => 'junior', 'min_salary' => 60000, 'max_salary' => 85000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'Senior Software Engineer', 'level' => 'senior', 'min_salary' => 90000, 'max_salary' => 130000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'Engineering Manager', 'level' => 'manager', 'min_salary' => 120000, 'max_salary' => 160000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'HR Specialist', 'level' => 'mid', 'min_salary' => 50000, 'max_salary' => 75000, 'department_id' => $deptModels[1]->id, 'tenant_id' => $tenantId],
            ['title' => 'HR Manager', 'level' => 'manager', 'min_salary' => 80000, 'max_salary' => 110000, 'department_id' => $deptModels[1]->id, 'tenant_id' => $tenantId],
            ['title' => 'Marketing Analyst', 'level' => 'junior', 'min_salary' => 45000, 'max_salary' => 65000, 'department_id' => $deptModels[2]->id, 'tenant_id' => $tenantId],
            ['title' => 'Marketing Manager', 'level' => 'manager', 'min_salary' => 80000, 'max_salary' => 115000, 'department_id' => $deptModels[2]->id, 'tenant_id' => $tenantId],
            ['title' => 'Financial Analyst', 'level' => 'mid', 'min_salary' => 55000, 'max_salary' => 80000, 'department_id' => $deptModels[3]->id, 'tenant_id' => $tenantId],
            ['title' => 'Operations Coordinator', 'level' => 'junior', 'min_salary' => 40000, 'max_salary' => 60000, 'department_id' => $deptModels[4]->id, 'tenant_id' => $tenantId],
            ['title' => 'Operations Manager', 'level' => 'manager', 'min_salary' => 75000, 'max_salary' => 105000, 'department_id' => $deptModels[4]->id, 'tenant_id' => $tenantId],
        ];

        $posModels = [];
        foreach ($positions as $pos) {
            $posModels[] = Position::create($pos);
        }

        // Create employees with user accounts
        $employeesData = [
            ['first_name' => 'Alice', 'last_name' => 'Johnson', 'role' => 'manager', 'dept' => 0, 'pos' => 2, 'salary' => 140000],
            ['first_name' => 'Bob', 'last_name' => 'Smith', 'role' => 'employee', 'dept' => 0, 'pos' => 1, 'salary' => 110000],
            ['first_name' => 'Charlie', 'last_name' => 'Brown', 'role' => 'employee', 'dept' => 0, 'pos' => 0, 'salary' => 72000],
            ['first_name' => 'Diana', 'last_name' => 'Lee', 'role' => 'employee', 'dept' => 0, 'pos' => 0, 'salary' => 68000],
            ['first_name' => 'Edward', 'last_name' => 'Wilson', 'role' => 'employee', 'dept' => 0, 'pos' => 1, 'salary' => 95000],
            ['first_name' => 'Fiona', 'last_name' => 'Davis', 'role' => 'manager', 'dept' => 1, 'pos' => 4, 'salary' => 95000],
            ['first_name' => 'George', 'last_name' => 'Martinez', 'role' => 'employee', 'dept' => 1, 'pos' => 3, 'salary' => 62000],
            ['first_name' => 'Hannah', 'last_name' => 'Taylor', 'role' => 'employee', 'dept' => 1, 'pos' => 3, 'salary' => 58000],
            ['first_name' => 'Ivan', 'last_name' => 'Anderson', 'role' => 'manager', 'dept' => 2, 'pos' => 6, 'salary' => 100000],
            ['first_name' => 'Julia', 'last_name' => 'Thomas', 'role' => 'employee', 'dept' => 2, 'pos' => 5, 'salary' => 52000],
            ['first_name' => 'Kevin', 'last_name' => 'Jackson', 'role' => 'employee', 'dept' => 2, 'pos' => 5, 'salary' => 55000],
            ['first_name' => 'Laura', 'last_name' => 'White', 'role' => 'employee', 'dept' => 3, 'pos' => 7, 'salary' => 68000],
            ['first_name' => 'Michael', 'last_name' => 'Harris', 'role' => 'employee', 'dept' => 3, 'pos' => 7, 'salary' => 72000],
            ['first_name' => 'Nancy', 'last_name' => 'Clark', 'role' => 'employee', 'dept' => 3, 'pos' => 7, 'salary' => 65000],
            ['first_name' => 'Oscar', 'last_name' => 'Lewis', 'role' => 'manager', 'dept' => 4, 'pos' => 9, 'salary' => 90000],
            ['first_name' => 'Patricia', 'last_name' => 'Robinson', 'role' => 'employee', 'dept' => 4, 'pos' => 8, 'salary' => 48000],
            ['first_name' => 'Quentin', 'last_name' => 'Walker', 'role' => 'employee', 'dept' => 4, 'pos' => 8, 'salary' => 45000],
            ['first_name' => 'Rachel', 'last_name' => 'Hall', 'role' => 'employee', 'dept' => 0, 'pos' => 0, 'salary' => 75000],
            ['first_name' => 'Samuel', 'last_name' => 'Allen', 'role' => 'employee', 'dept' => 0, 'pos' => 1, 'salary' => 105000],
            ['first_name' => 'Tina', 'last_name' => 'Young', 'role' => 'employee', 'dept' => 2, 'pos' => 5, 'salary' => 50000],
        ];

        $employeeModels = [];
        foreach ($employeesData as $index => $empData) {
            $user = User::create([
                'name' => "{$empData['first_name']} {$empData['last_name']}",
                'email' => strtolower($empData['first_name']).'.'.strtolower($empData['last_name']).'@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => $empData['role'],
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ]);

            $hireDate = Carbon::now()->subDays(rand(90, 1500));
            $status = 'active';
            $resignDate = null;

            // Make a couple of employees resigned/terminated
            if ($index === 16) {
                $status = 'resigned';
                $resignDate = Carbon::now()->subDays(15);
            } elseif ($index === 19) {
                $status = 'terminated';
                $resignDate = Carbon::now()->subDays(30);
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'department_id' => $deptModels[$empData['dept']]->id,
                'position_id' => $posModels[$empData['pos']]->id,
                'employee_code' => sprintf('EMP%04d', $index + 1),
                'first_name' => $empData['first_name'],
                'last_name' => $empData['last_name'],
                'email' => strtolower($empData['first_name']).'.'.strtolower($empData['last_name']).'@hranalytics.com',
                'phone' => sprintf('+1-555-%03d-%04d', rand(100, 999), rand(1000, 9999)),
                'hire_date' => $hireDate,
                'resign_date' => $resignDate,
                'salary' => $empData['salary'],
                'status' => $status,
                'tenant_id' => $tenantId,
            ]);

            $employeeModels[] = $employee;
        }

        // Set department managers
        $deptModels[0]->update(['manager_id' => $employeeModels[0]->id]);  // Alice - Engineering
        $deptModels[1]->update(['manager_id' => $employeeModels[5]->id]);  // Fiona - HR
        $deptModels[2]->update(['manager_id' => $employeeModels[8]->id]);  // Ivan - Marketing
        $deptModels[4]->update(['manager_id' => $employeeModels[14]->id]); // Oscar - Operations

        // Seed attendance records for the last 30 days
        $statuses = ['present', 'present', 'present', 'present', 'late', 'present', 'present'];

        foreach ($employeeModels as $employee) {
            if ($employee->status !== 'active') {
                continue;
            }

            for ($day = 29; $day >= 0; $day--) {
                $date = Carbon::now()->subDays($day);

                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                $status = $statuses[array_rand($statuses)];
                $isAnomaly = rand(1, 50) === 1; // ~2% anomaly rate

                // Some employees occasionally absent
                if (rand(1, 20) === 1) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                        'check_in' => null,
                        'check_out' => null,
                        'hours_worked' => null,
                        'status' => 'absent',
                        'is_anomaly' => false,
                        'anomaly_score' => 0,
                        'tenant_id' => $tenantId,
                    ]);

                    continue;
                }

                $checkInHour = $status === 'late' ? rand(9, 10) : rand(7, 8);
                $checkInMinute = rand(0, 59);
                $checkIn = $date->copy()->setHour($checkInHour)->setMinute($checkInMinute);

                $hoursWorked = rand(7, 10) + rand(0, 59) / 60;
                $checkOut = $checkIn->copy()->addMinutes((int) ($hoursWorked * 60));

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'hours_worked' => round($hoursWorked, 2),
                    'status' => $status,
                    'is_anomaly' => $isAnomaly,
                    'anomaly_score' => $isAnomaly ? round(rand(70, 99) / 100, 4) : 0,
                    'tenant_id' => $tenantId,
                ]);
            }
        }

        // Seed leave records
        $leaveTypes = ['annual', 'sick', 'personal', 'maternity'];
        $leaveStatuses = ['approved', 'approved', 'approved', 'pending', 'rejected'];

        foreach ($employeeModels as $employee) {
            if ($employee->status !== 'active') {
                continue;
            }

            $numLeaves = rand(1, 4);
            for ($i = 0; $i < $numLeaves; $i++) {
                $startDate = Carbon::now()->subDays(rand(1, 180));
                $days = rand(1, 5);
                $endDate = $startDate->copy()->addWeekdays($days);
                $leaveStatus = $leaveStatuses[array_rand($leaveStatuses)];

                $reasons = [
                    'annual' => ['Family vacation', 'Personal trip', 'Rest and recovery', 'Holiday travel'],
                    'sick' => ['Flu symptoms', 'Medical appointment', 'Recovery from illness', 'Dental procedure'],
                    'personal' => ['Family event', 'Home maintenance', 'Legal appointment', 'Moving to new home'],
                    'maternity' => ['Maternity leave', 'Parental bonding time'],
                ];

                $type = $leaveTypes[array_rand($leaveTypes)];
                $reasonOptions = $reasons[$type];

                Leave::create([
                    'employee_id' => $employee->id,
                    'type' => $type,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'days' => $days,
                    'reason' => $reasonOptions[array_rand($reasonOptions)],
                    'status' => $leaveStatus,
                    'approved_by' => $leaveStatus !== 'pending' ? $adminUser->id : null,
                    'tenant_id' => $tenantId,
                ]);
            }
        }
    }
}
