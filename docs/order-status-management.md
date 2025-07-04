# Panduan Manajemen Status Pesanan

## Gambaran Umum

Sistem manajemen pesanan telah disederhanakan untuk memudahkan pengelolaan status pesanan bagi admin dan meningkatkan transparansi bagi pelanggan.

## Status Pesanan

### 1. **Pending** (Menunggu Pembayaran)
- Status awal setelah pelanggan melakukan checkout
- Pesanan menunggu konfirmasi pembayaran
- Admin tidak perlu melakukan tindakan pada tahap ini

### 2. **Dibayar** (Pembayaran Terverifikasi)
- Status otomatis setelah pembayaran terverifikasi
- Untuk pembayaran manual: Admin verifikasi bukti transfer
- Untuk pembayaran otomatis (Tripay): Status berubah otomatis setelah callback berhasil
- **Tindakan Admin**: Pilih kurir dan input nomor resi

### 3. **Dikirim** (Dalam Pengiriman)
- Status setelah admin input nomor resi
- Pelanggan dapat melacak pengiriman
- **Tindakan Admin**: Tidak ada (menunggu konfirmasi penerimaan)

### 4. **Selesai** (Pesanan Diterima)
- Status akhir setelah pelanggan konfirmasi penerimaan
- Pesanan dianggap selesai
- **Tindakan Admin**: Tidak ada

### 5. **Batal** (Pesanan Dibatalkan)
- Dapat dilakukan oleh admin atau sistem (pembayaran expired)
- Stok produk dikembalikan otomatis
- **Tindakan Admin**: Konfirmasi alasan pembatalan

## Alur Kerja Admin

### A. Pesanan Baru (Status: Pending)
```
1. Tidak ada tindakan diperlukan
2. Menunggu pembayaran dari pelanggan
3. Sistem akan otomatis update status jika pembayaran masuk
```

### B. Pesanan Terbayar (Status: Dibayar)
```
1. Buka halaman detail pesanan
2. Pilih kurir pengiriman dari dropdown
3. Input nomor resi pengiriman
4. Klik "Simpan & Kirim"
5. Status otomatis berubah ke "Dikirim"
```

### C. Pesanan Dikirim (Status: Dikirim)
```
1. Tidak ada tindakan diperlukan
2. Menunggu konfirmasi penerimaan dari pelanggan
3. Pelanggan dapat melacak via nomor resi
```

### D. Pembatalan Pesanan
```
1. Hanya dapat dilakukan pada status "Pending" atau "Dibayar"
2. Klik tombol "Batalkan Pesanan"
3. Pilih alasan pembatalan
4. Konfirmasi pembatalan
5. Stok produk otomatis dikembalikan
```

## Fitur Otomatis

### 1. Update Status Otomatis
- **Pembayaran Tripay**: Status berubah otomatis setelah callback
- **Pembayaran Expired**: Status berubah ke "Batal" otomatis
- **Stok Management**: Stok dikembalikan otomatis saat pembatalan

### 2. Notifikasi Otomatis
- Email notifikasi ke pelanggan setiap perubahan status
- WhatsApp notifikasi untuk status penting (Dibayar, Dikirim)

### 3. Tracking Integration
- Nomor resi otomatis tersinkron dengan kurir
- Pelanggan dapat cek status pengiriman real-time

## Tips untuk Admin

1. **Jangan mengubah status manual** - Biarkan sistem mengelola transisi status
2. **Fokus pada input data** - Admin hanya perlu input kurir dan resi
3. **Verifikasi pembayaran manual dengan teliti** - Cek nominal dan bukti transfer
4. **Gunakan fitur filter** - Untuk melihat pesanan berdasarkan status tertentu
5. **Perhatikan indikator waktu** - Pesanan pending lebih dari 24 jam perlu follow up

## Troubleshooting

### Pesanan stuck di status "Pending"
- Cek apakah pembayaran sudah masuk
- Untuk pembayaran manual, verifikasi bukti transfer
- Untuk Tripay, cek callback status di log

### Tidak bisa ubah status ke "Dikirim"
- Pastikan sudah memilih kurir
- Pastikan nomor resi sudah diinput
- Status harus "Dibayar" untuk bisa dikirim

### Pelanggan tidak terima notifikasi
- Cek email/nomor WhatsApp pelanggan
- Cek log pengiriman notifikasi
- Pastikan service notifikasi berjalan

## Catatan Penting

⚠️ **Callback Verification**: Sistem baru memiliki validasi callback untuk mencegah false positive pada pembayaran Tripay. Pesanan hanya akan berubah status jika callback berhasil diterima dan diverifikasi.

⚠️ **Manual Override**: Admin dengan role Super Admin dapat melakukan override status dalam kondisi darurat melalui menu khusus.