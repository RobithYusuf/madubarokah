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
                'detailTransaksi.produk.kategori',
                'pengiriman',
                'pembayaran'
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.pesanan.index', compact('pesanans'));
        } catch (\Exception $e) {
            \Log::error('Error loading pesanan: ' . $e->getMessage());
            return view('admin.pesanan.index', ['pesanans' => collect()]);
        }
    }

    public function createTestData()
    {
        try {
            // Pastikan ada user dengan role pembeli
            $users = \App\Models\User::where('role', 'pembeli')->get();
            if ($users->count() == 0) {
                $users = collect([
                    \App\Models\User::create([
                        'nama' => 'Budi Santoso',
                        'username' => 'budi_customer',
                        'email' => 'budi@example.com',
                        'password' => bcrypt('password'),
                        'alamat' => 'Jl. Mawar No. 15, RT 03/RW 02, Kelurahan Sumber, Kecamatan Banjarsari',
                        'nohp' => '081234567890',
                        'role' => 'pembeli'
                    ]),
                    \App\Models\User::create([
                        'nama' => 'Siti Nurhaliza',
                        'username' => 'siti_customer',
                        'email' => 'siti@example.com',
                        'password' => bcrypt('password'),
                        'alamat' => 'Jl. Melati No. 8, RT 02/RW 01, Kelurahan Purwosari, Kudus',
                        'nohp' => '081234567891',
                        'role' => 'pembeli'
                    ])
                ]);
            }

            // Pastikan ada produk
            $produk = \App\Models\Produk::first();
            if (!$produk) {
                $kategori = \App\Models\Kategori::create([
                    'nama_kategori' => 'Madu Murni',
                    'deskripsi' => 'Kategori madu murni',
                    'warna' => '#ff6b35'
                ]);

                $produk = \App\Models\Produk::create([
                    'nama_produk' => 'Madu Klanceng 250ml',
                    'id_kategori' => $kategori->id,
                    'harga' => 85000,
                    'stok' => 50,
                    'deskripsi' => 'Madu klanceng murni ukuran 250ml',
                    'berat' => 300
                ]);
            }

            // Data untuk test cases yang konsisten dengan logika bisnis
            $testCases = [
                // Case 1: Pesanan Baru - Belum Bayar
                [
                    'transaction_status' => 'pending',
                    'payment_status' => 'pending',
                    'shipping_status' => 'menunggu_pembayaran',
                    'has_resi' => false,
                    'payment_time' => null,
                    'description' => 'Pesanan baru, menunggu pembayaran'
                ],
                // Case 2: Sudah Bayar - Sedang Diproses
                [
                    'transaction_status' => 'dibayar',
                    'payment_status' => 'berhasil',
                    'shipping_status' => 'diproses',
                    'has_resi' => false,
                    'payment_time' => now()->subHours(2),
                    'description' => 'Sudah bayar, sedang diproses untuk pengiriman'
                ],
                // Case 3: Sudah Bayar - Berhasil, Siap Kirim
                [
                    'transaction_status' => 'berhasil',
                    'payment_status' => 'dibayar',
                    'shipping_status' => 'diproses',
                    'has_resi' => false,
                    'payment_time' => now()->subHours(6),
                    'description' => 'Pembayaran berhasil, siap untuk dikirim'
                ],
                // Case 4: Sedang Dikirim
                [
                    'transaction_status' => 'dikirim',
                    'payment_status' => 'berhasil',
                    'shipping_status' => 'dikirim',
                    'has_resi' => true,
                    'payment_time' => now()->subDays(1),
                    'description' => 'Barang sedang dalam pengiriman'
                ],
                // Case 5: Transaksi Selesai
                [
                    'transaction_status' => 'selesai',
                    'payment_status' => 'berhasil',
                    'shipping_status' => 'diterima',
                    'has_resi' => true,
                    'payment_time' => now()->subDays(3),
                    'description' => 'Transaksi selesai, barang telah diterima'
                ],
                // Case 6: Pesanan Dibatalkan
                [
                    'transaction_status' => 'batal',
                    'payment_status' => 'pending',
                    'shipping_status' => 'dibatalkan',
                    'has_resi' => false,
                    'payment_time' => null,
                    'description' => 'Pesanan dibatalkan sebelum pembayaran'
                ],
                // Case 7: Pembayaran Gagal
                [
                    'transaction_status' => 'gagal',
                    'payment_status' => 'gagal',
                    'shipping_status' => 'menunggu_pembayaran',
                    'has_resi' => false,
                    'payment_time' => null,
                    'description' => 'Pembayaran gagal atau expired'
                ],
                // Case 8: Pembayaran Expired
                [
                    'transaction_status' => 'expired',
                    'payment_status' => 'expired',
                    'shipping_status' => 'menunggu_pembayaran',
                    'has_resi' => false,
                    'payment_time' => null,
                    'description' => 'Pembayaran expired, transaksi dibatalkan'
                ]
            ];

            $names = ['Budi Santoso', 'Siti Nurhaliza', 'Ahmad Rahman', 'Maya Sari', 'Doni Pratama'];
            $addresses = [
                'Jl. Melati No. 20, RT 01/RW 03, Kelurahan Tegalsari, Kudus',
                'Jl. Anggrek No. 8, RT 02/RW 01, Kelurahan Purwosari, Kudus',
                'Jl. Mawar No. 15, RT 05/RW 02, Kelurahan Mlonggo, Jepara',
                'Jl. Kenanga No. 12, RT 03/RW 04, Kelurahan Kauman, Kudus',
                'Jl. Dahlia No. 25, RT 01/RW 02, Kelurahan Barongan, Kudus'
            ];

            $couriers = ['JNE', 'TIKI', 'POS'];
            $services = ['REG', 'YES', 'OKE'];
            $paymentMethods = ['BRIVA', 'MANDIRI', 'BCA', 'QRIS', 'GOPAY'];

            // Buat transaksi berdasarkan test cases
            foreach ($testCases as $index => $testCase) {
                $user = $users->random();
                $merchantRef = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                $quantity = rand(1, 3);
                $subtotal = $quantity * $produk->harga;
                $shippingCost = rand(8000, 15000);
                $totalHarga = $subtotal + $shippingCost;

                // Tentukan tanggal transaksi berdasarkan status
                $transactionDate = match ($testCase['transaction_status']) {
                    'pending' => now()->subHours(rand(1, 12)),
                    'dibayar', 'berhasil' => now()->subDays(rand(1, 2)),
                    'dikirim' => now()->subDays(rand(2, 4)),
                    'selesai' => now()->subDays(rand(5, 10)),
                    'batal', 'gagal', 'expired' => now()->subDays(rand(1, 7)),
                    default => now()->subDays(rand(1, 5))
                };

                // Buat transaksi
                $transaksi = \App\Models\Transaksi::create([
                    'id_user' => $user->id,
                    'merchant_ref' => $merchantRef,
                    'tanggal_transaksi' => $transactionDate,
                    'total_harga' => $totalHarga,
                    'status' => $testCase['transaction_status'],
                    'expired_time' => $transactionDate->addHours(24),
                    'nama_penerima' => $names[array_rand($names)],
                    'telepon_penerima' => '08' . rand(1000000000, 9999999999),
                    'alamat_pengiriman' => $addresses[array_rand($addresses)],
                    'catatan' => $index % 3 == 0 ? $testCase['description'] : null
                ]);

                // Buat detail transaksi
                \App\Models\DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_produk' => $produk->id,
                    'jumlah' => $quantity,
                    'harga_satuan' => $produk->harga,
                    'subtotal' => $subtotal
                ]);

                // Buat data pengiriman yang konsisten
                $courier = $couriers[array_rand($couriers)];
                $service = $services[array_rand($services)];

                \App\Models\Pengiriman::create([
                    'id_transaksi' => $transaksi->id,
                    'destination_province_id' => 10, // Jawa Tengah
                    'destination_city_id' => 155, // Kudus
                    'weight' => $quantity * ($produk->berat ?? 300),
                    'kurir' => $courier,
                    'layanan' => $service,
                    'service_code' => $service,
                    'biaya' => $shippingCost,
                    'status' => $testCase['shipping_status'],
                    'resi' => $testCase['has_resi'] ? $courier . rand(1000000000, 9999999999) : null,
                    'etd' => rand(2, 5) . '-' . rand(3, 7),
                    'courier_info' => json_encode([
                        'courier_name' => $courier,
                        'service_name' => $service,
                        'destination' => 'Kudus, Jawa Tengah',
                        'weight' => $quantity * ($produk->berat ?? 300)
                    ])
                ]);

                // Buat data pembayaran yang konsisten
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                \App\Models\Pembayaran::create([
                    'id_transaksi' => $transaksi->id,
                    'reference' => $merchantRef,
                    'metode' => $paymentMethod,
                    'total_bayar' => $totalHarga,
                    'status' => $testCase['payment_status'],
                    'payment_code' => in_array($paymentMethod, ['BRIVA', 'MANDIRI', 'BCA']) ? rand(1000000000000, 9999999999999) : null,
                    'payment_type' => in_array($paymentMethod, ['QRIS', 'GOPAY']) ? 'tripay' : 'manual',
                    'waktu_bayar' => $testCase['payment_time'],
                    'expired_time' => $transaksi->expired_time
                ]);
            }

            return redirect()->route('admin.pesanan.index')
                ->with('success', '8 data test pesanan dengan skenario lengkap berhasil dibuat! Semua status sudah sesuai logika bisnis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pesanan = Transaksi::with(['user', 'detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->findOrFail($id);

        return view('admin.pesanan.detail', compact('pesanan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dibayar,berhasil,dikirim,selesai,batal,gagal,expired'
        ]);

        try {
            $pesanan = Transaksi::findOrFail($id);

            // Validasi logika bisnis
            $currentStatus = $pesanan->status;
            $newStatus = $request->status;

            // Cek apakah perubahan status valid
            if (!$this->isValidStatusTransition($pesanan, $newStatus)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Perubahan status tidak valid'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Perubahan status tidak valid');
            }

            $pesanan->update(['status' => $newStatus]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status transaksi berhasil diperbarui'
                ]);
            }

            return redirect()->route('admin.pesanan.index')
                ->with('success', 'Status pesanan berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateShipping(Request $request, $id)
    {
        $request->validate([
            'shipping_status' => 'sometimes|in:menunggu_pembayaran,diproses,dikirim,diterima,dibatalkan',
            'resi' => 'nullable|string|max:50'
        ]);

        try {
            $pesanan = Transaksi::with('pengiriman')->findOrFail($id);

            if ($pesanan->pengiriman) {
                $updateData = [];

                if ($request->has('shipping_status')) {
                    $updateData['status'] = $request->shipping_status;

                    // Update transaction status based on shipping status
                    if ($request->shipping_status === 'dikirim') {
                        $pesanan->update(['status' => 'dikirim']);
                    } elseif ($request->shipping_status === 'diterima') {
                        $pesanan->update(['status' => 'selesai']);
                    }
                }

                if ($request->has('resi')) {
                    $updateData['resi'] = $request->resi;
                }

                if (!empty($updateData)) {
                    $pesanan->pengiriman->update($updateData);
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status pengiriman berhasil diperbarui'
                ]);
            }

            return redirect()->route('admin.pesanan.index')
                ->with('success', 'Status pengiriman berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updatePayment(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,berhasil,dibayar,gagal,expired,refund,canceled',
            'waktu_bayar' => 'nullable|date'
        ]);

        try {
            $pesanan = Transaksi::with('pembayaran')->findOrFail($id);

            if ($pesanan->pembayaran) {
                $updateData = [
                    'status' => $request->payment_status
                ];

                if ($request->payment_status === 'berhasil' || $request->payment_status === 'dibayar') {
                    $updateData['waktu_bayar'] = $request->waktu_bayar ?? now();
                    // Update transaction status
                    $pesanan->update(['status' => 'dibayar']);
                }

                $pesanan->pembayaran->update($updateData);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status pembayaran berhasil diperbarui'
                ]);
            }

            return redirect()->route('admin.pesanan.index')
                ->with('success', 'Status pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $pesanan = Transaksi::findOrFail($id);

            // Cek apakah pesanan bisa dihapus
            if (in_array($pesanan->status, ['dikirim', 'selesai'])) {
                return redirect()->back()->with('error', 'Pesanan yang sudah dikirim atau selesai tidak dapat dihapus.');
            }

            // Hapus detail transaksi terlebih dahulu
            $pesanan->detailTransaksi()->delete();

            // Hapus data terkait
            if ($pesanan->pengiriman) {
                $pesanan->pengiriman->delete();
            }
            if ($pesanan->pembayaran) {
                $pesanan->pembayaran->delete();
            }

            // Hapus transaksi
            $pesanan->delete();

            return redirect()->route('admin.pesanan.index')
                ->with('success', 'Pesanan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Helper method untuk validasi perubahan status
    private function isValidStatusTransition($pesanan, $newStatus)
    {
        $currentStatus = $pesanan->status;
        $paymentStatus = $pesanan->pembayaran->status ?? 'pending';
        $shippingStatus = $pesanan->pengiriman->status ?? 'menunggu_pembayaran';

        // Status final tidak bisa diubah
        $finalStatuses = ['selesai', 'batal', 'gagal', 'expired'];
        if (in_array($currentStatus, $finalStatuses)) {
            return false;
        }

        // Validasi berdasarkan status saat ini
        switch ($currentStatus) {
            case 'pending':
                return in_array($newStatus, ['dibayar', 'batal', 'gagal', 'expired']);

            case 'dibayar':
                return in_array($newStatus, ['berhasil', 'dikirim', 'batal']);

            case 'berhasil':
                return in_array($newStatus, ['dikirim', 'batal']);

            case 'dikirim':
                return $newStatus === 'selesai';

            default:
                return false;
        }
    }
}
