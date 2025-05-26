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
    ];

    protected $casts = [
        'waktu_bayar' => 'datetime',
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
            'gagal' => 'danger'
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
}
