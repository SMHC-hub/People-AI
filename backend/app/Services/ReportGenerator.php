<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportGenerator
{
    /**
     * Generate a report based on type.
     */
    public function generate(string $type, int $tenantId, array $parameters = []): string
    {
        $data = match ($type) {
            'attendance' => $this->gatherAttendanceData($tenantId, $parameters),
            'leave' => $this->gatherLeaveData($tenantId, $parameters),
            'turnover' => $this->gatherTurnoverData($tenantId, $parameters),
            'department' => $this->gatherDepartmentData($tenantId, $parameters),
            'comprehensive' => $this->gatherComprehensiveData($tenantId, $parameters),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };

        $html = $this->renderHtml($type, $data);
        $filePath = $this->generatePdf($html, $type, $tenantId);

        return $filePath;
    }

    /**
     * Gather attendance report data.
     */
    protected function gatherAttendanceData(int $tenantId, array $parameters): array
    {
        $dateFrom = $parameters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $parameters['date_to'] ?? Carbon::now()->toDateString();

        $attendances = Attendance::with('employee.department')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $avgHours = $attendances->whereNotNull('hours_worked')->avg('hours_worked');

        return [
            'title' => 'Attendance Report',
            'period' => "{$dateFrom} to {$dateTo}",
            'generated_at' => now()->toDateTimeString(),
            'summary' => [
                'total_records' => $totalRecords,
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'average_hours' => round((float) $avgHours, 2),
                'attendance_rate' => $totalRecords > 0 ? round(($presentCount + $lateCount) / $totalRecords * 100, 2) : 0,
            ],
            'records' => $attendances->take(100)->toArray(),
        ];
    }

    /**
     * Gather leave report data.
     */
    protected function gatherLeaveData(int $tenantId, array $parameters): array
    {
        $dateFrom = $parameters['date_from'] ?? Carbon::now()->startOfYear()->toDateString();
        $dateTo = $parameters['date_to'] ?? Carbon::now()->toDateString();

        $leaves = Leave::with('employee.department')
            ->where('tenant_id', $tenantId)
            ->whereBetween('start_date', [$dateFrom, $dateTo])
            ->get();

        return [
            'title' => 'Leave Report',
            'period' => "{$dateFrom} to {$dateTo}",
            'generated_at' => now()->toDateTimeString(),
            'summary' => [
                'total_requests' => $leaves->count(),
                'approved' => $leaves->where('status', 'approved')->count(),
                'rejected' => $leaves->where('status', 'rejected')->count(),
                'pending' => $leaves->where('status', 'pending')->count(),
                'total_days' => $leaves->where('status', 'approved')->sum('days'),
                'by_type' => $leaves->groupBy('type')->map->count()->toArray(),
            ],
            'records' => $leaves->take(100)->toArray(),
        ];
    }

    /**
     * Gather turnover report data.
     */
    protected function gatherTurnoverData(int $tenantId, array $parameters): array
    {
        $employees = Employee::with(['department', 'position'])
            ->where('tenant_id', $tenantId)
            ->get();

        $activeCount = $employees->where('status', 'active')->count();
        $resignedCount = $employees->where('status', 'resigned')->count();
        $terminatedCount = $employees->where('status', 'terminated')->count();

        return [
            'title' => 'Turnover Report',
            'period' => 'All time',
            'generated_at' => now()->toDateTimeString(),
            'summary' => [
                'total_employees' => $employees->count(),
                'active' => $activeCount,
                'resigned' => $resignedCount,
                'terminated' => $terminatedCount,
                'turnover_rate' => $employees->count() > 0
                    ? round(($resignedCount + $terminatedCount) / $employees->count() * 100, 2)
                    : 0,
            ],
            'by_department' => $employees->groupBy('department.name')->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'active' => $group->where('status', 'active')->count(),
                    'left' => $group->whereIn('status', ['resigned', 'terminated'])->count(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Gather department report data.
     */
    protected function gatherDepartmentData(int $tenantId, array $parameters): array
    {
        $employees = Employee::with(['department', 'position'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        return [
            'title' => 'Department Report',
            'generated_at' => now()->toDateTimeString(),
            'departments' => $employees->groupBy('department.name')->map(function ($group) {
                return [
                    'headcount' => $group->count(),
                    'avg_salary' => round($group->avg('salary'), 2),
                    'positions' => $group->groupBy('position.title')->map->count()->toArray(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Gather comprehensive report data.
     */
    protected function gatherComprehensiveData(int $tenantId, array $parameters): array
    {
        return [
            'title' => 'Comprehensive HR Report',
            'generated_at' => now()->toDateTimeString(),
            'attendance' => $this->gatherAttendanceData($tenantId, $parameters),
            'leave' => $this->gatherLeaveData($tenantId, $parameters),
            'turnover' => $this->gatherTurnoverData($tenantId, $parameters),
            'department' => $this->gatherDepartmentData($tenantId, $parameters),
        ];
    }

    /**
     * Render report data to HTML.
     */
    protected function renderHtml(string $type, array $data): string
    {
        $title = $data['title'] ?? 'HR Report';
        $generatedAt = $data['generated_at'] ?? now()->toDateTimeString();
        $period = $data['period'] ?? 'N/A';

        $summaryHtml = '';
        if (isset($data['summary'])) {
            $summaryHtml = '<table class="summary-table"><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody>';
            foreach ($data['summary'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $displayValue = is_array($value) ? json_encode($value) : $value;
                $summaryHtml .= "<tr><td>{$label}</td><td>{$displayValue}</td></tr>";
            }
            $summaryHtml .= '</tbody></table>';
        }

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
                h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                .meta { color: #7f8c8d; margin-bottom: 20px; }
                .summary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
                .summary-table th { background-color: #3498db; color: white; }
                .summary-table tr:nth-child(even) { background-color: #f2f2f2; }
                .footer { margin-top: 40px; font-size: 12px; color: #95a5a6; border-top: 1px solid #ddd; padding-top: 10px; }
            </style>
        </head>
        <body>
            <h1>{$title}</h1>
            <div class="meta">
                <p>Period: {$period}</p>
                <p>Generated: {$generatedAt}</p>
            </div>
            {$summaryHtml}
            <div class="footer">
                <p>AI HR Analytics Platform - Confidential Report</p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Generate a PDF file from HTML content.
     */
    protected function generatePdf(string $html, string $type, int $tenantId): string
    {
        $fileName = sprintf(
            'reports/%d/%s_%s.pdf',
            $tenantId,
            $type,
            now()->format('Y_m_d_His')
        );

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait');

        Storage::disk('local')->put($fileName, $pdf->output());

        return $fileName;
    }
}
