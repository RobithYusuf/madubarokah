<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function index()
    {
        try {
            $pesanans = Transaksi::with([
                'user', 
                'detailTransaksi.produk.kategori'
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            
            return view('admin.pesanan.index', compact('pesanans'));
        } catch (\Exception $e) {
            \Log::error('Error loading pesanan: ' . $e->getMessage());
            return view('admin.pesanan.index', ['pesanans' => collect()]);
        }
    }

    // Method untuk testing - buat data dummy
    public function createTestData()
    {
        try {
            // Pastikan ada user dengan role pembeli
            $user = \App\Models\User::where('role', 'pembeli')->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'nama' => 'Customer Test',
                    'username' => 'customer_test',
                    'password' => bcrypt('password'),
                    'alamat' => 'Jl. Test No. 123',
                    'nohp' => '081234567890',
                    'role' => 'pembeli'
                ]);
            }

            // Buat beberapa transaksi dummy
            for ($i = 1; $i <= 3; $i++) {
                $transaksi = Transaksi::create([
                    'id_user' => $user->id,
                    'tanggal_transaksi' => now()->subDays($i),
                    'total_harga' => rand(50000, 200000),
                    'status' => ['pending', 'dibayar', 'dikirim'][array_rand(['pending', 'dibayar', 'dikirim'])],
                    'metode_pembayaran' => ['COD', 'Transfer Bank', 'E-Wallet'][array_rand(['COD', 'Transfer Bank', 'E-Wallet'])]
                ]);

                // Buat detail transaksi
                $produk = \App\Models\Produk::first();
                if ($produk) {
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id,
                        'id_produk' => $produk->id,
                        'jumlah' => rand(1, 3),
                        'harga_satuan' => $produk->harga,
                        'subtotal' => $transaksi->total_harga
                    ]);
                }
            }

            return redirect()->route('admin.pesanan.index')
                           ->with('success', 'Data test berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pesanan = Transaksi::with(['user', 'detailTransaksi.produk', 'pengiriman', 'pembayaran'])
                            ->findOrFail($id);
        
        return view('admin.pesanan.show', compact('pesanan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dibayar,dikirim,selesai,batal'
        ]);

        $pesanan = Transaksi::findOrFail($id);
        $pesanan->update([
            'status' => $request->status
        ]);

        return redirect()->route('admin.pesanan.index')
                        ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pesanan = Transaksi::findOrFail($id);
        
        // Hapus detail transaksi terlebih dahulu
        $pesanan->detailTransaksi()->delete();
        
        // Hapus transaksi
        $pesanan->delete();

        return redirect()->route('admin.pesanan.index')
                        ->with('success', 'Pesanan berhasil dihapus.');
    }
}
