<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_licenses' => $user->licenses()->count(),
            'active_licenses' => $user->licenses()->active()->count(),
            'pending_licenses' => $user->licenses()->pending()->count(),
            'unread_notifications' => $user->notifications()->whereNull('read_at')->count(),
        ];
        
        $recentOrders = $user->orders()
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();
        
        $recentLicenses = $user->licenses()
            ->with('order.product')
            ->latest()
            ->limit(10)
            ->get();
        
        return view('user.dashboard.index', compact('user', 'stats', 'recentOrders', 'recentLicenses'));
    }
}