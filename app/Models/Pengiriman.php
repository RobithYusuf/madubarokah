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
        'kurir',
        'layanan',
        'biaya',
        'resi',
        'status',
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
            'diproses' => 'warning',
            'dikirim' => 'primary',
            'diterima' => 'success'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
}
