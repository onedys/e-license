<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\Payment\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $tripayService;

    public function __construct(TripayService $tripayService)
    {
        $this->tripayService = $tripayService;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }
        
        $items = [];
        $total = 0;
        
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            
            if (!$product || !$product->is_in_stock) {
                return redirect()->route('cart.index')
                    ->with('error', 'Produk ' . $item['name'] . ' tidak tersedia.');
            }
            
            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'subtotal' => $product->price * $item['quantity'],
            ];
            
            $total += $product->price * $item['quantity'];
        }
        
        $paymentChannels = $this->tripayService->getPaymentChannels();
        
        return view('checkout.index', compact('items', 'total', 'paymentChannels'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);
        
        $user = Auth::user();
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }
        
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            
            if (!$product || !$product->is_in_stock) {
                return redirect()->route('cart.index')
                    ->with('error', 'Produk ' . $item['name'] . ' tidak tersedia.');
            }
            
            $orderNumber = Order::generateOrderNumber();
            $merchantRef = 'INV-' . time() . '-' . rand(1000, 9999);
            
            $warrantyUntil = now()->addDays(7);
            
            $order = Order::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_number' => $orderNumber,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_amount' => $product->price * $item['quantity'],
                'payment_status' => 'pending',
                'tripay_merchant_ref' => $merchantRef,
                'warranty_until' => $warrantyUntil,
            ]);
            
            try {
                $tripayData = [
                    'method' => $request->payment_method,
                    'merchant_ref' => $merchantRef,
                    'amount' => $order->total_amount,
                    'customer_name' => $user->name,
                    'customer_email' => $user->email ?? 'customer@e-license.com',
                    'customer_phone' => $user->phone ?? '081234567890',
                    'order_items' => [
                        [
                            'name' => $product->name,
                            'price' => $product->price,
                            'quantity' => $item['quantity'],
                        ]
                    ],
                    'return_url' => route('checkout.success', ['order' => $orderNumber]),
                    'expired_time' => time() + (24 * 60 * 60),
                ];
                
                $transaction = $this->tripayService->createTransaction($tripayData);
                
                $order->update([
                    'tripay_reference' => $transaction['reference'],
                    'payment_method' => $request->payment_method,
                ]);
                
                session()->put('pending_order_' . $orderNumber, [
                    'reference' => $transaction['reference'],
                    'checkout_url' => $transaction['checkout_url'],
                ]);
                
            } catch (\Exception $e) {
                $order->delete();
                
                return redirect()->route('checkout.index')
                    ->with('error', 'Pembayaran gagal: ' . $e->getMessage());
            }
        }
        
        session()->forget('cart');
        
        $firstOrder = Order::where('tripay_merchant_ref', $merchantRef)->first();
        
        return redirect()->route('payment.redirect', ['order' => $firstOrder->order_number]);
    }

    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
                     ->where('user_id', Auth::id())
                     ->firstOrFail();
        
        return view('checkout.success', compact('order'));
    }
}