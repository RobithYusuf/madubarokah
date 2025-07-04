# Panduan Cache Layanan Pengiriman

## Gambaran Umum

Sistem caching untuk API layanan pengiriman telah diimplementasikan untuk mengurangi beban API RajaOngkir dan mencegah habisnya token/quota API. Cache disimpan selama 1 hari untuk semua kombinasi rute pengiriman.

## Cara Kerja Cache

### 1. Cache Key Format
```
shipping_cost_{origin}_{destination}_{weight}_{courier}
```

Contoh: `shipping_cost_155_105_1000_jne`

### 2. Cache Duration
- **Shipping Cost**: 1 hari (24 jam)
- **Province/City Data**: 1 jam
- **Cache Keys Tracker**: 7 hari

### 3. Cache Flow
```
Request → Check Cache → Found? → Return Cached Data
                      ↓
                    Not Found → Call RajaOngkir API
                                       ↓
                                Store in Cache → Return Data
```

## Manfaat Cache

1. **Performa**: Response time < 0.1 detik untuk data cached vs 2-10 detik untuk API call
2. **Reliability**: Mengurangi timeout dan error dari API eksternal
3. **Cost Saving**: Mengurangi API calls, menghemat quota/token
4. **User Experience**: Loading lebih cepat saat checkout

## Command Line Tools

### 1. Check Cache Status
```bash
php artisan shipping:cache status
```

Output menampilkan:
- Total cache entries
- Active vs expired entries
- Cache size estimation
- Entries per courier
- Courier performance stats

### 2. Clear Cache
```bash
# Clear all shipping cache
php artisan shipping:cache clear

# Clear specific route cache
php artisan shipping:cache clear --origin=155 --destination=105 --courier=jne
```

### 3. Test Cache
```bash
php artisan shipping:cache test
```

Test akan:
- Melakukan 2 request identik
- Membandingkan response time
- Verifikasi cache working correctly

## Monitoring Cache

### Debug Info di Response
Setiap response shipping calculation include debug info:
```json
{
  "success": true,
  "data": [...],
  "debug": {
    "origin_city": "Jakarta",
    "destination_city": "Bekasi",
    "weight": 1000,
    "courier": "jne",
    "source": "rajaongkir_api",
    "cached": true,
    "execution_time": "0.045s"
  }
}
```

### Cache Indicators
- `cached: true` - Data dari cache
- `cached: false` - Fresh API call
- `execution_time < 0.1s` - Biasanya dari cache

## Best Practices

### 1. Cache Warming
Untuk route populer, bisa dilakukan cache warming:
```php
// Contoh cache warming untuk Jakarta ke kota-kota besar
$popularRoutes = [
    ['origin' => 155, 'destination' => 105], // Jakarta-Bekasi
    ['origin' => 155, 'destination' => 106], // Jakarta-Bandung
    ['origin' => 155, 'destination' => 444], // Jakarta-Surabaya
];

foreach ($popularRoutes as $route) {
    foreach (['jne', 'pos', 'tiki'] as $courier) {
        $rajaOngkirService->calculateShippingCost(
            $route['origin'],
            $route['destination'],
            1000, // 1kg standard
            $courier
        );
    }
}
```

### 2. Cache Invalidation
Cache otomatis expire setelah 1 hari, tapi bisa di-clear manual jika:
- Update tarif dari kurir
- Perubahan coverage area
- Maintenance/testing

### 3. Fallback Strategy
Jika API dan cache gagal, sistem punya fallback:
1. Cached data (jika ada)
2. Fallback estimation (untuk kurir non-API)
3. Error message yang user-friendly

## Configuration

### Environment Variables
```env
# Cache driver (redis recommended for production)
CACHE_DRIVER=redis

# Cache prefix untuk multi-tenant
CACHE_PREFIX=madubarokah_

# RajaOngkir settings
RAJAONGKIR_API_KEY=your_api_key_here
RAJAONGKIR_TYPE=starter
```

### Cache TTL Settings
Di `app/Services/RajaOngkirService.php`:
```php
// Shipping cost cache (1 day)
Cache::put($cacheKey, $result, now()->addDays(1));

// Province/city cache (1 hour)
Cache::remember('rajaongkir_provinces', 3600, function () {...});

// Performance stats (24 hours)
Cache::put($performanceKey, $performance, now()->addHours(24));
```

## Troubleshooting

### 1. Cache Not Working
```bash
# Check cache driver
php artisan config:cache
php artisan cache:clear

# Test cache connectivity
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### 2. High Memory Usage
```bash
# Check cache stats
php artisan shipping:cache status

# Clear old entries
php artisan shipping:cache clear
```

### 3. Stale Data
Jika data shipping sudah berubah tapi cache masih lama:
```bash
# Clear specific courier
php artisan shipping:cache clear --courier=jne

# Or clear all
php artisan shipping:cache clear
```

## Performance Metrics

### Expected Performance
- **First Load (API)**: 2-10 seconds
- **Cached Load**: < 100ms
- **Cache Hit Rate**: Target > 80%
- **API Token Saving**: ~90% reduction

### Monitoring Queries
```sql
-- Check shipping requests per day
SELECT DATE(created_at) as date, 
       COUNT(*) as total_requests
FROM log_entries 
WHERE message LIKE 'RajaOngkir calculate shipping%'
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Cache vs API calls ratio
SELECT 
    SUM(CASE WHEN message LIKE '%retrieved from cache%' THEN 1 ELSE 0 END) as cached,
    SUM(CASE WHEN message LIKE '%API response received%' THEN 1 ELSE 0 END) as api_calls
FROM log_entries
WHERE created_at >= NOW() - INTERVAL 1 DAY;
```

## Security Notes

1. **No Sensitive Data**: Cache only stores public shipping rates
2. **User Isolation**: Cache keys include specific route params
3. **Auto Expiration**: Data automatically removed after TTL
4. **No PII**: No personal information in cache keys or values