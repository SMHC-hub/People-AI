<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'tenant_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'tenant_id');
    }
}
