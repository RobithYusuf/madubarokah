<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    // Konstanta Status Transaksi
    public const STATUS_PENDING = 'pending';
    public const STATUS_DIBAYAR = 'dibayar';
    public const STATUS_BERHASIL = 'berhasil'; // Alternatif 'dibayar' dari Tripay
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';
    public const STATUS_GAGAL = 'gagal';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REFUND = 'refund';

    protected $table = 'transaksi';

    protected $fillable = [
        'id_user',
        'merchant_ref',
        'tanggal_transaksi',
        'total_harga',
        'status',
        'nama_penerima',
        'telepon_penerima',
        'alamat_pengiriman',
        'catatan',
        'expired_time',
        'callback_url',
        'return_url',
        'fee_merchant',
        'fee_customer',
        'callback_data',
        'tripay_reference',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'expired_time' => 'datetime',
        'callback_data' => 'array',
        'fee_merchant' => 'decimal:2',
        'fee_customer' => 'decimal:2',
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
            self::STATUS_PENDING => 'warning',
            self::STATUS_DIBAYAR => 'info',
            self::STATUS_BERHASIL => 'info', // sama dengan 'dibayar'
            self::STATUS_DIKIRIM => 'primary',
            self::STATUS_SELESAI => 'success',
            self::STATUS_BATAL => 'danger',
            self::STATUS_GAGAL => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_REFUND => 'dark'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
