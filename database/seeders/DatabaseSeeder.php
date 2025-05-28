<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeder untuk kategori dengan warna hex
        DB::table('kategori')->insert([
            ['nama_kategori' => 'Madu Lebah Liar', 'deskripsi' => 'Madu alami tanpa campuran yang diambil langsung dari sarang lebah liar di hutan.', 'warna' => '#FF9800', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk meningkatkan khasiat kesehatan.', 'warna' => '#4CAF50', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kategori' => 'Madu Hutan', 'deskripsi' => 'Madu premium yang dihasilkan dari nektar bunga-bunga hutan tropis.', 'warna' => '#8BC34A', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kategori' => 'Madu Rasa', 'deskripsi' => 'Madu dengan tambahan rasa alami dari buah-buahan atau rempah.', 'warna' => '#E91E63', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kategori' => 'Madu Organik', 'deskripsi' => 'Madu yang dihasilkan dari peternakan lebah organik tanpa bahan kimia.', 'warna' => '#009688', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kategori' => 'Propolis', 'deskripsi' => 'Produk lebah yang kaya antioksidan dan memiliki sifat antimikroba.', 'warna' => '#673AB7', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        $this->command->info('Seeder Kategori berhasil dijalankan!');

        // Seeder untuk produk yang lebih variatif
        DB::table('produk')->insert([
            // Produk Madu Lebah Liar
            ['nama_produk' => 'Madu Lebah Liar Botol Besar', 'id_kategori' => 1, 'harga' => 120000, 'stok' => 40, 'berat' => 800, 'deskripsi' => 'Madu asli lebah liar dengan ukuran 460ml', 'gambar' => 'madu_barokah.png ', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Lebah Liar Botol Sedang', 'id_kategori' => 1, 'harga' => 85000, 'stok' => 25, 'berat' => 500, 'deskripsi' => 'Madu asli lebah liar dengan ukuran 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Lebah Liar Botol Kecil', 'id_kategori' => 1, 'harga' => 45000, 'stok' => 30, 'berat' => 300, 'deskripsi' => 'Madu asli lebah liar dengan ukuran 140ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Produk Madu Herbal
            ['nama_produk' => 'Madu Herbal Jahe', 'id_kategori' => 2, 'harga' => 95000, 'stok' => 20, 'berat' => 450, 'deskripsi' => 'Madu dengan campuran ekstrak jahe untuk meningkatkan imunitas, 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Herbal Temulawak', 'id_kategori' => 2, 'harga' => 98000, 'stok' => 15, 'berat' => 470, 'deskripsi' => 'Madu dengan campuran ekstrak temulawak untuk kesehatan pencernaan, 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Herbal Kunyit', 'id_kategori' => 2, 'harga' => 92000, 'stok' => 18, 'berat' => 460, 'deskripsi' => 'Madu dengan campuran ekstrak kunyit untuk anti-inflamasi, 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Produk Madu Hutan
            ['nama_produk' => 'Madu Hutan Kalimantan', 'id_kategori' => 3, 'harga' => 150000, 'stok' => 12, 'berat' => 650, 'deskripsi' => 'Madu premium dari hutan Kalimantan, 350ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Hutan Sumbawa', 'id_kategori' => 3, 'harga' => 145000, 'stok' => 10, 'berat' => 630, 'deskripsi' => 'Madu premium dari hutan Sumbawa, 350ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Produk Madu Rasa
            ['nama_produk' => 'Madu Rasa Strawberry', 'id_kategori' => 4, 'harga' => 75000, 'stok' => 22, 'berat' => 480, 'deskripsi' => 'Madu dengan ekstrak strawberry alami, 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Madu Rasa Lemon', 'id_kategori' => 4, 'harga' => 72000, 'stok' => 25, 'berat' => 470, 'deskripsi' => 'Madu dengan ekstrak lemon alami, 250ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Produk Madu Organik
            ['nama_produk' => 'Madu Organik Premium', 'id_kategori' => 5, 'harga' => 135000, 'stok' => 15, 'berat' => 620, 'deskripsi' => 'Madu organik bersertifikat dari peternakan lebah organik, 350ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // Produk Propolis
            ['nama_produk' => 'Propolis Cair', 'id_kategori' => 6, 'harga' => 110000, 'stok' => 20, 'berat' => 150, 'deskripsi' => 'Propolis cair murni untuk meningkatkan sistem imun, 60ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_produk' => 'Propolis Spray', 'id_kategori' => 6, 'harga' => 85000, 'stok' => 18, 'berat' => 100, 'deskripsi' => 'Propolis dalam bentuk spray untuk kesehatan tenggorokan, 30ml', 'gambar' => 'madu_barokah.png', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        $this->command->info('Seeder Produk berhasil dijalankan!');

        // Seeder untuk user
        DB::table('users')->insert([
            ['nama' => 'Admin', 'username' => 'admin', 'password' => Hash::make('admin'), 'alamat' => 'Jl. Nyai Dasimah No.1, Jakarta Selatan', 'nohp' => '081234567890', 'role' => 'admin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama' => 'Budi Santoso', 'username' => 'budi', 'password' => Hash::make('password'), 'alamat' => 'Jl. Cut Nyak Dien No.2, Bandung', 'nohp' => '081234567891', 'role' => 'pembeli', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama' => 'Siti Rahayu', 'username' => 'siti', 'password' => Hash::make('password'), 'alamat' => 'Jl. Ahmad Yani No.15, Surabaya', 'nohp' => '081234567892', 'role' => 'pembeli', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama' => 'Dian Purnama', 'username' => 'dian', 'password' => Hash::make('password'), 'alamat' => 'Jl. Diponegoro No.45, Yogyakarta', 'nohp' => '081234567893', 'role' => 'pembeli', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama' => 'Rudi Hermawan', 'username' => 'rudi', 'password' => Hash::make('password'), 'alamat' => 'Jl. Sudirman No.123, Jakarta Pusat', 'nohp' => '081234567894', 'role' => 'pembeli', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        $this->command->info('Seeder User berhasil dijalankan!');

        // Seeder untuk transaksi
        // DB::table('transaksi')->insert([
        //     ['id_user' => 2, 'tanggal_transaksi' => Carbon::now()->subDays(5), 'total_harga' => 205000, 'status' => 'selesai',  'created_at' => Carbon::now()->subDays(5), 'updated_at' => Carbon::now()->subDays(3)],
        //     ['id_user' => 3, 'tanggal_transaksi' => Carbon::now()->subDays(3), 'total_harga' => 150000, 'status' => 'dikirim',  'created_at' => Carbon::now()->subDays(3), 'updated_at' => Carbon::now()->subDays(2)],
        //     ['id_user' => 4, 'tanggal_transaksi' => Carbon::now()->subDays(2), 'total_harga' => 95000, 'status' => 'dibayar',  'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(1)],
        //     ['id_user' => 5, 'tanggal_transaksi' => Carbon::now()->subDay(), 'total_harga' => 135000, 'status' => 'pending',  'created_at' => Carbon::now()->subDay(), 'updated_at' => Carbon::now()->subDay()],
        // ]);
        // $this->command->info('Seeder Transaksi berhasil dijalankan!');

        // // Seeder untuk detail transaksi
        // DB::table('detail_transaksi')->insert([
        //     ['id_transaksi' => 1, 'id_produk' => 1, 'jumlah' => 1, 'harga_satuan' => 120000, 'subtotal' => 120000, 'created_at' => Carbon::now()->subDays(5), 'updated_at' => Carbon::now()->subDays(5)],
        //     ['id_transaksi' => 1, 'id_produk' => 4, 'jumlah' => 1, 'harga_satuan' => 85000, 'subtotal' => 85000, 'created_at' => Carbon::now()->subDays(5), 'updated_at' => Carbon::now()->subDays(5)],
        //     ['id_transaksi' => 2, 'id_produk' => 7, 'jumlah' => 1, 'harga_satuan' => 150000, 'subtotal' => 150000, 'created_at' => Carbon::now()->subDays(3), 'updated_at' => Carbon::now()->subDays(3)],
        //     ['id_transaksi' => 3, 'id_produk' => 4, 'jumlah' => 1, 'harga_satuan' => 95000, 'subtotal' => 95000, 'created_at' => Carbon::now()->subDays(2), 'updated_at' => Carbon::now()->subDays(2)],
        //     ['id_transaksi' => 4, 'id_produk' => 11, 'jumlah' => 1, 'harga_satuan' => 135000, 'subtotal' => 135000, 'created_at' => Carbon::now()->subDay(), 'updated_at' => Carbon::now()->subDay()],
        // ]);
        // $this->command->info('Seeder Detail Transaksi berhasil dijalankan!');

        // // Seeder untuk cart
        // DB::table('carts')->insert([
        //     ['id_user' => 2, 'id_produk' => 2, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_user' => 3, 'id_produk' => 5, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_user' => 4, 'id_produk' => 9, 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_user' => 5, 'id_produk' => 12, 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        // ]);
        // $this->command->info('Seeder Cart berhasil dijalankan!');

        // Call additional seeders for Tripay & Raja Ongkir
        $this->call([
            PaymentChannelSeeder::class,
            // PaymentChannelInstructionsSeeder::class,
            CourierSeeder::class,
            ShippingAreasSeeder::class,
        ]);

        $this->command->info('Semua seeder berhasil dijalankan!');
    }
}
