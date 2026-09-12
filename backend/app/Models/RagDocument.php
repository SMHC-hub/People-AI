<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RagDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'file_path',
        'file_type',
        'file_size_bytes',
        'chunk_count',
        'version',
        'status',
        'allowed_roles',
        'department_id',
        'tenant_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
