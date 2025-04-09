<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
} 