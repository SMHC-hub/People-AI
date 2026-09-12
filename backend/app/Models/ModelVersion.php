<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_name',
        'version',
        'algorithm',
        'stage',
        'accuracy',
        'precision_score',
        'recall_score',
        'f1_score',
        'roc_auc',
        'pr_auc',
        'drift_psi',
        'drift_status',
        'prediction_volume',
        'avg_latency_ms',
        'hyperparameters',
        'feature_importance',
        'artifact_path',
        'last_trained_at',
        'tenant_id',
    ];

    protected $casts = [
        'hyperparameters' => 'array',
        'feature_importance' => 'array',
        'accuracy' => 'decimal:4',
        'precision_score' => 'decimal:4',
        'recall_score' => 'decimal:4',
        'f1_score' => 'decimal:4',
        'roc_auc' => 'decimal:4',
        'pr_auc' => 'decimal:4',
        'drift_psi' => 'decimal:4',
        'avg_latency_ms' => 'decimal:2',
        'last_trained_at' => 'datetime',
    ];
}
