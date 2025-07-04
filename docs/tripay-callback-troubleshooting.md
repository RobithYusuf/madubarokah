# Panduan Troubleshooting Tripay Callback

## Masalah yang Diperbaiki

### 1. Error 405 Method Not Allowed
**Penyebab**: Route configuration dan middleware yang kompleks
**Solusi**: 
- Simplifikasi route menjadi single POST endpoint
- Hapus middleware yang tidak perlu
- Tambahkan exception CSRF untuk ngrok

### 2. False Positive Payment Status
**Penyebab**: ConfirmationController mengecek status Tripay API langsung tanpa verifikasi callback
**Solusi**:
- Tambahkan field `callback_verified` di tabel pembayaran
- Hanya update status jika callback terverifikasi
- Tracking callback attempts untuk monitoring

## Konfigurasi Ngrok

### Setup Environment
```bash
# .env file
NGROK_URL=https://your-subdomain.ngrok-free.app
APP_URL=http://127.0.0.1:3001
```

### Jalankan Ngrok
```bash
# Pastikan port sesuai dengan Laravel server
ngrok http 3001
```

### Update Tripay Dashboard
1. Login ke dashboard Tripay
2. Masuk ke Settings > Callback URL
3. Update URL: `https://your-subdomain.ngrok-free.app/api/tripay/callback`

## Testing Callback

### 1. Test Manual dengan Script
```bash
# Update ngrok URL dan private key di file test
php test-ngrok-callback.php
```

### 2. Monitor Log
```bash
# Terminal 1 - Laravel log
tail -f storage/logs/laravel.log | grep -E "(TRIPAY|tripay)"

# Terminal 2 - Callback specific log
tail -f storage/logs/tripay-callback.log
```

### 3. Verifikasi Route
```bash
# Check route list
php artisan route:list | grep tripay

# Test OPTIONS request
curl -X OPTIONS https://your-subdomain.ngrok-free.app/api/tripay/callback -v
```

## Callback Flow yang Benar

```
1. Tripay mengirim POST request ke callback URL
2. TripayCallbackController menerima request
3. Verifikasi signature HMAC SHA256
4. Cari transaksi berdasarkan merchant_ref atau reference
5. Update pembayaran dengan callback_verified = true
6. Update status pembayaran dan transaksi
7. Return success response ke Tripay
```

## Common Issues

### 1. Signature Mismatch
- Pastikan private key di .env benar
- Jangan ada spasi extra di private key
- Signature case-sensitive

### 2. Transaction Not Found
- Callback bisa menggunakan `merchant_ref` atau `reference`
- Controller sudah handle kedua scenario

### 3. CSRF Token Error
- File `VerifyCsrfToken.php` sudah exclude:
  - `api/*`
  - `*.ngrok-free.app/api/tripay/callback`

### 4. Port Mismatch
- Laravel default: 8000
- Vite/Frontend: 3001
- Pastikan ngrok sesuai port Laravel

## Monitoring

### Database Check
```sql
-- Check callback status
SELECT 
    merchant_ref,
    status,
    callback_verified,
    callback_received_at,
    created_at,
    updated_at
FROM pembayaran 
WHERE payment_type = 'tripay'
ORDER BY created_at DESC;

-- Check failed callbacks
SELECT 
    t.merchant_ref,
    t.callback_attempts,
    t.last_callback_attempt,
    p.status,
    p.callback_verified
FROM transaksi t
JOIN pembayaran p ON t.id = p.id_transaksi
WHERE t.callback_attempts > 0
ORDER BY t.last_callback_attempt DESC;
```

### Log Analysis
```bash
# Count callback attempts
grep "Tripay callback received" storage/logs/laravel.log | wc -l

# Find failed verifications
grep "signature.*mismatch" storage/logs/laravel.log

# Check successful updates
grep "Payment status updated via callback" storage/logs/laravel.log
```

## Security Notes

1. **Never expose private key** in logs or responses
2. **Always verify signature** before processing
3. **Use HTTPS** for production (ngrok provides this)
4. **Limit retry attempts** to prevent abuse
5. **Log all attempts** for audit trail