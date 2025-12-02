<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ActivationController;
use App\Http\Controllers\User\WarrantyController;
use App\Http\Controllers\PaymentController;

Route::middleware(['api'])->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'service' => 'e-License API',
            'version' => '1.0.0',
        ]);
    });
    
    Route::get('/products', function () {
        $products = \App\Models\Product::active()
            ->select('id', 'name', 'slug', 'price', 'category', 'description')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ]);
    });
    
    Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
        ->post('/payment/tripay-callback', [PaymentController::class, 'callback']);
});

Route::middleware(['web', 'auth', 'api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $request->user()->id,
                'username' => $request->user()->username,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'is_admin' => $request->user()->is_admin,
                'created_at' => $request->user()->created_at,
            ],
        ]);
    });
    
    Route::get('/licenses', function (Request $request) {
        $licenses = $request->user()->licenses()
            ->with(['order.product'])
            ->latest()
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $licenses,
            'meta' => [
                'total' => $licenses->total(),
                'per_page' => $licenses->perPage(),
                'current_page' => $licenses->currentPage(),
                'last_page' => $licenses->lastPage(),
            ],
        ]);
    });
    
    Route::get('/orders', function (Request $request) {
        $orders = $request->user()->orders()
            ->with(['product'])
            ->latest()
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    });
    
    Route::prefix('activation')->group(function () {
        Route::post('/check-installation-id', [ActivationController::class, 'checkInstallationId']);
        
        Route::get('/pending-licenses', function (Request $request) {
            $licenses = $request->user()->licenses()
                ->pending()
                ->with('order.product')
                ->get()
                ->map(function ($license) {
                    return [
                        'id' => $license->id,
                        'license_key' => $license->license_key_formatted,
                        'order_number' => $license->order->order_number,
                        'product_name' => $license->order->product->name,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $licenses,
                'count' => $licenses->count(),
            ]);
        });
        
        Route::post('/activate', [ActivationController::class, 'activate']);
    });
    
    Route::prefix('warranty')->group(function () {
        Route::get('/eligible-licenses', function (Request $request) {
            $licenses = $request->user()->licenses()
                ->blocked()
                ->where('warranty_until', '>=', now())
                ->whereNull('replaced_by')
                ->with('order.product')
                ->get()
                ->filter(function ($license) {
                    $hasSuccessfulActivation = $license->activationLogs()
                        ->where('status', 'success')
                        ->exists();
                    
                    return !$hasSuccessfulActivation;
                })
                ->map(function ($license) {
                    return [
                        'license_key' => $license->license_key_formatted,
                        'order_number' => $license->order->order_number,
                        'product_name' => $license->order->product->name,
                        'warranty_until' => $license->warranty_until->format('Y-m-d H:i:s'),
                        'warranty_remaining' => $license->warranty_until->diffForHumans(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $licenses,
                'count' => $licenses->count(),
            ]);
        });
        
        Route::post('/check-eligibility', function (Request $request) {
            $request->validate([
                'license_key' => 'required|string',
            ]);
            
            $license = $request->user()->licenses()
                ->where('license_key', encrypt($request->license_key))
                ->first();
            
            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lisensi tidak ditemukan.',
                ], 404);
            }
            
            $eligibility = app(\App\Services\License\WarrantyProcessor::class)
                ->checkWarrantyEligibility($license);
            
            return response()->json([
                'success' => true,
                'eligible' => $eligibility['eligible'],
                'message' => $eligibility['message'],
                'license' => [
                    'key' => $license->license_key_formatted,
                    'status' => $license->status,
                    'warranty_until' => $license->warranty_until,
                    'is_warranty_valid' => $license->is_warranty_valid,
                    'replaced_by' => $license->replaced_by,
                    'activation_attempts' => $license->activation_attempts,
                ],
            ]);
        });
        
        Route::post('/claim', [WarrantyController::class, 'claim']);
        
        Route::get('/history', function (Request $request) {
            $claims = \App\Models\WarrantyExchange::whereHas('userLicense', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->with(['userLicense', 'replacementLicense'])
            ->latest()
            ->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $claims,
            ]);
        });
    });
    
    Route::prefix('cart')->group(function () {
        Route::get('/', function (Request $request) {
            $cart = session()->get('cart', []);
            
            return response()->json([
                'success' => true,
                'data' => $cart,
                'count' => count($cart),
                'total' => array_sum(array_map(function ($item) {
                    return $item['price'] * $item['quantity'];
                }, $cart)),
            ]);
        });
        
        Route::post('/add/{productId}', function (Request $request, $productId) {
            $product = \App\Models\Product::active()->findOrFail($productId);
            
            $cart = session()->get('cart', []);
            
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $request->quantity ?? 1;
            } else {
                $cart[$productId] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $request->quantity ?? 1,
                    'image' => $product->image,
                ];
            }
            
            session()->put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
                'cart_count' => count($cart),
            ]);
        });
        
        Route::put('/update/{productId}', function (Request $request, $productId) {
            $cart = session()->get('cart', []);
            
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $request->quantity;
                session()->put('cart', $cart);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Keranjang berhasil diperbarui.',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di keranjang.',
            ], 404);
        });
        
        Route::delete('/remove/{productId}', function ($productId) {
            $cart = session()->get('cart', []);
            
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                session()->put('cart', $cart);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil dihapus dari keranjang.',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di keranjang.',
            ], 404);
        });
        
        Route::delete('/clear', function () {
            session()->forget('cart');
            
            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil dikosongkan.',
            ]);
        });
    });
    
    Route::prefix('checkout')->group(function () {
        Route::get('/', function (Request $request) {
            $cart = session()->get('cart', []);
            
            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang belanja kosong.',
                ], 400);
            }
            
            $items = [];
            $total = 0;
            
            foreach ($cart as $item) {
                $product = \App\Models\Product::find($item['id']);
                
                if (!$product || !$product->is_in_stock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk ' . $item['name'] . ' tidak tersedia.',
                    ], 400);
                }
                
                $items[] = [
                    'product' => $product->only(['id', 'name', 'price']),
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                ];
                
                $total += $product->price * $item['quantity'];
            }
            
            $tripayService = app(\App\Services\Payment\TripayService::class);
            $paymentChannels = $tripayService->getPaymentChannels();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'total' => $total,
                    'payment_channels' => $paymentChannels,
                    'user' => $request->user()->only(['name', 'email', 'phone']),
                ],
            ]);
        });
        
        Route::post('/process', [\App\Http\Controllers\CheckoutController::class, 'process']);
        
        Route::get('/payment/status/{orderNumber}', function (Request $request, $orderNumber) {
            $order = $request->user()->orders()
                ->where('order_number', $orderNumber)
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => $order->payment_status,
                    'paid_at' => $order->paid_at,
                    'total_amount' => $order->total_amount,
                    'product' => $order->product->name,
                ],
            ]);
        });
    });
});

Route::middleware(['web', 'auth', 'admin', 'api'])->prefix('admin')->group(function () {
    Route::get('/stats', function () {
        $today = now()->today();
        
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_licenses' => \App\Models\UserLicense::count(),
            'total_license_pool' => \App\Models\LicensePool::count(),
            
            'today_orders' => \App\Models\Order::whereDate('created_at', $today)->count(),
            'today_sales' => \App\Models\Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'today_activations' => \App\Models\UserLicense::whereDate('activated_at', $today)->count(),
            'today_warranty_claims' => \App\Models\WarrantyExchange::whereDate('created_at', $today)->count(),
            
            'pending_orders' => \App\Models\Order::where('payment_status', 'pending')->count(),
            'active_licenses' => \App\Models\UserLicense::active()->count(),
            'blocked_licenses' => \App\Models\UserLicense::blocked()->count(),
            'available_pool' => \App\Models\LicensePool::active()->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    });
    
    Route::prefix('license-pool')->group(function () {
        Route::get('/stats', function () {
            $stats = [
                'total' => \App\Models\LicensePool::count(),
                'active' => \App\Models\LicensePool::where('status', 'active')->count(),
                'blocked' => \App\Models\LicensePool::where('status', 'blocked')->count(),
                'invalid' => \App\Models\LicensePool::where('status', 'invalid')->count(),
                'exhausted' => \App\Models\LicensePool::where('status', 'exhausted')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        });
        
        Route::post('/bulk-upload', [\App\Http\Controllers\Admin\LicensePoolController::class, 'store']);
        
        Route::get('/export', [\App\Http\Controllers\Admin\LicensePoolController::class, 'export']);
        
        Route::post('/{id}/revalidate', [\App\Http\Controllers\Admin\LicensePoolController::class, 'revalidate']);
    });
    
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index']);
        
        Route::post('/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword']);
    });
    
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OrderController::class, 'index']);
        
        Route::put('/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus']);
    });
    
    Route::prefix('warranty')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WarrantyController::class, 'index']);
        
        Route::get('/pending', [\App\Http\Controllers\Admin\WarrantyController::class, 'pending']);
        
        Route::put('/{claim}/approve', [\App\Http\Controllers\Admin\WarrantyController::class, 'approve']);
        
        Route::put('/{claim}/reject', [\App\Http\Controllers\Admin\WarrantyController::class, 'reject']);
    });
});