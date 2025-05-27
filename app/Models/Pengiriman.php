<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    
    protected $fillable = [
        'id_transaksi',
        'origin_province_id',
        'origin_city_id', 
        'origin_subdistrict_id',
        'destination_province_id',
        'destination_city_id',
        'destination_subdistrict_id',
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
            'menunggu_pembayaran' => 'secondary',
            'diproses' => 'warning',
            'dikirim' => 'primary',
            'diterima' => 'success',
            'dibatalkan' => 'danger'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
}
