<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
            // Seeder untuk kategori
            DB::table('kategori')->insert([
                ['nama_kategori' => 'Madu Lebah Liar', 'deskripsi' => 'Madu alami tanpa campuran.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
                ['nama_kategori' => 'Madu Herbal', 'deskripsi' => 'Madu yang dicampur dengan herbal untuk kesehatan.'],
            ]);
    
            // Seeder untuk produk
            DB::table('produk')->insert([
                ['nama_produk' => 'Madu Botol Besar', 'id_kategori' => 1, 'harga' => 120000, 'stok' => 40, 'deskripsi' => 'Madu asli dengan ukuran 460ml', 'gambar' => 'madu_besar.jpg'],
                ['nama_produk' => 'Madu Botol Sedang', 'id_kategori' => 1, 'harga' => 85000, 'stok' => 5, 'deskripsi' => 'Madu asli dengan ukuran 256ml', 'gambar' => 'madu_sedang.jpg'],
                ['nama_produk' => 'Madu Botol Kecil', 'id_kategori' => 1, 'harga' => 45000, 'stok' => 10, 'deskripsi' => 'Madu asli dengan ukuran 140ml', 'gambar' => 'madu_kecil.jpg']
            ]);
    
            // Seeder untuk user
            DB::table('users')->insert([
                ['nama' => 'Admin', 'username' => 'admin', 'password' => Hash::make('admin'), 'alamat' => 'Jl. Nyai Dasimah No.1', 'no_telp' => '081234567890', 'role' => 'admin'],
                ['nama' => 'User', 'username' => 'user', 'password' => Hash::make('user'), 'alamat' => 'Jl. Cut Nyak Dien No.2', 'no_telp' => '081234567891', 'role' => 'pembeli']
            ]);
    
            // Seeder untuk transaksi
            DB::table('transaksi')->insert([
                ['id_user' => 1, 'total_harga' => 150000, 'status' => 'dibayar', 'metode_pembayaran' => 'Transfer Bank'],
                ['id_user' => 2, 'total_harga' => 75000, 'status' => 'pending', 'metode_pembayaran' => 'E-Wallet']
            ]);

            DB::table('carts')->insert([
                ['id_user' => 2, 'id_produk' => 2, 'quantity' => 4],
            ]);
        }
    }
