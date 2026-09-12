<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ModelOpsController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkforceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel 11.
|
*/

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Workforce Intelligence routes
    Route::prefix('workforce')->group(function () {
        Route::get('/stats', [WorkforceController::class, 'stats']);
        Route::get('/heatmap', [WorkforceController::class, 'heatmap']);
        Route::get('/insights', [WorkforceController::class, 'insights']);
    });

    // Employee routes
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
        Route::get('/{employee}/risk-score', [EmployeeController::class, 'riskScore']);
    });

    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/anomalies', [AttendanceController::class, 'anomalies']);
        Route::get('/stats', [AttendanceController::class, 'stats']);
    });

    // Payroll Intelligence routes
    Route::prefix('payrolls')->group(function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::get('/stats', [PayrollController::class, 'stats']);
        Route::get('/anomalies', [PayrollController::class, 'anomalies']);
        Route::post('/{id}/review', [PayrollController::class, 'review']);
    });

    // Performance review routes
    Route::prefix('performances')->group(function () {
        Route::get('/', [PerformanceController::class, 'index']);
        Route::get('/stats', [PerformanceController::class, 'stats']);
    });

    // Leave routes
    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::post('/{leave}/approve', [LeaveController::class, 'approve']);
        Route::post('/{leave}/reject', [LeaveController::class, 'reject']);
        Route::get('/predictions', [LeaveController::class, 'predictions']);
    });

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/generate', [ReportController::class, 'generate']);
        Route::get('/{id}/download', [ReportController::class, 'download']);
    });

    // Chatbot routes
    Route::prefix('chatbot')->group(function () {
        Route::post('/query', [ChatbotController::class, 'query']);
        Route::post('/policy', [ChatbotController::class, 'policy']);
        Route::get('/history', [ChatbotController::class, 'history']);
    });

    // MLOps & Model Registry routes
    Route::prefix('mlops')->group(function () {
        Route::get('/models', [ModelOpsController::class, 'index']);
        Route::get('/metrics', [ModelOpsController::class, 'metrics']);
        Route::post('/retrain', [ModelOpsController::class, 'retrain']);
        Route::post('/models/{id}/promote', [ModelOpsController::class, 'promote']);
    });

    // Audit logs & Settings routes
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::put('/settings', [SettingsController::class, 'update']);

    // Department routes
    Route::apiResource('departments', DepartmentController::class);
});
