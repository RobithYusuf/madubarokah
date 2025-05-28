# Proyek Sistem Informasi Manajemen Bisnis - Toko Madu Barokah

Selamat datang di proyek Sistem Informasi Manajemen Bisnis Toko Madu Barokah. Aplikasi web ini dibangun untuk membantu mengelola berbagai aspek operasional dan penjualan Toko Madu Barokah, mulai dari manajemen produk, transaksi, pelanggan, hingga pelaporan.

## Daftar Isi

1.  [Fitur Utama](#fitur-utama)
2.  [Teknologi yang Digunakan](#teknologi-yang-digunakan)
3.  [Struktur Direktori Proyek (Umum)](#struktur-direktori-proyek-umum)
4.  [Instalasi & Setup](#instalasi--setup)
5.  [Modul Utama](#modul-utama)
    * [Autentikasi](#autentikasi)
    * [Manajemen Produk & Kategori](#manajemen-produk--kategori)
    * [Manajemen Transaksi](#manajemen-transaksi)
    * [Manajemen Pelanggan](#manajemen-pelanggan)
    * [Manajemen Pengiriman](#manajemen-pengiriman)
    * [Manajemen Pembayaran](#manajemen-pembayaran)
    * [Sistem Pelaporan (PDF)](#sistem-pelaporan-pdf)
6.  [Konfigurasi Penting](#konfigurasi-penting)
7.  [Kontribusi](#kontribusi)
8.  [Masalah Umum & Solusi](#masalah-umum--solusi)
9.  [Rencana Pengembangan](#rencana-pengembangan)

## Fitur Utama

* **Dashboard Admin**: Tampilan ringkasan dan statistik penting bisnis.
* **Manajemen Produk**: CRUD (Create, Read, Update, Delete) untuk produk, termasuk detail, harga, stok, dan gambar.
* **Manajemen Kategori Produk**: Pengelompokan produk berdasarkan kategori.
* **Manajemen Transaksi**: Pencatatan transaksi penjualan, pembaruan status, dan detail item.
* **Manajemen Pelanggan (Pembeli)**: Informasi pelanggan dan riwayat transaksi.
* **Integrasi Pembayaran (Diasumsikan)**: Proses pembayaran transaksi (misalnya, integrasi dengan payment gateway jika ada).
* **Manajemen Pengiriman**: Pelacakan status pengiriman dan informasi kurir.
* **Sistem Pelaporan Dinamis**: Pembuatan laporan PDF untuk transaksi, penjualan, pelanggan, dll., dengan filter periode dan status. [cite: 1, 2]
* **Autentikasi & Otorisasi**: Sistem login untuk admin dan (jika ada) pelanggan, dengan pembatasan akses berdasarkan peran.

## Teknologi yang Digunakan

* **Backend**:
    * PHP
    * Laravel Framework
* **Frontend**:
    * Blade Templating Engine
    * HTML5 & CSS3
    * JavaScript (mungkin dengan jQuery atau framework JS ringan lainnya)
    * (Opsional) Bootstrap atau Tailwind CSS untuk styling UI
* **Database**: MySQL / MariaDB (atau database lain yang didukung Laravel)
* **Web Server**: Apache / Nginx
* **Lainnya**:
    * Composer untuk manajemen dependensi PHP
    * Payment gateway : Tripay 
    * Cek Ongkir : Raja Ongkir 

## Struktur Direktori Proyek (Umum)

Distrukturkan mengikuti standar framework Laravel:

* `app/`: Logika inti aplikasi (Models, Controllers, Http, Providers, dll.)
    * `Http/Controllers/Admin/LaporanController.php`: Mengelola logika pelaporan. [cite: 1]
    * `Models/`: Model Eloquent (Transaksi, Produk, User, Kategori, Pembayaran, Pengiriman, DetailTransaksi). [cite: 1]
* `config/`: File konfigurasi aplikasi.
    * `config/app.php`: Konfigurasi aplikasi dasar. [cite: 1]
    * `config/shop.php`: Konfigurasi spesifik toko (nama, alamat, kontak). [cite: 1]
* `database/`: Migrations, seeders, dan factories.
* `public/`: Assets publik (CSS, JS, gambar) dan `index.php`.
* `resources/`:
    * `views/`: File Blade templates.
        * `admin/laporan/`: View untuk modul laporan.
            * `index.blade.php`: Halaman utama laporan dengan filter dan tabel. [cite: 1]
            * `pdf.blade.php`: Template utama untuk PDF. [cite: 1]
            * `pdf/transaksi.blade.php` (dan partials lainnya): Konten detail untuk masing-masing jenis laporan PDF. [cite: 1]
    * `css/`, `js/`, `sass/`: Assets frontend.
* `routes/`: Definisi rute aplikasi (`web.php`, `api.php`).
* `.env`: File konfigurasi environment (database, API keys, dll.).

## Instalasi & Setup

1.  **Clone Repository**:
    ```bash
    git clone [URL_REPOSITORY_ANDA]
    cd [NAMA_DIREKTORI_PROYEK]
    ```
2.  **Install Dependensi PHP**:
    ```bash
    composer install
    ```
3.  **Buat File `.env`**: Salin `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```
4.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```
5.  **Konfigurasi Database**: Atur koneksi database Anda di file `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD, dll.).
6.  **Jalankan Migrasi Database**: Untuk membuat tabel-tabel di database.
    ```bash
    php artisan migrate
    ```
7.  **(Opsional) Jalankan Seeder**: Jika Anda memiliki data awal.
    ```bash
    php artisan db:seed
    ```
8.  **(Opsional) Install Dependensi Frontend**: Jika menggunakan NPM/Yarn.
    ```bash
    npm install
    npm run dev # atau build
    ```
9.  **Atur Web Server**: Arahkan document root web server Anda ke direktori `public/` proyek.
10. **Akses Aplikasi**: Buka aplikasi melalui browser.

## Modul Utama

### Autentikasi
* Login untuk pengguna (admin).
* (Opsional) Registrasi dan login untuk pelanggan jika ada fitur e-commerce.
* Middleware untuk proteksi rute berdasarkan peran.

### Manajemen Produk & Kategori
* Admin dapat menambah, melihat, mengubah, dan menghapus produk.
* Setiap produk memiliki detail seperti nama, deskripsi, harga, stok, gambar.
* Admin dapat mengelola kategori produk.

### Manajemen Transaksi
* Sistem mencatat setiap transaksi yang masuk.
* Admin dapat melihat daftar transaksi, detail per transaksi (item, jumlah, subtotal, total harga, ongkir).
* Admin dapat memperbarui status transaksi (misalnya: pending, dibayar, diproses, dikirim, selesai, batal). [cite: 1]

### Manajemen Pelanggan
* Data pelanggan (pembeli) tersimpan.
* Admin dapat melihat daftar pelanggan dan riwayat transaksi mereka. [cite: 1]

### Manajemen Pengiriman
* Informasi pengiriman per transaksi (kurir, layanan, biaya, nomor resi).
* Admin dapat memperbarui status pengiriman (misalnya: menunggu pembayaran, diproses, dikirim, diterima). [cite: 1]

### Manajemen Pembayaran
* Informasi pembayaran per transaksi (metode, status, waktu bayar).
* Admin dapat memperbarui status pembayaran (misalnya: pending, berhasil, gagal). [cite: 1]

### Sistem Pelaporan (PDF)
* Menghasilkan laporan PDF untuk berbagai aspek bisnis.
* **Jenis Laporan**: Transaksi, Penjualan, Pelanggan, Pengiriman, Produk. [cite: 1]
* **Fitur**:
    * Filter berdasarkan rentang tanggal. [cite: 1]
    * Filter berdasarkan status transaksi, pembayaran, dan pengiriman. [cite: 1]
    * Ringkasan data (total transaksi, total pendapatan, rata-rata, dll.). [cite: 1]
    * Tabel data detail. [cite: 1]
    * Tombol cetak ke PDF dan tutup.
    * Kop surat dan footer standar. [cite: 1]
    * Desain optimal untuk A4.

## Konfigurasi Penting

* **`.env`**:
    * `APP_NAME`: Nama aplikasi.
    * `APP_URL`: URL dasar aplikasi.
    * `DB_*`: Detail koneksi database.
    * (Jika ada) Kunci API untuk layanan eksternal (payment gateway, kurir, dll.).
* **`config/shop.php`**:
    * Informasi detail toko (nama, tagline, alamat, email, telepon, logo, sosial media, lokasi gudang, jam operasional). [cite: 1]

## Kontribusi

Saat ini proyek dikelola secara internal. Untuk kontribusi, silakan diskusikan terlebih dahulu dengan tim pengembang utama.
(Atau, jika ini proyek open source, Anda bisa menambahkan panduan kontribusi yang lebih detail: fork, branch, pull request, coding standards, dll.)

## Masalah Umum & Solusi

* **Halaman Error / Tidak Tampil**:
    * Pastikan konfigurasi `.env` sudah benar.
    * Cek log error Laravel di `storage/logs/laravel.log`.
    * Pastikan dependensi sudah terinstall (`composer install`).
    * Pastikan migrasi database sudah dijalankan (`php artisan migrate`).
* **Laporan PDF Tidak Sesuai / Error**:
    * **Summary Cards Dobel/Bertumpuk**: Pastikan HTML summary cards HANYA ada di template PDF utama (`admin/laporan/pdf.blade.php`) dan telah dihapus dari file partial (`admin/laporan/pdf/*.blade.php`). Cek juga CSS `@media print`.
    * **Data Tidak Tampil**: Periksa query di `LaporanController.php` dan pastikan variabel dikirim dengan benar ke view.


---

Silakan sesuaikan dan lengkapi README ini sesuai dengan detail spesifik proyek Anda. Bagian seperti "Instalasi & Setup" mungkin perlu disesuaikan jika Anda memiliki langkah-langkah yang lebih spesifik.