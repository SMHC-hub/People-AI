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
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('pay_period'); // e.g. 2026-08
                $table->decimal('base_salary', 10, 2);
                $table->decimal('bonus', 10, 2)->default(0.00);
                $table->decimal('overtime_pay', 10, 2)->default(0.00);
                $table->decimal('deductions', 10, 2)->default(0.00);
                $table->decimal('net_salary', 10, 2);
                $table->enum('status', ['draft', 'approved', 'processed', 'flagged'])->default('draft');
                $table->boolean('is_anomaly')->default(false);
                $table->enum('anomaly_severity', ['low', 'medium', 'high', 'critical'])->nullable();
                $table->string('anomaly_type')->nullable(); // unexpected_change, abnormal_bonus, overtime_spike, duplicate_payout, department_outlier
                $table->text('anomaly_explanation')->nullable();
                $table->decimal('expected_min', 10, 2)->nullable();
                $table->decimal('expected_max', 10, 2)->nullable();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['tenant_id', 'pay_period']);
                $table->index(['tenant_id', 'is_anomaly']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
