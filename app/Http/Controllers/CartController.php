<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pengiriman;
use App\Models\PaymentChannel;
use App\Models\Courier;
use App\Models\Pembayaran;
use App\Services\RajaOngkirService;
use App\Services\TripayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $rajaOngkirService;
    protected $tripayService;

    public function __construct(RajaOngkirService $rajaOngkirService, TripayService $tripayService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
        $this->tripayService = $tripayService;
    }

    public function index()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk.kategori')->get();
        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->produk->harga;
        });

        return view('frontend.cart.index', compact('cartItems', 'total'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id',
            'quantity' => 'integer|min:1'
        ]);

        $quantity = $request->quantity ?? 1;

        // Check stock availability
        $produk = Produk::findOrFail($request->id_produk);
        if ($produk->stok < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $produk->stok
            ]);
        }

        $cart = Cart::where('id_user', Auth::id())
            ->where('id_produk', $request->id_produk)
            ->first();

        if ($cart) {
            $newQuantity = $cart->quantity + $quantity;
            if ($produk->stok < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $produk->stok
                ]);
            }
            $cart->update(['quantity' => $newQuantity]);
        } else {
            Cart::create([
                'id_user' => Auth::id(),
                'id_produk' => $request->id_produk,
                'quantity' => $quantity
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang!']);
    }

    public function updateCartItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('id', $id)->where('id_user', Auth::id())->first();

        if ($cart) {
            // Check stock
            if ($cart->produk->stok < $request->quantity) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $cart->produk->stok);
            }

            $cart->update(['quantity' => $request->quantity]);
            return redirect()->back()->with('success', 'Jumlah produk berhasil diperbarui');
        }

        return redirect()->back()->with('error', 'Produk tidak ditemukan');
    }

    public function removeFromCart($id)
    {
        Cart::where('id', $id)->where('id_user', Auth::id())->delete();
        return redirect()->back()->with('success', 'Produk dihapus dari keranjang');
    }

    public function checkout()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk.kategori')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Keranjang kosong');
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->produk->harga;
        });

        // Get provinces for shipping - with fallback
        try {
            $provinces = $this->rajaOngkirService->getCachedProvinces();

            // If cached provinces is empty, try to get from database directly
            if ($provinces->isEmpty()) {
                $provinces = \App\Models\ShippingArea::provinces()->get();
                \Log::info('Using direct database query for provinces', ['count' => $provinces->count()]);
            }

            \Log::info('Provinces data for checkout', [
                'count' => $provinces->count(),
                'sample' => $provinces->take(3)->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting provinces for checkout: ' . $e->getMessage());
            // Fallback to empty collection
            $provinces = collect([]);
        }

        // Calculate total weight using product weight from database
        $totalWeight = $cartItems->sum(function ($item) {
            $weight = $item->produk->berat ?? 500; // Use product weight from database, fallback 500g
            return $item->quantity * $weight;
        });

        \Log::info('Checkout weight calculation', [
            'total_weight' => $totalWeight,
            'items' => $cartItems->map(function ($item) {
                return [
                    'produk' => $item->produk->nama_produk,
                    'quantity' => $item->quantity,
                    'berat_satuan' => $item->produk->berat ?? 500,
                    'total_berat' => $item->quantity * ($item->produk->berat ?? 500)
                ];
            })
        ]);

        // Check minimum order
        $minimumOrder = config('shop.minimum_order', 0);
        if ($subtotal < $minimumOrder) {
            return redirect()->route('frontend.cart.index')
                ->with('error', 'Minimum order Rp ' . number_format($minimumOrder, 0, ',', '.') . '. Tambah produk senilai Rp ' . number_format($minimumOrder - $subtotal, 0, ',', '.') . ' lagi.');
        }

        // Get payment channels
        $paymentChannels = \App\Models\PaymentChannel::active()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        // Calculate total with proper formatting
        $tax = config('shop.tax_rate', 0) > 0 ? ($subtotal * config('shop.tax_rate') / 100) : 0;
        $total = $subtotal + $tax;

        return view('frontend.cart.checkout', compact(
            'cartItems',
            'subtotal',
            'total',
            'tax',
            'provinces',
            'paymentChannels',
            'totalWeight'
        ));
    }

    // API Endpoint untuk mendapatkan data checkout (alternatif)
    public function getCheckoutData()
    {
        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong']);
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->produk->harga;
        });

        $totalWeight = $cartItems->sum(function ($item) {
            $weight = $item->produk->berat ?? 500;
            return $item->quantity * $weight;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => (float) $subtotal,
                'totalWeight' => (int) $totalWeight,
                'originCity' => 155,
                'originName' => 'Jakarta',
                'itemCount' => $cartItems->sum('quantity')
            ]
        ]);
    }

    // API untuk mendapatkan kota berdasarkan provinsi
    public function getCitiesByProvince($provinceId)
    {
        try {
            $cities = $this->rajaOngkirService->getCachedCities($provinceId);

            // If cached cities is empty, try direct database query
            if ($cities->isEmpty()) {
                $cities = \App\Models\ShippingArea::where('province_id', $provinceId)
                    ->orderBy('city_name')
                    ->get();
            }

            $citiesData = $cities->map(function ($city) {
                return [
                    'rajaongkir_id' => $city->rajaongkir_id,
                    'city_name' => $city->city_name,
                    'province_name' => $city->province_name,
                    'full_name' => $city->city_name . ', ' . $city->province_name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $citiesData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting cities for province: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kota: ' . $e->getMessage()
            ], 500);
        }
    }

    // API untuk menghitung ongkos kirim - OPTIMIZED VERSION
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string'
        ]);

        try {
            // Quick timeout check - return fallback immediately if needed
            $startTime = microtime(true);

            // Use same method as ShippingController for consistency and speed
            $result = $this->rajaOngkirService->calculateShippingCost(
                $request->origin,
                $request->destination,
                $request->weight,
                $request->courier
            );

            $executionTime = microtime(true) - $startTime;

            // If API is too slow, use fallback for better UX
            if ($executionTime > 5.0) {
                return $this->getFallbackShippingRates($request->courier, $request->weight);
            }

            if ($result['success']) {
                return response()->json($result);
            } else {
                // Immediate fallback on API failure
                return $this->getFallbackShippingRates($request->courier, $request->weight);
            }
        } catch (\Exception $e) {
            \Log::error('Shipping calculation error: ' . $e->getMessage());

            // Return fallback data immediately on exception
            return $this->getFallbackShippingRates($request->courier, $request->weight);
        }
    }

    private function getFallbackShippingRates($courier, $weight)
    {
        // Use same structure as ShippingController for consistency
        $baseCost = 10000;
        $weightCost = ($weight / 1000) * 5000;
        $distanceCost = 100 * 100; // dummy distance calculation

        $services = [];

        // Define services with consistent structure
        switch (strtolower($courier)) {
            case 'jne':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [['value' => intval($baseCost + $weightCost + $distanceCost), 'etd' => '2-3', 'note' => 'Estimasi - API tidak tersedia']]
                    ],
                    [
                        'service' => 'OKE',
                        'description' => 'Ongkos Kirim Ekonomis',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 0.8), 'etd' => '3-4', 'note' => 'Estimasi - API tidak tersedia']]
                    ],
                    [
                        'service' => 'YES',
                        'description' => 'Yakin Esok Sampai',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 1.5), 'etd' => '1-1', 'note' => 'Estimasi - API tidak tersedia']]
                    ]
                ];
                break;

            case 'pos':
                $services = [
                    [
                        'service' => 'Paket Kilat Khusus',
                        'description' => 'Paket Kilat Khusus',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 0.9)), 'etd' => '2-4', 'note' => 'Estimasi - API tidak tersedia']]
                    ],
                    [
                        'service' => 'Express Next Day',
                        'description' => 'Express Next Day',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 1.3), 'etd' => '1-1', 'note' => 'Estimasi - API tidak tersedia']]
                    ]
                ];
                break;

            case 'tiki':
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => [['value' => intval($baseCost + $weightCost + ($distanceCost * 1.1)), 'etd' => '3-5', 'note' => 'Estimasi - API tidak tersedia']]
                    ],
                    [
                        'service' => 'ECO',
                        'description' => 'Economy Service',
                        'cost' => [['value' => intval(($baseCost + $weightCost + $distanceCost) * 0.7), 'etd' => '4-6', 'note' => 'Estimasi - API tidak tersedia']]
                    ]
                ];
                break;

            default:
                $services = [
                    [
                        'service' => 'REG',
                        'description' => strtoupper($courier) . ' Regular Service',
                        'cost' => [['value' => intval($baseCost + $weightCost + $distanceCost), 'etd' => '2-4', 'note' => 'Estimasi - Kurir tidak dikenal']]
                    ]
                ];
                break;
        }

        $fallbackData = [
            [
                'code' => strtolower($courier),
                'name' => strtoupper($courier),
                'costs' => $services
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $fallbackData,
            'debug' => [
                'source' => 'fallback_optimized',
                'message' => 'Tarif estimasi cepat - API tidak tersedia atau timeout',
                'weight' => $weight,
                'courier' => $courier,
                'execution_time' => 'immediate',
                'note' => 'Gunakan kurir JNE/POS/TIKI untuk data akurat dari API RajaOngkir'
            ]
        ]);
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'destination_province' => 'required|integer',
            'destination_city' => 'required|integer',
            'alamat_lengkap' => 'required|string|max:500',
            'courier' => 'required|string',
            'service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|string'
        ]);

        $cartItems = Cart::where('id_user', Auth::id())->with('produk')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Keranjang kosong');
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                return $item->quantity * $item->produk->harga;
            });

            $shippingCost = (float) $request->shipping_cost;

            // No free shipping check anymore
            $tax = 0; // No tax calculation anymore
            $totalHarga = $subtotal + $shippingCost;

            // Generate unique merchant reference
            $merchantRef = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Create transaction
            $transaksi = Transaksi::create([
                'id_user' => Auth::id(),
                'merchant_ref' => $merchantRef,
                'tanggal_transaksi' => now(),
                'total_harga' => $totalHarga,
                'status' => 'pending',
                'metode_pembayaran' => $request->metode_pembayaran,
                'expired_time' => now()->addHours(24),
                'return_url' => route('frontend.home'),
                'callback_url' => null,
                'fee_customer' => 0,
                'fee_merchant' => 0,
                'nama_penerima' => auth()->user()->name,
                'telepon_penerima' => auth()->user()->phone ?? '08123456789',
                'alamat_pengiriman' => $request->alamat_lengkap,
                'catatan' => null
            ]);

            // Create transaction details
            foreach ($cartItems as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->quantity,
                    'harga_satuan' => $item->produk->harga,
                    'subtotal' => $item->quantity * $item->produk->harga
                ]);

                // Reduce product stock
                $item->produk->decrement('stok', $item->quantity);
            }

            // Get destination info
            $destinationArea = \App\Models\ShippingArea::where('rajaongkir_id', $request->destination_city)->first();

            // Calculate total weight
            $totalWeight = $cartItems->sum(function ($item) {
                return $item->quantity * ($item->produk->berat ?? 500);
            });

            // Create shipping record
            Pengiriman::create([
                'id_transaksi' => $transaksi->id,
                'origin_city_id' => config('shop.warehouse_city_id', 209), // Kudus
                'destination_province_id' => $request->destination_province,
                'destination_city_id' => $request->destination_city,
                'weight' => $totalWeight,
                'kurir' => strtoupper($request->courier),
                'layanan' => $request->service,
                'service_code' => $request->service,
                'biaya' => $shippingCost,
                'status' => 'menunggu_pembayaran',
                'courier_info' => [
                    'courier_name' => strtoupper($request->courier),
                    'service_name' => $request->service,
                    'destination' => $destinationArea ? $destinationArea->city_name : 'Unknown',
                    'weight' => $totalWeight,
                    'alamat_lengkap' => $request->alamat_lengkap
                ]
            ]);

            // Create payment record
            Pembayaran::create([
                'id_transaksi' => $transaksi->id,
                'reference' => $merchantRef,
                'metode' => $request->metode_pembayaran,
                'total_bayar' => $totalHarga,
                'status' => 'pending',
                'expired_time' => $transaksi->expired_time,
                'payment_type' => 'manual'
            ]);

            // Clear cart
            Cart::where('id_user', Auth::id())->delete();

            DB::commit();

            // Redirect to payment confirmation page
            return redirect()->route('frontend.checkout.confirmation', $transaksi->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    private function handlePaymentProcessing($transaksi, $request, $paymentChannel)
    {
        // Only process Tripay payment channels (no manual payments)
        if ($paymentChannel) {
            try {
                $tripayTransaction = $this->createTripayTransaction($transaksi, $paymentChannel);

                if ($tripayTransaction && isset($tripayTransaction['data'])) {
                    // Create separate pembayaran record for Tripay transaction
                    Pembayaran::create([
                        'id_transaksi' => $transaksi->id,
                        'reference' => $tripayTransaction['data']['reference'],
                        'metode' => $request->metode_pembayaran,
                        'total_bayar' => $transaksi->total_harga,
                        'status' => 'pending',
                        'payment_code' => $tripayTransaction['data']['pay_code'] ?? null,
                        'payment_url' => $tripayTransaction['data']['pay_url'] ?? null,
                        'checkout_url' => $tripayTransaction['data']['checkout_url'] ?? null,
                        'expired_time' => $transaksi->expired_time,
                        'payment_instructions' => $tripayTransaction['data']['instructions'] ?? null,
                        'payment_type' => $tripayTransaction['data']['is_closed_payment'] ? 'direct' : 'redirect',
                    ]);

                    \Log::info('Tripay transaction created', [
                        'merchant_ref' => $transaksi->merchant_ref,
                        'tripay_reference' => $tripayTransaction['data']['reference']
                    ]);
                } else {
                    \Log::error('Failed to create Tripay transaction', [
                        'merchant_ref' => $transaksi->merchant_ref,
                        'payment_method' => $request->metode_pembayaran
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Tripay transaction creation failed: ' . $e->getMessage());
                // Don't throw exception, just log it
            }
        } else {
            \Log::warning('Payment channel not found', [
                'payment_method' => $request->metode_pembayaran
            ]);
        }
    }

    private function createTripayTransaction($transaksi, $paymentChannel)
    {
        $user = Auth::user();

        $customerDetails = [
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone ?? '08123456789'
        ];

        $orderItems = $transaksi->detailTransaksi->map(function ($detail) {
            return [
                'sku' => 'PRD-' . $detail->id_produk,
                'name' => $detail->produk->nama_produk ?? 'Product',
                'price' => (int) $detail->harga_satuan,
                'quantity' => $detail->jumlah,
                'product_url' => url('/'),
                'image_url' => $detail->produk->gambar ? asset('storage/' . $detail->produk->gambar) : null
            ];
        })->toArray();

        $requestData = [
            'method' => $paymentChannel->code,
            'merchant_ref' => $transaksi->merchant_ref,
            'amount' => (int) $transaksi->total_harga,
            'customer_name' => $customerDetails['name'],
            'customer_email' => $customerDetails['email'],
            'customer_phone' => $customerDetails['phone'],
            'order_items' => $orderItems,
            'return_url' => route('frontend.cart.index'),
            'expired_time' => (int) $transaksi->expired_time->timestamp,
            'signature' => ''
        ];

        return $this->tripayService->createTransaction($requestData);
    }

    public function confirmation($transaksiId)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id', $transaksiId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        // LOG DETAIL UNTUK DEBUGGING
        \Log::info('=== CONFIRMATION PAGE DATA ===', [
            'transaksi_id' => $transaksi->id,
            'merchant_ref' => $transaksi->merchant_ref,
            'user_id' => $transaksi->id_user,
            'total_harga' => $transaksi->total_harga,
            'status' => $transaksi->status,
            'metode_pembayaran' => $transaksi->metode_pembayaran,
            'expired_time' => $transaksi->expired_time,
            'detail_produk' => $transaksi->detailTransaksi->map(function($detail) {
                return [
                    'produk' => $detail->produk->nama_produk ?? 'Unknown',
                    'jumlah' => $detail->jumlah,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $detail->subtotal
                ];
            }),
            'pengiriman' => [
                'kurir' => $transaksi->pengiriman->kurir ?? 'N/A',
                'layanan' => $transaksi->pengiriman->layanan ?? 'N/A',
                'biaya' => $transaksi->pengiriman->biaya ?? 0,
                'status' => $transaksi->pengiriman->status ?? 'N/A',
                'weight' => $transaksi->pengiriman->weight ?? 0
            ],
            'pembayaran' => [
                'reference' => $transaksi->pembayaran->reference ?? 'N/A',
                'metode' => $transaksi->pembayaran->metode ?? 'N/A',
                'status' => $transaksi->pembayaran->status ?? 'N/A',
                'payment_type' => $transaksi->pembayaran->payment_type ?? 'N/A',
                'payment_code' => $transaksi->pembayaran->payment_code ?? 'N/A',
                'payment_url' => $transaksi->pembayaran->payment_url ?? 'N/A'
            ]
        ]);

        $whatsappNumber = config('shop.whatsapp');
        $whatsappMessage = "Halo, saya ingin konfirmasi pembayaran untuk pesanan {$transaksi->merchant_ref} dengan total Rp " . number_format($transaksi->total_harga, 0, ',', '.');
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappMessage);

        return view('frontend.cart.confirmation', compact('transaksi', 'whatsappUrl'));
    }

    public function history()
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id_user', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        \Log::info('User accessing transaction history', [
            'user_id' => Auth::id(),
            'total_transactions' => $transaksi->total(),
            'current_page' => $transaksi->currentPage()
        ]);

        return view('frontend.cart.history', compact('transaksi'));
    }

    public function historyDetail($transaksiId)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'pengiriman', 'pembayaran'])
            ->where('id', $transaksiId)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        $whatsappNumber = config('shop.whatsapp');
        $whatsappMessage = "Halo, saya ingin menanyakan status pesanan {$transaksi->merchant_ref}";
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($whatsappMessage);

        return view('frontend.cart.history-detail', compact('transaksi', 'whatsappUrl'));
    }

    public function clearCart()
    {
        Cart::where('id_user', Auth::id())->delete();
        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function getCartCount()
    {
        $count = Cart::where('id_user', Auth::id())->sum('quantity');
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function syncTripayChannels()
    {
        try {
            $synced = $this->tripayService->syncPaymentChannels();

            return response()->json([
                'success' => true,
                'message' => "Berhasil sinkronisasi {$synced} payment channel dari Tripay",
                'synced' => $synced
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi payment channels: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncCartToDatabase(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'cart' => 'required|array',
                'cart.*.id_produk' => 'required|exists:produk,id',
                'cart.*.quantity' => 'required|integer|min:1'
            ]);

            $cart = $request->cart;
            $userId = Auth::id();
            $syncedItems = 0;

            // Begin transaction
            DB::beginTransaction();

            foreach ($cart as $item) {
                // Check product stock
                $produk = Produk::findOrFail($item['id_produk']);
                if ($produk->stok < $item['quantity']) {
                    continue; // Skip items with insufficient stock
                }

                // Check if item already exists in cart
                $cartItem = Cart::where('id_user', $userId)
                    ->where('id_produk', $item['id_produk'])
                    ->first();

                if ($cartItem) {
                    // Update existing cart item
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + $item['quantity']
                    ]);
                } else {
                    // Create new cart item
                    Cart::create([
                        'id_user' => $userId,
                        'id_produk' => $item['id_produk'],
                        'quantity' => $item['quantity']
                    ]);
                }

                $syncedItems++;
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyinkronkan $syncedItems item ke keranjang",
                'synced_items' => $syncedItems
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            \Log::error('Cart sync error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'cart_data' => $request->cart ?? 'No cart data',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyinkronkan keranjang: ' . $e->getMessage()
            ], 500);
        }
    }
}
