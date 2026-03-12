<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * Test listing attendance records.
     */
    public function test_can_list_attendance_records(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => Carbon::today()->setHour(8)->setMinute(30),
            'check_out' => Carbon::today()->setHour(17)->setMinute(30),
            'hours_worked' => 9.0,
            'status' => 'present',
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $user->tenant_id,
        ]);

        $response = $this->getJson('/api/attendance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'employee_id', 'date', 'status'],
                    ],
                ],
            ]);
    }

    /**
     * Test employee can check in.
     */
    public function test_employee_can_check_in(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        $response = $this->postJson('/api/attendance/check-in', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Check-in recorded successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Test duplicate check-in is rejected.
     */
    public function test_duplicate_check_in_is_rejected(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => Carbon::now(),
            'status' => 'present',
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $user->tenant_id,
        ]);

        $response = $this->postJson('/api/attendance/check-in', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Employee already checked in today',
            ]);
    }

    /**
     * Test employee can check out.
     */
    public function test_employee_can_check_out(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => Carbon::today()->setHour(8)->setMinute(0),
            'status' => 'present',
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $user->tenant_id,
        ]);

        $response = $this->postJson('/api/attendance/check-out', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Check-out recorded successfully',
            ]);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance->check_out);
        $this->assertNotNull($attendance->hours_worked);
    }

    /**
     * Test check-out fails without check-in.
     */
    public function test_check_out_fails_without_check_in(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        $response = $this->postJson('/api/attendance/check-out', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No check-in record found for today',
            ]);
    }

    /**
     * Test attendance statistics endpoint.
     */
    public function test_can_get_attendance_stats(): void
    {
        $user = $this->authenticateUser('admin');
        $employee = $this->createEmployee($user->tenant_id);

        // Create a few attendance records
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::today()->subDays($i);
            if ($date->isWeekend()) {
                continue;
            }

            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
                'check_in' => $date->copy()->setHour(8),
                'check_out' => $date->copy()->setHour(17),
                'hours_worked' => 9.0,
                'status' => $i === 0 ? 'late' : 'present',
                'is_anomaly' => false,
                'anomaly_score' => 0,
                'tenant_id' => $user->tenant_id,
            ]);
        }

        $response = $this->getJson('/api/attendance/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'total_records',
                    'present',
                    'late',
                    'absent',
                    'anomalies',
                    'average_hours_worked',
                    'attendance_rate',
                ],
            ]);
    }

    /**
     * Test filtering attendance by employee.
     */
    public function test_can_filter_attendance_by_employee(): void
    {
        $user = $this->authenticateUser('admin');
        $employee1 = $this->createEmployee($user->tenant_id);
        $employee2 = $this->createEmployee($user->tenant_id);

        Attendance::create([
            'employee_id' => $employee1->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => Carbon::now(),
            'status' => 'present',
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $user->tenant_id,
        ]);

        Attendance::create([
            'employee_id' => $employee2->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => Carbon::now(),
            'status' => 'present',
            'is_anomaly' => false,
            'anomaly_score' => 0,
            'tenant_id' => $user->tenant_id,
        ]);

        $response = $this->getJson("/api/attendance?employee_id={$employee1->id}");

        $response->assertStatus(200);
        $data = $response->json('data.data');
        foreach ($data as $record) {
            $this->assertEquals($employee1->id, $record['employee_id']);
        }
    }

    /**
     * Test check-in requires valid employee_id.
     */
    public function test_check_in_requires_valid_employee(): void
    {
        $this->authenticateUser('admin');

        $response = $this->postJson('/api/attendance/check-in', [
            'employee_id' => 99999,
        ]);

        $response->assertStatus(422);
    }
}
