<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'rajaongkir_id',
        'province_id',
        'province_name',
        'city_name',
        'type',
        'postal_code'
    ];

    protected $casts = [
        'rajaongkir_id' => 'integer',
        'province_id' => 'integer'
    ];

    // Scope untuk mendapatkan provinsi saja
    public function scopeProvinces($query)
    {
        return $query->select('province_id', 'province_name')
                    ->whereNotNull('province_id')
                    ->groupBy('province_id', 'province_name')
                    ->orderBy('province_name');
    }

    // Scope untuk mendapatkan kota berdasarkan provinsi
    public function scopeCitiesByProvince($query, $provinceId)
    {
        return $query->where('province_id', $provinceId)
                    ->orderBy('city_name');
    }

    // Method untuk format nama lengkap
    public function getFullNameAttribute()
    {
        return $this->city_name . ', ' . $this->province_name;
    }
}
