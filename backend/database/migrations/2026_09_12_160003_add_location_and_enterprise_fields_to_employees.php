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
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('position_id');
            }
            if (! Schema::hasColumn('employees', 'risk_score')) {
                $table->decimal('risk_score', 5, 2)->default(0.00)->after('salary');
            }
            if (! Schema::hasColumn('employees', 'risk_level')) {
                $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low')->after('risk_score');
            }
            if (! Schema::hasColumn('employees', 'years_since_promotion')) {
                $table->decimal('years_since_promotion', 4, 1)->default(0.0)->after('risk_level');
            }
            if (! Schema::hasColumn('employees', 'job_satisfaction')) {
                $table->unsignedTinyInteger('job_satisfaction')->default(3)->after('years_since_promotion');
            }
            if (! Schema::hasColumn('employees', 'performance_rating')) {
                $table->decimal('performance_rating', 3, 2)->default(3.50)->after('job_satisfaction');
            }
            if (! Schema::hasColumn('employees', 'risk_factors')) {
                $table->json('risk_factors')->nullable()->after('performance_rating');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'location_id',
                'risk_score',
                'risk_level',
                'years_since_promotion',
                'job_satisfaction',
                'performance_rating',
                'risk_factors',
            ]);
        });
    }
};
