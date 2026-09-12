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
        if (! Schema::hasTable('performances')) {
            Schema::create('performances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('review_period'); // e.g. 2026-Q2
                $table->decimal('rating', 3, 2); // 1.00 - 5.00
                $table->unsignedTinyInteger('goals_met_percent')->default(85);
                $table->boolean('promoted')->default(false);
                $table->date('review_date');
                $table->text('strengths')->nullable();
                $table->text('areas_for_improvement')->nullable();
                $table->unsignedBigInteger('reviewer_id')->nullable();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['tenant_id', 'review_period']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
};
