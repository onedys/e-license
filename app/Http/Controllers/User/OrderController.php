<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()
            ->orders()
            ->with('product')
            ->latest()
            ->paginate(20);
        
        $totalSpent = Auth::user()
            ->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $totalLicenses = Auth::user()
            ->orders()
            ->where('payment_status', 'paid')
            ->sum('quantity');
        
        return view('user.orders.index', compact('orders', 'totalSpent', 'totalLicenses'));
    }

    public function show($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
                     ->where('user_id', Auth::id())
                     ->with(['product', 'licenses'])
                     ->firstOrFail();
        
        return view('user.orders.show', compact('order'));
    }
}