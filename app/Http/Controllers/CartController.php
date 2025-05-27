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

        // Get provinces for shipping
        $provinces = $this->rajaOngkirService->getCachedProvinces();

        // Get only active payment channels
        $paymentChannels = PaymentChannel::active()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        // Get active couriers
        $couriers = Courier::active()
            ->orderBy('name', 'asc')
            ->get();

        // Calculate total weight
        $totalWeight = $cartItems->sum(function ($item) {
            $weight = $item->produk->weight ?? 500;
            return $item->quantity * $weight;
        });

        return view('frontend.cart.checkout', compact(
            'cartItems',
            'subtotal',
            'provinces',
            'paymentChannels',
            'couriers',
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
            $weight = $item->produk->weight ?? 500;
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
            
            return response()->json([
                'success' => true,
                'data' => $cities->map(function($city) {
                    return [
                        'rajaongkir_id' => $city->rajaongkir_id,
                        'city_name' => $city->city_name,
                        'province_name' => $city->province_name,
                        'full_name' => $city->city_name . ', ' . $city->province_name
                    ];
                })
            ]);
        } catch (\Exception $e) {
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
            \Log::info('Shipping calculation time: ' . $executionTime . ' seconds');

            // If API is too slow, use fallback for better UX
            if ($executionTime > 5.0) {
                \Log::warning('API response too slow, using fallback');
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
        \Log::info('Using optimized fallback shipping rates', ['courier' => $courier, 'weight' => $weight]);
        
        // Optimized fallback rates dengan tarif yang lebih realistis
        $fallbackRates = [
            'jne' => [
                'REG' => ['cost' => 15000, 'etd' => '2-3', 'description' => 'Layanan Reguler'],
                'OKE' => ['cost' => 12000, 'etd' => '3-4', 'description' => 'Ongkos Kirim Ekonomis'],
                'YES' => ['cost' => 25000, 'etd' => '1-1', 'description' => 'Yakin Esok Sampai']
            ],
            'pos' => [
                'Kilat Khusus' => ['cost' => 18000, 'etd' => '2-4', 'description' => 'Paket Kilat Khusus'],
                'Express' => ['cost' => 30000, 'etd' => '1-1', 'description' => 'Express Next Day']
            ],
            'tiki' => [
                'REG' => ['cost' => 16000, 'etd' => '3-4', 'description' => 'Regular Service'],
                'ECO' => ['cost' => 13000, 'etd' => '4-5', 'description' => 'Economy Service'],
                'ONS' => ['cost' => 28000, 'etd' => '1-1', 'description' => 'Over Night Service']
            ]
        ];

        $courierName = strtoupper($courier);
        $services = $fallbackRates[$courier] ?? [
            'REG' => ['cost' => 15000, 'etd' => '2-3', 'description' => 'Regular Service']
        ];

        // Quick weight-based cost adjustment
        $weightMultiplier = max(1, ceil($weight / 1000)); // Per kg
        
        $costs = [];
        foreach ($services as $serviceCode => $serviceData) {
            $adjustedCost = $serviceData['cost'] * $weightMultiplier;

            $costs[] = [
                'service' => $serviceCode,
                'description' => $serviceData['description'],
                'cost' => [
                    [
                        'value' => $adjustedCost,
                        'etd' => $serviceData['etd'],
                        'note' => ''
                    ]
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                [
                    'code' => $courier,
                    'name' => $courierName,
                    'costs' => $costs
                ]
            ],
            'debug' => [
                'source' => 'fallback_optimized',
                'message' => 'Tarif estimasi cepat - API tidak tersedia atau timeout',
                'weight_multiplier' => $weightMultiplier,
                'execution_time' => 'immediate'
            ]
        ]);
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string',
            'destination_city' => 'required|integer',
            'courier' => 'required|string',
            'service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'alamat_lengkap' => 'required|string|max:500'
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

            $shippingCost = $request->shipping_cost;
            
            // Calculate payment fee if using Tripay channel
            $paymentFee = 0;
            $paymentChannel = PaymentChannel::where('code', $request->metode_pembayaran)->first();
            if ($paymentChannel) {
                $paymentFee = $paymentChannel->calculateFee($subtotal);
            }
            
            $totalHarga = $subtotal + $shippingCost + $paymentFee;

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
                'return_url' => route('frontend.cart.index'),
                'callback_url' => route('tripay.callback') ?? null,
                'fee_customer' => $paymentFee,
                'fee_merchant' => 0,
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

            // Get shipping area info
            $destinationArea = $this->rajaOngkirService->getCachedCities(
                $request->destination_province ?? 0
            )->where('rajaongkir_id', $request->destination_city)->first();

            // Calculate total weight
            $totalWeight = $cartItems->sum(function ($item) {
                $weight = $item->produk->weight ?? 500;
                return $item->quantity * $weight;
            });

            // Create shipping record
            Pengiriman::create([
                'id_transaksi' => $transaksi->id,
                'destination_province_id' => $destinationArea->province_id ?? null,
                'destination_city_id' => $request->destination_city,
                'weight' => $totalWeight,
                'kurir' => strtoupper($request->courier),
                'layanan' => $request->service,
                'service_code' => $request->service,
                'biaya' => $shippingCost,
                'status' => 'diproses',
                'courier_info' => json_encode([
                    'courier_name' => strtoupper($request->courier),
                    'service_name' => $request->service,
                    'destination' => $destinationArea->full_name ?? 'Unknown',
                    'weight' => $totalWeight,
                    'alamat_lengkap' => $request->alamat_lengkap
                ])
            ]);

            // Handle payment processing
            $this->handlePaymentProcessing($transaksi, $request, $paymentChannel);

            // Clear cart
            Cart::where('id_user', Auth::id())->delete();

            DB::commit();

            return redirect()->route('frontend.cart.index')
                ->with('success', 'Pesanan berhasil dibuat! ID Transaksi: ' . $merchantRef);

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
        
        $orderItems = $transaksi->detailTransaksi->map(function($detail) {
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