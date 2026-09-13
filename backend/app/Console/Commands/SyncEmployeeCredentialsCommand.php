<?php

namespace App\Console\Commands;

use App\Services\EmployeeCredentialsService;
use Illuminate\Console\Command;

class SyncEmployeeCredentialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:sync-credentials {--password=password : Default password for generated accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision User login accounts for all employees and generate/sync employee_credentials.csv';

    /**
     * Execute the console command.
     */
    public function handle(EmployeeCredentialsService $service): int
    {
        $password = $this->option('password') ?: 'password';
        $this->info("Provisioning credentials for all employees with default password: '{$password}'...");

        $count = $service->syncAllEmployees($password);

        $this->info("✅ Successfully synced! Created {$count} new user accounts.");
        $this->info("📄 Credentials CSV updated at:");
        foreach (EmployeeCredentialsService::getCsvPaths() as $path) {
            $this->line("   - {$path}");
        }

        return Command::SUCCESS;
    }
}
