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

        return view('pembeli.cart.index', compact('cartItems', 'total'));
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

        // Get payment channels
        $paymentChannels = \App\Models\PaymentChannel::active()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $total = $subtotal; // Initialize total with subtotal

        return view('pembeli.checkout.index', compact(
            'cartItems',
            'subtotal',
            'total',
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

    // API untuk menghitung ongkos kirim - OPTIMIZED VERSION with better fallback
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string'
        ]);

        $startTime = microtime(true);

        \Log::info('CartController: Shipping calculation started', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier
        ]);

        try {
            // Try RajaOngkir API first with timeout
            $result = $this->rajaOngkirService->calculateShippingCost(
                $request->origin,
                $request->destination,
                $request->weight,
                $request->courier
            );

            $executionTime = microtime(true) - $startTime;

            \Log::info('CartController: RajaOngkir result received', [
                'success' => $result['success'],
                'execution_time' => round($executionTime, 2) . 's',
                'has_fallback_suggested' => isset($result['fallback_suggested'])
            ]);

            // Use API result if successful and fast enough
            if ($result['success'] && $executionTime < 15.0) {
                \Log::info('CartController: Using RajaOngkir API result');
                return response()->json($result);
            }

            // Use fallback if API failed or too slow
            \Log::warning('CartController: Using fallback shipping rates', [
                'reason' => !$result['success'] ? 'API_FAILED' : 'TOO_SLOW',
                'execution_time' => round($executionTime, 2) . 's',
                'api_message' => $result['message'] ?? 'Unknown error'
            ]);

            return $this->getFallbackShippingRates($request->courier, $request->weight);
        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            \Log::error('CartController: Shipping calculation exception', [
                'error' => $e->getMessage(),
                'execution_time' => round($executionTime, 2) . 's',
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback data immediately on exception
            return $this->getFallbackShippingRates($request->courier, $request->weight);
        }
    }

    private function getFallbackShippingRates($courier, $weight)
    {
        \Log::info('CartController: Using fallback shipping rates', [
            'courier' => $courier,
            'weight' => $weight . 'g'
        ]);

        // More realistic pricing calculation
        $baseCost = 8000; // Base cost
        $weightCost = ceil($weight / 1000) * 3000; // Per kg
        $courierMultiplier = 1.0;

        // Courier-specific adjustments
        switch (strtolower($courier)) {
            case 'jne':
                $courierMultiplier = 1.0;
                break;
            case 'pos':
                $courierMultiplier = 0.9;
                break;
            case 'tiki':
                $courierMultiplier = 1.1;
                break;
        }

        $services = [];

        // Define services with more realistic pricing
        switch (strtolower($courier)) {
            case 'jne':
                $regCost = intval(($baseCost + $weightCost) * $courierMultiplier);
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Layanan Reguler',
                        'cost' => [[
                            'value' => $regCost,
                            'etd' => '2-3',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ],
                    [
                        'service' => 'OKE',
                        'description' => 'Ongkos Kirim Ekonomis',
                        'cost' => [[
                            'value' => intval($regCost * 0.8),
                            'etd' => '3-5',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ],
                    [
                        'service' => 'YES',
                        'description' => 'Yakin Esok Sampai',
                        'cost' => [[
                            'value' => intval($regCost * 1.5),
                            'etd' => '1-1',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ]
                ];
                break;

            case 'pos':
                $regCost = intval(($baseCost + $weightCost) * $courierMultiplier);
                $services = [
                    [
                        'service' => 'Paket Kilat Khusus',
                        'description' => 'Paket Kilat Khusus',
                        'cost' => [[
                            'value' => $regCost,
                            'etd' => '2-4',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ],
                    [
                        'service' => 'Express Next Day',
                        'description' => 'Express Next Day',
                        'cost' => [[
                            'value' => intval($regCost * 1.3),
                            'etd' => '1-1',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ]
                ];
                break;

            case 'tiki':
                $regCost = intval(($baseCost + $weightCost) * $courierMultiplier);
                $services = [
                    [
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => [[
                            'value' => $regCost,
                            'etd' => '3-5',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ],
                    [
                        'service' => 'ECO',
                        'description' => 'Economy Service',
                        'cost' => [[
                            'value' => intval($regCost * 0.8),
                            'etd' => '4-7',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ],
                    [
                        'service' => 'ONS',
                        'description' => 'Over Night Service',
                        'cost' => [[
                            'value' => intval($regCost * 1.6),
                            'etd' => '1-1',
                            'note' => 'Estimasi berdasarkan berat dan jarak'
                        ]]
                    ]
                ];
                break;

            default:
                $regCost = intval($baseCost + $weightCost);
                $services = [
                    [
                        'service' => 'REG',
                        'description' => strtoupper($courier) . ' Regular Service',
                        'cost' => [[
                            'value' => $regCost,
                            'etd' => '2-4',
                            'note' => 'Estimasi untuk kurir ' . strtoupper($courier)
                        ]]
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

        \Log::info('CartController: Fallback rates generated', [
            'courier' => $courier,
            'services_count' => count($services),
            'price_range' => [
                'min' => min(array_column(array_column($services, 'cost'), 0))['value'] ?? 0,
                'max' => max(array_column(array_column($services, 'cost'), 0))['value'] ?? 0
            ]
        ]);

        return response()->json([
            'success' => true,
            'data' => $fallbackData,
            'debug' => [
                'source' => 'fallback_enhanced',
                'message' => 'Tarif estimasi berdasarkan berat dan jarak - RajaOngkir tidak tersedia',
                'weight' => $weight . 'g',
                'courier' => strtoupper($courier),
                'base_cost' => $baseCost,
                'weight_cost' => $weightCost,
                'total_services' => count($services),
                'note' => 'Tarif dapat berbeda dengan tarif aktual kurir'
            ]
        ]);
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'telepon_penerima' => 'required|string|max:15',
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

            // Check for free shipping
            $freeShippingMin = config('shop.free_shipping_minimum', 0);
            if ($freeShippingMin > 0 && $subtotal >= $freeShippingMin) {
                $shippingCost = 0;
            }

            $tax = config('shop.tax_rate', 0) > 0 ? ($subtotal * config('shop.tax_rate') / 100) : 0;
            $totalHarga = $subtotal + $shippingCost + $tax;

            // Generate unique merchant reference
            $merchantRef = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Create transaction
            $transaksi = Transaksi::create([
                'id_user' => Auth::id(),
                'merchant_ref' => $merchantRef,
                'tanggal_transaksi' => now(),
                'total_harga' => $totalHarga,
                'status' => 'pending',
                'expired_time' => now()->addHours(24),
                'return_url' => route('frontend.home'),
                'callback_url' => null, // Will be set later if using Tripay
                'fee_customer' => 0,
                'fee_merchant' => 0,
                'nama_penerima' => $request->nama_penerima,
                'telepon_penerima' => $request->telepon_penerima,
                'alamat_pengiriman' => $request->alamat_lengkap,
                'catatan' => $request->catatan ?? null
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
                'destination_province_id' => $request->destination_province,
                'destination_city_id' => $request->destination_city,
                'weight' => $totalWeight,
                'kurir' => strtoupper($request->courier),
                'layanan' => $request->service,
                'service_code' => $request->service,
                'biaya' => $shippingCost,
                'status' => 'menunggu_pembayaran',
                'courier_info' => json_encode([
                    'courier_name' => strtoupper($request->courier),
                    'service_name' => $request->service,
                    'destination' => $destinationArea ? $destinationArea->city_name : 'Unknown',
                    'weight' => $totalWeight,
                    'alamat_lengkap' => $request->alamat_lengkap
                ])
            ]);

            // Handle payment processing (Tripay vs Manual)
            $paymentChannel = PaymentChannel::where('code', $request->metode_pembayaran)
                ->where('is_active', true)
                ->first();

            \Log::info('Processing payment for transaction', [
                'merchant_ref' => $transaksi->merchant_ref,
                'payment_method' => $request->metode_pembayaran,
                'channel_found' => $paymentChannel ? true : false,
                'channel_synced' => $paymentChannel ? $paymentChannel->is_synced : false
            ]);

            $this->handlePaymentProcessing($transaksi, $request, $paymentChannel);

            // Clear cart
            Cart::where('id_user', Auth::id())->delete();

            DB::commit();

            // Prepare user-friendly info for confirmation page
            $orderInfo = [
                'merchant_ref' => $transaksi->merchant_ref,
                'total' => number_format($transaksi->total_harga, 0, ',', '.'),
                'recipient' => $transaksi->nama_penerima,
                'address' => $transaksi->alamat_pengiriman,
                'phone' => $transaksi->telepon_penerima,
                'payment_method' => $paymentChannel ? $paymentChannel->name : $request->metode_pembayaran
            ];

            // Redirect to payment confirmation page
            return redirect()->route('frontend.checkout.confirmation', $transaksi->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran')
                ->with('order_info', $orderInfo);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    private function handlePaymentProcessing($transaksi, $request, $paymentChannel)
    {
        if ($paymentChannel && $paymentChannel->is_synced) {
            // Process Tripay payment channel
            try {
                $tripayTransaction = $this->createTripayTransaction($transaksi, $paymentChannel);

                if ($tripayTransaction && isset($tripayTransaction['success']) && $tripayTransaction['success'] && isset($tripayTransaction['data'])) {
                    // Update transaksi with Tripay reference
                    $transaksi->update([
                        'tripay_reference' => $tripayTransaction['data']['reference'],
                        'callback_url' => url('/api/tripay/callback'),
                        'return_url' => route('frontend.confirmation.show', $transaksi->id)
                    ]);

                    // Create Tripay pembayaran record
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
                        'payment_instructions' => isset($tripayTransaction['data']['instructions']) ? json_encode($tripayTransaction['data']['instructions']) : null,
                        'payment_type' => 'tripay',
                        'qr_string' => $tripayTransaction['data']['qr_string'] ?? null,
                        'qr_url' => $tripayTransaction['data']['qr_url'] ?? null
                    ]);
                } else {
                    // Tripay failed, create fallback payment
                    $errorMessage = $tripayTransaction['message'] ?? 'Unknown error';
                    throw new \Exception('Tripay transaction failed: ' . $errorMessage);
                }
            } catch (\Exception $e) {
                \Log::error('Tripay transaction creation failed', [
                    'merchant_ref' => $transaksi->merchant_ref,
                    'error' => $e->getMessage()
                ]);

                // Fallback to manual payment
                $this->createFallbackPayment($transaksi, $request, $paymentChannel);
            }
        } else {
            // Create manual payment for non-Tripay channels
            $this->createManualPayment($transaksi, $request);
        }
    }

    private function createManualPayment($transaksi, $request)
    {
        Pembayaran::create([
            'id_transaksi' => $transaksi->id,
            'reference' => $transaksi->merchant_ref,
            'metode' => $request->metode_pembayaran,
            'total_bayar' => $transaksi->total_harga,
            'status' => 'pending',
            'expired_time' => $transaksi->expired_time,
            'payment_type' => 'manual'
        ]);

        \Log::info('Manual payment created', [
            'merchant_ref' => $transaksi->merchant_ref,
            'payment_method' => $request->metode_pembayaran,
            'payment_type' => 'manual'
        ]);
    }

    private function createFallbackPayment($transaksi, $request, $paymentChannel)
    {
        // Create payment record that uses manual type but keeps the synced channel info
        // This allows the confirmation page to still show Tripay instructions
        Pembayaran::create([
            'id_transaksi' => $transaksi->id,
            'reference' => $transaksi->merchant_ref,
            'metode' => $request->metode_pembayaran,
            'total_bayar' => $transaksi->total_harga,
            'status' => 'pending',
            'expired_time' => $transaksi->expired_time,
            'payment_type' => 'manual', // Manual processing but still use channel instructions
            'payment_instructions' => 'Tripay gagal, gunakan instruksi manual dari channel'
        ]);

        \Log::info('Fallback payment created', [
            'merchant_ref' => $transaksi->merchant_ref,
            'payment_method' => $request->metode_pembayaran,
            'payment_type' => 'manual',
            'channel_synced' => $paymentChannel->is_synced,
            'reason' => 'Tripay transaction failed but channel is synced'
        ]);
    }

    private function createTripayTransaction($transaksi, $paymentChannel)
    {
        $user = Auth::user();

        // Validate and fix email format
        $customerEmail = $user->email;
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $domain = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
            $customerEmail = 'customer' . $user->id . '@' . $domain;
        }

        // Validate and format phone number
        $customerPhone = $user->nohp ?? '08123456789';
        $customerPhone = preg_replace('/[^0-9+]/', '', $customerPhone);
        if (!preg_match('/^(08\d{8,11}|\+628\d{8,11})$/', $customerPhone)) {
            $customerPhone = '08123456789';
        }

        // Calculate subtotals
        $subtotalProduk = $transaksi->detailTransaksi->sum('subtotal');
        $biayaKirim = $transaksi->pengiriman ? $transaksi->pengiriman->biaya : 0;
        $totalAmount = (int) $transaksi->total_harga;
        
        // PENTING: Pastikan order_items total = amount
        // Jika ada biaya kirim, tambahkan sebagai item terpisah
        $orderItems = [];
        
        // Add product items
        foreach ($transaksi->detailTransaksi as $detail) {
            $price = (int) $detail->harga_satuan;
            $quantity = (int) $detail->jumlah;
            
            if ($price <= 0 || $quantity <= 0) {
                throw new \Exception("Invalid price or quantity for product: " . ($detail->produk->nama_produk ?? 'Unknown'));
            }
            
            $orderItems[] = [
                'sku' => 'PRD-' . $detail->id_produk,
                'name' => $detail->produk->nama_produk ?? 'Product',
                'price' => $price,
                'quantity' => $quantity,
                'product_url' => config('app.url', 'http://127.0.0.1:8000'),
                'image_url' => $detail->produk->gambar ? asset('storage/' . $detail->produk->gambar) : ''
            ];
        }
        
        // Add shipping cost as separate item if exists
        if ($biayaKirim > 0) {
            $orderItems[] = [
                'sku' => 'SHIPPING-' . $transaksi->id,
                'name' => 'Biaya Pengiriman (' . ($transaksi->pengiriman->kurir ?? 'Unknown') . ')',
                'price' => (int) $biayaKirim,
                'quantity' => 1,
                'product_url' => config('app.url', 'http://127.0.0.1:8000'),
                'image_url' => ''
            ];
        }
        
        // Calculate additional fees/tax if any
        $totalOrderItems = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $orderItems));
        
        // If there's a difference, add as additional fee
        $difference = $totalAmount - $totalOrderItems;
        if ($difference > 0) {
            $orderItems[] = [
                'sku' => 'FEE-' . $transaksi->id,
                'name' => 'Biaya Admin & Layanan',
                'price' => (int) $difference,
                'quantity' => 1,
                'product_url' => config('app.url', 'http://127.0.0.1:8000'),
                'image_url' => ''
            ];
        } elseif ($difference < 0) {
            // Adjust last item if over
            $lastIndex = count($orderItems) - 1;
            $orderItems[$lastIndex]['price'] += $difference;
        }
        
        // Validate total amount matches
        $finalTotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $orderItems));
        
        if ($finalTotal !== $totalAmount) {
            throw new \Exception("Amount mismatch: order_items total (" . $finalTotal . ") != transaction amount (" . $totalAmount . ")");
        }

        // Validate expired time
        $expiredTime = (int) $transaksi->expired_time->timestamp;
        if ($expiredTime <= time()) {
            throw new \Exception("Invalid expired time: expired time must be in the future");
        }

        // Prepare request data
        $requestData = [
            'method' => $paymentChannel->code,
            'merchant_ref' => $transaksi->merchant_ref,
            'amount' => $totalAmount,
            'customer_name' => $user->nama ?? 'Customer',
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => $orderItems,
            'return_url' => route('frontend.confirmation.show', $transaksi->id),
            'expired_time' => $expiredTime,
        ];

        return $this->tripayService->createTransaction($requestData);
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
}
