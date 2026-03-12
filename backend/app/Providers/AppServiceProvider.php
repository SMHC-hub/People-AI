<?php

namespace App\Providers;

use App\Models\Employee;
use App\Policies\EmployeePolicy;
use App\Services\MLServiceClient;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MLServiceClient as a singleton
        $this->app->singleton(MLServiceClient::class, function ($app) {
            return new MLServiceClient();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
