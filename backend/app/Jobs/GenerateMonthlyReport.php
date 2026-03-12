<?php

namespace App\Jobs;

use App\Services\DingTalkNotifier;
use App\Services\ReportGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $type,
        protected string $title,
        protected int $tenantId,
        protected int $generatedBy,
        protected array $parameters = []
    ) {
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(ReportGenerator $reportGenerator, DingTalkNotifier $notifier): void
    {
        Log::info('Generating report', [
            'type' => $this->type,
            'title' => $this->title,
            'tenant_id' => $this->tenantId,
        ]);

        try {
            $filePath = $reportGenerator->generate(
                $this->type,
                $this->tenantId,
                $this->parameters
            );

            DB::table('reports')->insert([
                'title' => $this->title,
                'type' => $this->type,
                'file_path' => $filePath,
                'generated_by' => $this->generatedBy,
                'tenant_id' => $this->tenantId,
                'parameters' => json_encode($this->parameters),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Notify via DingTalk
            $notifier->notifyReportGenerated(
                $this->title,
                config('app.url').'/reports/download/'.basename($filePath)
            );

            Log::info('Report generated successfully', [
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'type' => $this->type,
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
        Log::error('Report generation job failed permanently', [
            'type' => $this->type,
            'title' => $this->title,
            'error' => $exception?->getMessage(),
        ]);
    }
}
