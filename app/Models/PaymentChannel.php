<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'icon_url',
        'group',
        'fee_flat',
        'fee_percent',
        'minimum_fee',
        'maximum_fee',
        'is_active',
        'instructions'
    ];

    protected $casts = [
        'fee_flat' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'minimum_fee' => 'integer',
        'maximum_fee' => 'integer',
        'is_active' => 'boolean',
        'instructions' => 'array'
    ];

    // Scope untuk channel aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk group tertentu
    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    // Method untuk menghitung fee
    public function calculateFee($amount)
    {
        $fee = $this->fee_flat + ($amount * $this->fee_percent / 100);
        
        if ($this->minimum_fee > 0 && $fee < $this->minimum_fee) {
            $fee = $this->minimum_fee;
        }
        
        if ($this->maximum_fee > 0 && $fee > $this->maximum_fee) {
            $fee = $this->maximum_fee;
        }
        
        return $fee;
    }

    // Method untuk mendapatkan total amount + fee
    public function getTotalAmount($amount)
    {
        return $amount + $this->calculateFee($amount);
    }
}
