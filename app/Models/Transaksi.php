<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'id_user',
        'tanggal_transaksi',
        'total_harga',
        'status',
        'metode_pembayaran',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    // Relasi ke Detail Transaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke Pengiriman
    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'id_transaksi');
    }

    // Relasi ke Pembayaran
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_transaksi');
    }

    // Badge status untuk tampilan
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'dibayar' => 'info',
            'dikirim' => 'primary',
            'selesai' => 'success',
            'batal' => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
