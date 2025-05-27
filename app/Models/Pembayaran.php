<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    
    protected $fillable = [
        'id_transaksi',
        'reference',
        'metode',
        'total_bayar',
        'status',
        'waktu_bayar',
        'payment_code',
        'payment_url',
        'checkout_url',
        'expired_time',
        'payment_instructions',
        'callback_signature',
        'payment_type',
    ];

    protected $casts = [
        'waktu_bayar' => 'datetime',
        'expired_time' => 'datetime',
        'payment_instructions' => 'array',
        'total_bayar' => 'decimal:2',
    ];

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }
    
    // Badge status untuk tampilan
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'berhasil' => 'success',
            'gagal' => 'danger',
            'expired' => 'secondary',
            'canceled' => 'secondary'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
    
    // Badge payment type untuk tampilan
    public function getPaymentTypeBadgeAttribute()
    {
        $badges = [
            'manual' => 'info',
            'direct' => 'primary',
            'redirect' => 'success'
        ];
        
        return $badges[$this->payment_type] ?? 'secondary';
    }
}
