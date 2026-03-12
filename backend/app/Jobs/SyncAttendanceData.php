<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\DingTalkNotifier;
use App\Services\MLServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncAttendanceData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $tenantId,
        protected ?string $date = null
    ) {
        $this->onQueue('attendance');
        $this->date = $date ?? Carbon::today()->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(MLServiceClient $mlService, DingTalkNotifier $notifier): void
    {
        Log::info('Syncing attendance data and running anomaly detection', [
            'tenant_id' => $this->tenantId,
            'date' => $this->date,
        ]);

        // Get recent attendance records for analysis
        $attendances = Attendance::with('employee.department')
            ->where('tenant_id', $this->tenantId)
            ->where('date', '>=', Carbon::parse($this->date)->subDays(30)->toDateString())
            ->where('date', '<=', $this->date)
            ->get();

        if ($attendances->isEmpty()) {
            Log::info('No attendance records found for anomaly detection');

            return;
        }

        try {
            // Call ML service for anomaly detection
            $result = $mlService->detectAnomalies([
                'attendance_records' => $attendances->toArray(),
                'tenant_id' => $this->tenantId,
                'date' => $this->date,
            ]);

            $anomalyIds = $result['anomaly_ids'] ?? [];
            $anomalyScores = $result['anomaly_scores'] ?? [];

            if (! empty($anomalyIds)) {
                // Update anomaly flags in database
                foreach ($anomalyIds as $index => $id) {
                    Attendance::where('id', $id)->update([
                        'is_anomaly' => true,
                        'anomaly_score' => $anomalyScores[$index] ?? 0.0,
                    ]);
                }

                // Prepare notification data
                $anomalyRecords = Attendance::with('employee')
                    ->whereIn('id', $anomalyIds)
                    ->get()
                    ->map(function ($record) {
                        return [
                            'employee_name' => $record->employee->first_name.' '.$record->employee->last_name,
                            'date' => $record->date->toDateString(),
                            'score' => $record->anomaly_score,
                        ];
                    })
                    ->toArray();

                // Send DingTalk notification
                $notifier->notifyAnomalies($anomalyRecords);

                Log::info('Anomaly detection completed', [
                    'anomalies_found' => count($anomalyIds),
                ]);
            } else {
                Log::info('No anomalies detected');
            }
        } catch (\Exception $e) {
            Log::error('Anomaly detection failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Attendance sync job failed permanently', [
            'tenant_id' => $this->tenantId,
            'date' => $this->date,
            'error' => $exception?->getMessage(),
        ]);
    }
}
