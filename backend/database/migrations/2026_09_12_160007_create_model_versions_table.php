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
        if (! Schema::hasTable('model_versions')) {
            Schema::create('model_versions', function (Blueprint $table) {
                $table->id();
                $table->string('model_name'); // attrition_xgb, attendance_isoforest, payroll_ensemble
                $table->string('version'); // v1.2.0
                $table->string('algorithm'); // XGBoost, Isolation Forest, LOF Ensemble
                $table->enum('stage', ['development', 'staging', 'production', 'archived'])->default('production');
                $table->decimal('accuracy', 5, 4)->nullable();
                $table->decimal('precision_score', 5, 4)->nullable();
                $table->decimal('recall_score', 5, 4)->nullable();
                $table->decimal('f1_score', 5, 4)->nullable();
                $table->decimal('roc_auc', 5, 4)->nullable();
                $table->decimal('pr_auc', 5, 4)->nullable();
                $table->decimal('drift_psi', 5, 4)->default(0.0450);
                $table->string('drift_status')->default('healthy'); // healthy, warning, drift_detected
                $table->unsignedInteger('prediction_volume')->default(0);
                $table->decimal('avg_latency_ms', 6, 2)->default(18.50);
                $table->json('hyperparameters')->nullable();
                $table->json('feature_importance')->nullable();
                $table->string('artifact_path')->nullable();
                $table->timestamp('last_trained_at')->nullable();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();

                $table->index(['tenant_id', 'model_name', 'stage']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_versions');
    }
};
