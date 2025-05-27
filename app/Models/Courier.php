<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'services'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'services' => 'array'
    ];

    // Scope untuk courier aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Method untuk mendapatkan layanan courier
    public function getServicesListAttribute()
    {
        if (is_array($this->services)) {
            return $this->services;
        }
        
        return json_decode($this->services, true) ?? [];
    }
}
