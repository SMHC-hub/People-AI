<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Location;
use App\Models\ModelVersion;
use App\Models\Organization;
use App\Models\Position;
use App\Models\RagDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Deterministically seed PeopleAI enterprise workforce database with 1,000+ employees.
     */
    public function run(): void
    {
        mt_srand(42); // Deterministic seed for reproducible enterprise analytics
        $tenantId = 1;

        // Truncate tables for clean seed
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        DB::table('organizations')->truncate();
        DB::table('locations')->truncate();
        DB::table('departments')->truncate();
        DB::table('positions')->truncate();
        DB::table('employees')->truncate();
        DB::table('attendances')->truncate();
        DB::table('leaves')->truncate();
        DB::table('payrolls')->truncate();
        DB::table('performances')->truncate();
        DB::table('audit_logs')->truncate();
        DB::table('model_versions')->truncate();
        DB::table('rag_documents')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Organization
        Organization::create([
            'id' => 1,
            'name' => 'Acme Global Technologies Inc.',
            'slug' => 'acme-global',
            'domain' => 'hranalytics.com',
            'plan' => 'enterprise',
            'settings' => [
                'retention_threshold' => 70,
                'anomaly_sensitivity' => 'high',
                'auto_report_schedule' => 'weekly',
                'llm_provider' => 'Groq (llama-3.3-70b-versatile)',
            ],
        ]);

        // 2. Locations
        $locationsData = [
            ['name' => 'San Francisco HQ', 'city' => 'San Francisco', 'country' => 'United States', 'timezone' => 'America/Los_Angeles', 'tenant_id' => $tenantId],
            ['name' => 'New York Office', 'city' => 'New York', 'country' => 'United States', 'timezone' => 'America/New_York', 'tenant_id' => $tenantId],
            ['name' => 'London Tech Hub', 'city' => 'London', 'country' => 'United Kingdom', 'timezone' => 'Europe/London', 'tenant_id' => $tenantId],
            ['name' => 'Singapore Regional Hub', 'city' => 'Singapore', 'country' => 'Singapore', 'timezone' => 'Asia/Singapore', 'tenant_id' => $tenantId],
        ];
        $locationIds = [];
        foreach ($locationsData as $loc) {
            $created = Location::create($loc);
            $locationIds[] = $created->id;
        }

        // 3. Departments
        $departmentsData = [
            ['name' => 'Engineering', 'code' => 'ENG', 'tenant_id' => $tenantId],
            ['name' => 'Product & Design', 'code' => 'PRD', 'tenant_id' => $tenantId],
            ['name' => 'Human Resources', 'code' => 'HR', 'tenant_id' => $tenantId],
            ['name' => 'Finance & Legal', 'code' => 'FIN', 'tenant_id' => $tenantId],
            ['name' => 'Enterprise Sales', 'code' => 'SLS', 'tenant_id' => $tenantId],
            ['name' => 'Marketing', 'code' => 'MKT', 'tenant_id' => $tenantId],
            ['name' => 'Global Operations', 'code' => 'OPS', 'tenant_id' => $tenantId],
        ];
        $deptModels = [];
        foreach ($departmentsData as $dept) {
            $deptModels[] = Department::create($dept);
        }

        // 4. Positions across levels
        $positionsData = [
            // Engineering
            ['title' => 'Associate Software Engineer', 'level' => 'junior', 'min_salary' => 75000, 'max_salary' => 95000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'Software Engineer', 'level' => 'mid', 'min_salary' => 100000, 'max_salary' => 135000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'Senior Staff Engineer', 'level' => 'senior', 'min_salary' => 140000, 'max_salary' => 185000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            ['title' => 'Engineering Manager', 'level' => 'manager', 'min_salary' => 170000, 'max_salary' => 220000, 'department_id' => $deptModels[0]->id, 'tenant_id' => $tenantId],
            // Product
            ['title' => 'Product Designer', 'level' => 'mid', 'min_salary' => 85000, 'max_salary' => 115000, 'department_id' => $deptModels[1]->id, 'tenant_id' => $tenantId],
            ['title' => 'Senior Product Manager', 'level' => 'senior', 'min_salary' => 130000, 'max_salary' => 175000, 'department_id' => $deptModels[1]->id, 'tenant_id' => $tenantId],
            // HR
            ['title' => 'People Operations Specialist', 'level' => 'mid', 'min_salary' => 60000, 'max_salary' => 85000, 'department_id' => $deptModels[2]->id, 'tenant_id' => $tenantId],
            ['title' => 'Director of Talent & Culture', 'level' => 'manager', 'min_salary' => 110000, 'max_salary' => 150000, 'department_id' => $deptModels[2]->id, 'tenant_id' => $tenantId],
            // Finance
            ['title' => 'Financial Analyst', 'level' => 'mid', 'min_salary' => 70000, 'max_salary' => 98000, 'department_id' => $deptModels[3]->id, 'tenant_id' => $tenantId],
            ['title' => 'Finance Director', 'level' => 'manager', 'min_salary' => 135000, 'max_salary' => 180000, 'department_id' => $deptModels[3]->id, 'tenant_id' => $tenantId],
            // Sales
            ['title' => 'Account Executive', 'level' => 'mid', 'min_salary' => 70000, 'max_salary' => 110000, 'department_id' => $deptModels[4]->id, 'tenant_id' => $tenantId],
            ['title' => 'VP of Global Sales', 'level' => 'manager', 'min_salary' => 160000, 'max_salary' => 230000, 'department_id' => $deptModels[4]->id, 'tenant_id' => $tenantId],
            // Marketing
            ['title' => 'Growth Marketing Lead', 'level' => 'senior', 'min_salary' => 90000, 'max_salary' => 130000, 'department_id' => $deptModels[5]->id, 'tenant_id' => $tenantId],
            // Operations
            ['title' => 'Operations Analyst', 'level' => 'junior', 'min_salary' => 55000, 'max_salary' => 75000, 'department_id' => $deptModels[6]->id, 'tenant_id' => $tenantId],
            ['title' => 'Head of Global Operations', 'level' => 'manager', 'min_salary' => 125000, 'max_salary' => 165000, 'department_id' => $deptModels[6]->id, 'tenant_id' => $tenantId],
        ];
        $positionModels = [];
        foreach ($positionsData as $pos) {
            $positionModels[] = Position::create($pos);
        }

        // 5. Enterprise Users with RBAC Roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Chen (HR Director)',
                'email' => 'sarah.chen@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'hr_manager',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'David Miller (People Analyst)',
                'email' => 'david.miller@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'hr_analyst',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Marcus Vance (Engineering VP)',
                'email' => 'marcus.vance@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Watson (Staff Engineer)',
                'email' => 'emily.watson@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Watson',
                'email' => 'employee@hranalytics.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], $u);
        }

        // 6. Name pools for 1,000+ realistic synthetic enterprise employees
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth',
            'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen',
            'Christopher', 'Nancy', 'Daniel', 'Lisa', 'Matthew', 'Betty', 'Anthony', 'Margaret', 'Donald', 'Sandra',
            'Mark', 'Ashley', 'Paul', 'Kimberly', 'Steven', 'Emily', 'Andrew', 'Donna', 'Kenneth', 'Michelle',
            'Joshua', 'Carol', 'Kevin', 'Amanda', 'Brian', 'Melissa', 'George', 'Deborah', 'Edward', 'Stephanie',
            'Ronald', 'Rebecca', 'Timothy', 'Sharon', 'Jason', 'Laura', 'Jeffrey', 'Cynthia', 'Ryan', 'Dorothy',
            'Jacob', 'Amy', 'Gary', 'Kathleen', 'Nicholas', 'Angela', 'Eric', 'Shirley', 'Jonathan', 'Emma',
            'Stephen', 'Brenda', 'Larry', 'Pamela', 'Justin', 'Nicole', 'Scott', 'Anna', 'Brandon', 'Samantha',
            'Benjamin', 'Katherine', 'Samuel', 'Christine', 'Gregory', 'Debra', 'Alexander', 'Rachel', 'Patrick', 'Carolyn',
            'Frank', 'Janet', 'Raymond', 'Maria', 'Jack', 'Heather', 'Dennis', 'Diane', 'Jerry', 'Julie',
            'Tyler', 'Joyce', 'Aaron', 'Victoria', 'Jose', 'Kelly', 'Adam', 'Christina', 'Nathan', 'Joan',
            'Henry', 'Evelyn', 'Douglas', 'Judith', 'Zachary', 'Megan', 'Peter', 'Cheryl', 'Kyle', 'Andrea',
            'Walter', 'Hannah', 'Ethan', 'Martha', 'Jeremy', 'Jacqueline', 'Harold', 'Frances', 'Keith', 'Ann',
            'Christian', 'Gloria', 'Roger', 'Jean', 'Noah', 'Kathryn', 'Gerald', 'Alice', 'Carl', 'Teresa',
            'Terry', 'Sara', 'Sean', 'Janice', 'Austin', 'Doris', 'Arthur', 'Madison', 'Lawrence', 'Julia',
            'Jesse', 'Grace', 'Dylan', 'Judy', 'Bryan', 'Abigail', 'Joe', 'Marie', 'Jordan', 'Denise',
            'Billy', 'Beverly', 'Albert', 'Amber', 'Bruce', 'Theresa', 'Willie', 'Marilyn', 'Gabriel', 'Danielle',
            'Logan', 'Diana', 'Alan', 'Brittany', 'Juan', 'Natalie', 'Wayne', 'Sophia', 'Roy', 'Rose',
            'Ralph', 'Isabella', 'Randy', 'Alexis', 'Eugene', 'Kayla', 'Vincent', 'Charlotte', 'Russell', 'Valerie',
            'Louis', 'Monica', 'Philip', 'Mackenzie', 'Bobby', 'Desiree', 'Johnny', 'Tiffany', 'Bradley', 'Meagan'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
            'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts',
            'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes',
            'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper',
            'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward', 'Richardson',
            'Watson', 'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes',
            'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster', 'Jimenez',
            'Powell', 'Jenkins', 'Perry', 'Russell', 'Sullivan', 'Bell', 'Coleman', 'Butler', 'Henderson', 'Barnes'
        ];

        $totalEmployeesToCreate = 1000;
        $employeesToInsert = [];
        $now = Carbon::now();

        for ($i = 1; $i <= $totalEmployeesToCreate; $i++) {
            $fName = $firstNames[array_rand($firstNames)];
            $lName = $lastNames[array_rand($lastNames)];
            $pos = $positionModels[array_rand($positionModels)];
            $deptId = $pos->department_id;
            $locId = $locationIds[array_rand($locationIds)];

            // Realistic salary within position band
            $salary = mt_rand((int) $pos->min_salary, (int) $pos->max_salary);

            // Hire date between 2018-01-01 and 2026-03-01
            $daysAgo = mt_rand(180, 3100);
            $hireDate = $now->copy()->subDays($daysAgo);

            // Tenure in years
            $tenureYears = round($daysAgo / 365, 1);

            // Years since promotion
            $yearsSincePromotion = min($tenureYears, round(mt_rand(0, min((int) ($tenureYears * 10), 75)) / 10, 1));

            // Job satisfaction (1 to 5)
            $jobSatisfaction = mt_rand(1, 5);

            // Performance rating (2.20 to 4.95)
            $performanceRating = round(mt_rand(220, 495) / 100, 2);

            // Calculate realistic attrition probability & risk score (0 - 100)
            $baseRisk = 20.0;
            $shapFactors = [];

            if ($jobSatisfaction <= 2) {
                $baseRisk += 28.0;
                $shapFactors[] = ['factor' => 'Low Job Satisfaction', 'impact' => 28.0, 'direction' => 'risk'];
            } elseif ($jobSatisfaction >= 4) {
                $baseRisk -= 12.0;
                $shapFactors[] = ['factor' => 'High Job Satisfaction', 'impact' => -12.0, 'direction' => 'retention'];
            }

            if ($yearsSincePromotion >= 3.0) {
                $baseRisk += 22.0;
                $shapFactors[] = ['factor' => 'Promotion Stagnation (>3 yrs)', 'impact' => 22.0, 'direction' => 'risk'];
            }

            if ($salary < ($pos->min_salary + $pos->max_salary) / 2) {
                $baseRisk += 14.0;
                $shapFactors[] = ['factor' => 'Below Market Median Salary', 'impact' => 14.0, 'direction' => 'risk'];
            } else {
                $baseRisk -= 10.0;
                $shapFactors[] = ['factor' => 'Competitive Compensation', 'impact' => -10.0, 'direction' => 'retention'];
            }

            // High performance but stagnant promotion = flight risk
            if ($performanceRating >= 4.2 && $yearsSincePromotion >= 2.0) {
                $baseRisk += 18.0;
                $shapFactors[] = ['factor' => 'High Performer Lacking Career Growth', 'impact' => 18.0, 'direction' => 'risk'];
            }

            $riskScore = max(5.0, min(96.0, $baseRisk + (mt_rand(-5, 5))));

            if ($riskScore >= 75.0) {
                $riskLevel = 'critical';
            } elseif ($riskScore >= 55.0) {
                $riskLevel = 'high';
            } elseif ($riskScore >= 30.0) {
                $riskLevel = 'medium';
            } else {
                $riskLevel = 'low';
            }

            // Employee status (92% active, 4% on_leave, 4% resigned/terminated)
            $statusRoll = mt_rand(1, 100);
            if ($statusRoll <= 92) {
                $status = 'active';
                $resignDate = null;
            } elseif ($statusRoll <= 96) {
                $status = 'on_leave';
                $resignDate = null;
            } else {
                $status = 'terminated';
                $resignDate = $now->copy()->subDays(mt_rand(10, 120))->toDateString();
            }

            $empCode = 'EMP' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
            $email = strtolower($fName . '.' . $lName . $i . '@hranalytics.com');

            $employeesToInsert[] = [
                'id' => $i,
                'user_id' => ($i <= 5) ? $i : null,
                'department_id' => $deptId,
                'position_id' => $pos->id,
                'location_id' => $locId,
                'employee_code' => $empCode,
                'first_name' => $fName,
                'last_name' => $lName,
                'email' => $email,
                'phone' => '+1-555-' . mt_rand(100, 999) . '-' . mt_rand(1000, 9999),
                'hire_date' => $hireDate->toDateString(),
                'resign_date' => $resignDate,
                'salary' => $salary,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'years_since_promotion' => $yearsSincePromotion,
                'job_satisfaction' => $jobSatisfaction,
                'performance_rating' => $performanceRating,
                'risk_factors' => json_encode($shapFactors),
                'status' => $status,
                'tenant_id' => $tenantId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks of 200 for fast bulk execution
        foreach (array_chunk($employeesToInsert, 200) as $chunk) {
            DB::table('employees')->insert($chunk);
        }

        // 7. Seed Attendance Records for 100 sample active employees across the last 30 days
        $attendancesToInsert = [];
        $sampleEmployeeIds = range(1, 100);

        for ($day = 30; $day >= 0; $day--) {
            $date = $now->copy()->subDays($day);
            if ($date->isWeekend()) {
                continue; // Skip weekends
            }

            $dateStr = $date->toDateString();

            foreach ($sampleEmployeeIds as $empId) {
                $statusRoll = mt_rand(1, 100);

                if ($statusRoll <= 85) {
                    // Normal on-time arrival
                    $checkInHour = 8;
                    $checkInMin = mt_rand(45, 59);
                    $checkOutHour = 17;
                    $checkOutMin = mt_rand(10, 35);
                    $attStatus = 'present';
                    $isAnomaly = false;
                } elseif ($statusRoll <= 93) {
                    // Late arrival
                    $checkInHour = mt_rand(9, 11);
                    $checkInMin = mt_rand(15, 55);
                    $checkOutHour = 17;
                    $checkOutMin = mt_rand(30, 50);
                    $attStatus = 'late';
                    $isAnomaly = true;
                } elseif ($statusRoll <= 97) {
                    // Early departure
                    $checkInHour = 9;
                    $checkInMin = mt_rand(0, 10);
                    $checkOutHour = 14;
                    $checkOutMin = mt_rand(0, 30);
                    $attStatus = 'early_departure';
                    $isAnomaly = true;
                } else {
                    // Absent
                    $checkInHour = null;
                    $checkInMin = null;
                    $checkOutHour = null;
                    $checkOutMin = null;
                    $attStatus = 'absent';
                    $isAnomaly = true;
                }

                $checkInTime = ($checkInHour !== null) ? $date->copy()->setTime($checkInHour, $checkInMin)->toDateTimeString() : null;
                $checkOutTime = ($checkOutHour !== null) ? $date->copy()->setTime($checkOutHour, $checkOutMin)->toDateTimeString() : null;
                $workHours = ($checkInTime && $checkOutTime) ? round(($checkOutHour - $checkInHour) + (($checkOutMin - $checkInMin) / 60), 2) : 0.00;

                $attendancesToInsert[] = [
                    'employee_id' => $empId,
                    'date' => $dateStr,
                    'check_in' => $checkInTime,
                    'check_out' => $checkOutTime,
                    'hours_worked' => $workHours,
                    'status' => $attStatus,
                    'is_anomaly' => $isAnomaly,
                    'anomaly_score' => $isAnomaly ? round(mt_rand(65, 98) / 100, 2) : 0.05,
                    'tenant_id' => $tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($attendancesToInsert, 300) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        // 8. Seed Payroll Records for all 1,000 employees for the last 3 months
        $payPeriods = ['2026-06', '2026-07', '2026-08'];
        $payrollsToInsert = [];

        foreach ($payPeriods as $period) {
            foreach ($employeesToInsert as $emp) {
                $baseMonthly = round($emp['salary'] / 12, 2);
                $isAnomaly = false;
                $anomalySeverity = null;
                $anomalyType = null;
                $anomalyExplanation = null;
                $bonus = 0.00;
                $overtimePay = 0.00;
                $deductions = round($baseMonthly * 0.18, 2); // 18% taxes and benefits

                // Inject targeted realistic enterprise anomalies in ~3.5% of records
                $anomalyRoll = mt_rand(1, 1000);

                if ($anomalyRoll <= 10) {
                    // Overtime spike anomaly
                    $isAnomaly = true;
                    $anomalySeverity = 'high';
                    $anomalyType = 'overtime_spike';
                    $overtimeHours = mt_rand(45, 70);
                    $hourlyRate = round($emp['salary'] / 2080, 2);
                    $overtimePay = round($overtimeHours * $hourlyRate * 1.5, 2);
                    $anomalyExplanation = "Excessive overtime recorded ({$overtimeHours} hrs, 185% above department baseline). Potential burnout or payroll entry error.";
                } elseif ($anomalyRoll <= 20) {
                    // Abnormal bonus payout
                    $isAnomaly = true;
                    $anomalySeverity = 'critical';
                    $anomalyType = 'abnormal_bonus';
                    $bonus = round($baseMonthly * 2.5, 2);
                    $anomalyExplanation = "Unusually high bonus payout ($" . number_format($bonus, 2) . ") exceeding executive approval threshold.";
                } elseif ($anomalyRoll <= 30) {
                    // Unexpected compensation spike
                    $isAnomaly = true;
                    $anomalySeverity = 'medium';
                    $anomalyType = 'unexpected_salary_spike';
                    $bonus = round($baseMonthly * 0.45, 2);
                    $anomalyExplanation = "Total monthly compensation deviates by +45% compared to preceding 3-month average.";
                } elseif ($anomalyRoll <= 35) {
                    // Duplicate payment candidate
                    $isAnomaly = true;
                    $anomalySeverity = 'critical';
                    $anomalyType = 'duplicate_payout';
                    $overtimePay = $baseMonthly;
                    $anomalyExplanation = "Identical dual payment detected on consecutive processing cycles.";
                }

                $netSalary = round($baseMonthly + $bonus + $overtimePay - $deductions, 2);

                $payrollsToInsert[] = [
                    'employee_id' => $emp['id'],
                    'pay_period' => $period,
                    'base_salary' => $baseMonthly,
                    'bonus' => $bonus,
                    'overtime_pay' => $overtimePay,
                    'deductions' => $deductions,
                    'net_salary' => $netSalary,
                    'status' => $isAnomaly ? 'flagged' : 'processed',
                    'is_anomaly' => $isAnomaly,
                    'anomaly_severity' => $anomalySeverity,
                    'anomaly_type' => $anomalyType,
                    'anomaly_explanation' => $anomalyExplanation,
                    'expected_min' => round($baseMonthly * 0.95 - $deductions, 2),
                    'expected_max' => round($baseMonthly * 1.15 - $deductions, 2),
                    'tenant_id' => $tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($payrollsToInsert, 250) as $chunk) {
            DB::table('payrolls')->insert($chunk);
        }

        // 9. Seed Performance Reviews for first 200 employees (2025-Q4, 2026-Q1, 2026-Q2)
        $performancesToInsert = [];
        $periods = [
            ['period' => '2025-Q4', 'date' => '2025-12-15'],
            ['period' => '2026-Q1', 'date' => '2026-03-20'],
            ['period' => '2026-Q2', 'date' => '2026-06-25'],
        ];

        for ($empId = 1; $empId <= 200; $empId++) {
            foreach ($periods as $p) {
                $rating = round(mt_rand(280, 500) / 100, 2);
                $goalsMet = mt_rand(70, 100);
                $promoted = ($rating >= 4.5 && mt_rand(1, 10) <= 3);

                $performancesToInsert[] = [
                    'employee_id' => $empId,
                    'review_period' => $p['period'],
                    'rating' => $rating,
                    'goals_met_percent' => $goalsMet,
                    'promoted' => $promoted,
                    'review_date' => $p['date'],
                    'strengths' => 'Strong domain expertise, proactive cross-functional collaboration, and consistent milestone delivery.',
                    'areas_for_improvement' => 'Expand mentorship of junior peers and streamline documentation of architectural specifications.',
                    'reviewer_id' => mt_rand(1, 4),
                    'tenant_id' => $tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($performancesToInsert, 200) as $chunk) {
            DB::table('performances')->insert($chunk);
        }

        // 10. Seed Model Versions into MLOps Registry
        $models = [
            [
                'model_name' => 'attrition_xgb',
                'version' => 'v2.1.0',
                'algorithm' => 'XGBoostClassifier',
                'stage' => 'production',
                'accuracy' => 0.9120,
                'precision_score' => 0.8840,
                'recall_score' => 0.8650,
                'f1_score' => 0.8744,
                'roc_auc' => 0.9410,
                'pr_auc' => 0.9180,
                'drift_psi' => 0.0420,
                'drift_status' => 'healthy',
                'prediction_volume' => 1420,
                'avg_latency_ms' => 18.50,
                'hyperparameters' => [
                    'max_depth' => 5,
                    'learning_rate' => 0.08,
                    'n_estimators' => 180,
                    'subsample' => 0.85,
                    'scale_pos_weight' => 2.4,
                ],
                'feature_importance' => [
                    'years_since_promotion' => 0.28,
                    'job_satisfaction' => 0.24,
                    'salary_ratio_to_median' => 0.18,
                    'overtime_frequency' => 0.14,
                    'performance_rating' => 0.10,
                    'tenure_years' => 0.06,
                ],
                'artifact_path' => 'models/attrition_xgb_v2.1.0.joblib',
                'last_trained_at' => $now->copy()->subDays(4),
                'tenant_id' => $tenantId,
            ],
            [
                'model_name' => 'attendance_isoforest',
                'version' => 'v1.4.0',
                'algorithm' => 'IsolationForest',
                'stage' => 'production',
                'accuracy' => 0.9450,
                'precision_score' => 0.8920,
                'recall_score' => 0.9150,
                'f1_score' => 0.9034,
                'roc_auc' => 0.9520,
                'pr_auc' => 0.9310,
                'drift_psi' => 0.0380,
                'drift_status' => 'healthy',
                'prediction_volume' => 32400,
                'avg_latency_ms' => 12.20,
                'hyperparameters' => [
                    'n_estimators' => 150,
                    'contamination' => 0.05,
                    'max_samples' => 'auto',
                ],
                'feature_importance' => [
                    'check_in_deviation_min' => 0.38,
                    'total_work_hours' => 0.30,
                    'consecutive_lates' => 0.20,
                    'early_departure_min' => 0.12,
                ],
                'artifact_path' => 'models/attendance_isoforest_v1.4.0.joblib',
                'last_trained_at' => $now->copy()->subDays(7),
                'tenant_id' => $tenantId,
            ],
            [
                'model_name' => 'payroll_ensemble',
                'version' => 'v1.0.0',
                'algorithm' => 'LOF + Isolation Forest Ensemble',
                'stage' => 'production',
                'accuracy' => 0.9680,
                'precision_score' => 0.9230,
                'recall_score' => 0.8950,
                'f1_score' => 0.9088,
                'roc_auc' => 0.9650,
                'pr_auc' => 0.9420,
                'drift_psi' => 0.0290,
                'drift_status' => 'healthy',
                'prediction_volume' => 3000,
                'avg_latency_ms' => 24.10,
                'hyperparameters' => [
                    'n_neighbors' => 20,
                    'iqr_multiplier' => 1.5,
                    'z_score_threshold' => 3.0,
                ],
                'feature_importance' => [
                    'overtime_to_base_ratio' => 0.35,
                    'bonus_deviation_from_median' => 0.32,
                    'month_over_month_spike' => 0.21,
                    'department_salary_outlier' => 0.12,
                ],
                'artifact_path' => 'models/payroll_ensemble_v1.0.0.joblib',
                'last_trained_at' => $now->copy()->subDays(2),
                'tenant_id' => $tenantId,
            ],
            [
                'model_name' => 'attrition_logistic',
                'version' => 'v1.0.0',
                'algorithm' => 'LogisticRegression Baseline',
                'stage' => 'staging',
                'accuracy' => 0.8250,
                'precision_score' => 0.7620,
                'recall_score' => 0.7410,
                'f1_score' => 0.7513,
                'roc_auc' => 0.8650,
                'pr_auc' => 0.8120,
                'drift_psi' => 0.0510,
                'drift_status' => 'healthy',
                'prediction_volume' => 500,
                'avg_latency_ms' => 4.80,
                'hyperparameters' => ['C' => 1.0, 'solver' => 'lbfgs', 'max_iter' => 1000],
                'feature_importance' => null,
                'artifact_path' => 'models/attrition_logistic_v1.0.0.joblib',
                'last_trained_at' => $now->copy()->subDays(12),
                'tenant_id' => $tenantId,
            ],
            [
                'model_name' => 'attrition_rf',
                'version' => 'v1.1.0',
                'algorithm' => 'RandomForest Baseline',
                'stage' => 'staging',
                'accuracy' => 0.8780,
                'precision_score' => 0.8350,
                'recall_score' => 0.8120,
                'f1_score' => 0.8233,
                'roc_auc' => 0.9050,
                'pr_auc' => 0.8710,
                'drift_psi' => 0.0460,
                'drift_status' => 'healthy',
                'prediction_volume' => 500,
                'avg_latency_ms' => 14.50,
                'hyperparameters' => ['n_estimators' => 100, 'max_depth' => 8],
                'feature_importance' => null,
                'artifact_path' => 'models/attrition_rf_v1.1.0.joblib',
                'last_trained_at' => $now->copy()->subDays(10),
                'tenant_id' => $tenantId,
            ],
        ];

        foreach ($models as $m) {
            ModelVersion::create($m);
        }

        // 11. Seed RAG Documents
        $ragDocs = [
            ['title' => 'Global Employee Handbook 2026', 'category' => 'policy', 'file_type' => 'pdf', 'file_size_bytes' => 2450000, 'chunk_count' => 24, 'version' => '2.4', 'status' => 'indexed', 'allowed_roles' => 'all', 'tenant_id' => $tenantId],
            ['title' => 'Remote Work & Hybrid Attendance Guidelines', 'category' => 'policy', 'file_type' => 'pdf', 'file_size_bytes' => 1180000, 'chunk_count' => 12, 'version' => '1.8', 'status' => 'indexed', 'allowed_roles' => 'all', 'tenant_id' => $tenantId],
            ['title' => 'Comprehensive Health & Parental Leave Policy', 'category' => 'benefits', 'file_type' => 'docx', 'file_size_bytes' => 950000, 'chunk_count' => 16, 'version' => '3.1', 'status' => 'indexed', 'allowed_roles' => 'all', 'tenant_id' => $tenantId],
            ['title' => 'Compensation, Equity & Bonus Incentive Framework', 'category' => 'compliance', 'file_type' => 'pdf', 'file_size_bytes' => 1840000, 'chunk_count' => 18, 'version' => '2.0', 'status' => 'indexed', 'allowed_roles' => 'manager_up', 'tenant_id' => $tenantId],
            ['title' => 'Global Travel & Discretionary Expense Policy', 'category' => 'policy', 'file_type' => 'pdf', 'file_size_bytes' => 840000, 'chunk_count' => 10, 'version' => '1.5', 'status' => 'indexed', 'allowed_roles' => 'all', 'tenant_id' => $tenantId],
        ];

        foreach ($ragDocs as $rd) {
            RagDocument::create($rd);
        }

        // 12. Seed Enterprise Audit Logs
        $auditLogs = [
            ['user_id' => 1, 'user_name' => 'Admin User', 'action' => 'login', 'resource_type' => 'Auth', 'resource_id' => '1', 'ip_address' => '127.0.0.1', 'status' => 'success', 'details' => ['method' => 'bearer_token']],
            ['user_id' => 1, 'user_name' => 'Admin User', 'action' => 'model_promoted', 'resource_type' => 'ModelVersion', 'resource_id' => 'attrition_xgb_v2.1.0', 'ip_address' => '127.0.0.1', 'status' => 'success', 'details' => ['from' => 'staging', 'to' => 'production', 'metric' => 'f1_score_0.8744']],
            ['user_id' => 2, 'user_name' => 'Sarah Chen', 'action' => 'employee_viewed', 'resource_type' => 'Employee', 'resource_id' => 'EMP00042', 'ip_address' => '192.168.1.45', 'status' => 'success', 'details' => ['view' => 'risk_profile']],
            ['user_id' => 2, 'user_name' => 'Sarah Chen', 'action' => 'prediction_generated', 'resource_type' => 'MLInference', 'resource_id' => 'EMP00042', 'ip_address' => '192.168.1.45', 'status' => 'success', 'details' => ['model' => 'attrition_xgb', 'score' => 78.4]],
            ['user_id' => 3, 'user_name' => 'David Miller', 'action' => 'payroll_flagged', 'resource_type' => 'Payroll', 'resource_id' => 'PAY-202608-019', 'ip_address' => '192.168.1.88', 'status' => 'warning', 'details' => ['reason' => 'overtime_spike_58hrs']],
            ['user_id' => 1, 'user_name' => 'Admin User', 'action' => 'document_uploaded', 'resource_type' => 'RagDocument', 'resource_id' => 'Employee Handbook 2026', 'ip_address' => '127.0.0.1', 'status' => 'success', 'details' => ['chunks' => 24, 'vector_engine' => 'ChromaDB']],
        ];

        foreach ($auditLogs as $al) {
            $al['tenant_id'] = $tenantId;
            AuditLog::create($al);
        }

        // 13. Provision User accounts for all employees and sync credentials CSV
        app(\App\Services\EmployeeCredentialsService::class)->syncAllEmployees('password');
    }
}
