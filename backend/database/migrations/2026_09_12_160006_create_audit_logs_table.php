<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('action'); // login, logout, employee_viewed, prediction_generated, payroll_flagged, document_uploaded, model_retrained
                $table->string('resource_type')->nullable(); // Employee, Payroll, ModelVersion, Document
                $table->string('resource_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('status')->default('success'); // success, warning, failure
                $table->json('details')->nullable();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();

                $table->index(['tenant_id', 'action']);
                $table->index(['tenant_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
