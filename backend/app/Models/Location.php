<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'country',
        'timezone',
        'tenant_id',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
