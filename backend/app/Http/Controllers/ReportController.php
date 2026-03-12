<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMonthlyReport;
use App\Services\ReportGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGenerator $reportGenerator
    ) {}

    /**
     * Display a listing of generated reports.
     */
    public function index(Request $request): JsonResponse
    {
        $reports = DB::table('reports')
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($reports);
    }

    /**
     * Generate a new report.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:attendance,leave,turnover,department,comprehensive'],
            'title' => ['required', 'string', 'max:255'],
            'parameters' => ['sometimes', 'array'],
            'parameters.date_from' => ['sometimes', 'date'],
            'parameters.date_to' => ['sometimes', 'date'],
            'parameters.department_id' => ['sometimes', 'integer'],
            'async' => ['sometimes', 'boolean'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $userId = $request->user()->id;

        if ($request->input('async', false)) {
            // Dispatch to queue
            GenerateMonthlyReport::dispatch(
                $validated['type'],
                $validated['title'],
                $tenantId,
                $userId,
                $validated['parameters'] ?? []
            );

            return $this->success(null, 'Report generation has been queued', 202);
        }

        // Generate synchronously
        try {
            $filePath = $this->reportGenerator->generate(
                $validated['type'],
                $tenantId,
                $validated['parameters'] ?? []
            );

            $reportId = DB::table('reports')->insertGetId([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'file_path' => $filePath,
                'generated_by' => $userId,
                'tenant_id' => $tenantId,
                'parameters' => json_encode($validated['parameters'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->success([
                'report_id' => $reportId,
                'file_path' => $filePath,
            ], 'Report generated successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to generate report: '.$e->getMessage(), 500);
        }
    }

    /**
     * Download a generated report.
     */
    public function download(Request $request, int $id): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        $report = DB::table('reports')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (! $report) {
            return $this->error('Report not found', 404);
        }

        if (! Storage::disk('local')->exists($report->file_path)) {
            return $this->error('Report file not found', 404);
        }

        return response()->download(
            Storage::disk('local')->path($report->file_path),
            $report->title.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
