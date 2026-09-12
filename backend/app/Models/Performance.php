<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'review_period',
        'rating',
        'goals_met_percent',
        'promoted',
        'review_date',
        'strengths',
        'areas_for_improvement',
        'reviewer_id',
        'tenant_id',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'goals_met_percent' => 'integer',
        'promoted' => 'boolean',
        'review_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
