<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeCredentialsService
{
    /**
     * Paths where the CSV file should be maintained.
     */
    public static function getCsvPaths(): array
    {
        return [
            base_path('../employee_credentials.csv'),                 // Root project directory
            public_path('employee_credentials.csv'),                  // Backend public
            base_path('../frontend/public/employee_credentials.csv'), // Frontend public
        ];
    }

    /**
     * CSV Headers
     */
    public static function getHeaders(): array
    {
        return [
            'Employee ID',
            'Full Name',
            'Corporate Email',
            'Default Password',
            'System Role',
            'Department',
            'Position Title',
            'Office Location',
            'Portal URL',
        ];
    }

    /**
     * Sync and provision User accounts for all employees, and generate full CSV.
     */
    public function syncAllEmployees(string $defaultPassword = 'password'): int
    {
        $employees = Employee::with(['department', 'position', 'location', 'user'])->get();
        $provisionedCount = 0;

        $csvRows = [];
        $csvRows[] = self::getHeaders();

        $portalUrl = 'http://localhost:3000/login';
        $hashedPassword = Hash::make($defaultPassword);

        foreach ($employees as $emp) {
            // Match or create user with the employee's corporate email
            $user = User::where('email', $emp->email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'email' => $emp->email,
                    'password' => $hashedPassword,
                    'role' => 'employee',
                    'tenant_id' => $emp->tenant_id ?: 1,
                    'email_verified_at' => now(),
                ]);
                $provisionedCount++;
            }

            if ($emp->user_id !== $user->id) {
                $emp->update(['user_id' => $user->id]);
            }

            $csvRows[] = [
                $emp->employee_code,
                $emp->first_name . ' ' . $emp->last_name,
                $emp->email,
                $defaultPassword,
                'employee',
                $emp->department?->name ?? 'Engineering',
                $emp->position?->title ?? 'Software Engineer',
                $emp->location?->name ?? 'San Francisco HQ',
                $portalUrl,
            ];
        }

        $this->writeCsvToAllPaths($csvRows);

        return $provisionedCount;
    }

    /**
     * Append a newly created employee's credentials to the CSV files.
     */
    public function appendEmployeeCredentials(Employee $employee, string $password = 'password'): void
    {
        $employee->loadMissing(['department', 'position', 'location']);
        $portalUrl = 'http://localhost:3000/login';

        $row = [
            $employee->employee_code,
            $employee->first_name . ' ' . $employee->last_name,
            $employee->email,
            $password,
            'employee',
            $employee->department?->name ?? 'Engineering',
            $employee->position?->title ?? 'Software Engineer',
            $employee->location?->name ?? 'San Francisco HQ',
            $portalUrl,
        ];

        foreach (self::getCsvPaths() as $path) {
            $dir = dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (! file_exists($path)) {
                $this->writeCsvToAllPaths([self::getHeaders(), $row]);
                continue;
            }

            $fp = fopen($path, 'a');
            if ($fp) {
                fputcsv($fp, $row);
                fclose($fp);
            }
        }
    }

    /**
     * Write full CSV dataset to all target paths.
     */
    protected function writeCsvToAllPaths(array $rows): void
    {
        foreach (self::getCsvPaths() as $path) {
            $dir = dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $fp = fopen($path, 'w');
            if ($fp) {
                foreach ($rows as $row) {
                    fputcsv($fp, $row);
                }
                fclose($fp);
            }
        }
    }
}
