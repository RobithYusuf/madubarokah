<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    // Konstanta Status Pengiriman
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $table = 'pengiriman';
    
    protected $fillable = [
        'id_transaksi',
        'origin_city_id', 
        'destination_province_id',
        'destination_city_id',
        'weight',
        'kurir',
        'layanan',
        'service_code',
        'biaya',
        'etd',
        'resi',
        'status',
        'courier_info'
    ];

    protected $casts = [
        'courier_info' => 'array',
        'biaya' => 'decimal:2'
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
            self::STATUS_MENUNGGU_PEMBAYARAN => 'secondary',
            self::STATUS_DIPROSES => 'warning',
            self::STATUS_DIKIRIM => 'primary',
            self::STATUS_DITERIMA => 'success',
            self::STATUS_DIBATALKAN => 'danger'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
}
