<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    // Konstanta Status Pembayaran
    public const STATUS_PENDING = 'pending';
    public const STATUS_BERHASIL = 'berhasil';
    public const STATUS_DIBAYAR = 'dibayar';    // Alternatif untuk 'berhasil'
    public const STATUS_GAGAL = 'gagal';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUND = 'refund';
    public const STATUS_CANCELED = 'canceled';
    
    // Konstanta Tipe Pembayaran
    public const TYPE_MANUAL = 'manual';
    public const TYPE_DIRECT = 'direct';
    public const TYPE_REDIRECT = 'redirect';
    public const TYPE_TRIPAY = 'tripay';

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
            self::STATUS_PENDING => 'warning',
            self::STATUS_BERHASIL => 'success',
            self::STATUS_DIBAYAR => 'success',  // Sama dengan berhasil
            self::STATUS_GAGAL => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_REFUND => 'dark',
            self::STATUS_CANCELED => 'secondary'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
    
    // Badge payment type untuk tampilan
    public function getPaymentTypeBadgeAttribute()
    {
        $badges = [
            self::TYPE_MANUAL => 'info',
            self::TYPE_DIRECT => 'primary',
            self::TYPE_REDIRECT => 'success',
            self::TYPE_TRIPAY => 'primary'
        ];
        
        return $badges[$this->payment_type] ?? 'secondary';
    }
}
