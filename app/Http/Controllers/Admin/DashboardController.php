<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\LicensePool;
use App\Models\UserLicense;
use App\Models\WarrantyExchange;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_licenses' => UserLicense::count(),
            'total_license_pool' => LicensePool::count(),
            
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'today_sales' => Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'today_activations' => UserLicense::whereDate('activated_at', $today)->count(),
            'today_warranty_claims' => WarrantyExchange::whereDate('created_at', $today)->count(),
            
            'pending_orders' => Order::where('payment_status', 'pending')->count(),
            'active_licenses' => UserLicense::active()->count(),
            'blocked_licenses' => UserLicense::blocked()->count(),
            'available_pool' => LicensePool::active()->count(),
        ];

        $recentOrders = Order::with(['user', 'product'])
            ->latest()
            ->limit(10)
            ->get();

        $recentActivations = UserLicense::with(['user', 'order.product'])
            ->whereNotNull('activated_at')
            ->latest('activated_at')
            ->limit(10)
            ->get();

        $recentWarrantyClaims = WarrantyExchange::with(['userLicense.user', 'replacementLicense'])
            ->latest()
            ->limit(10)
            ->get();

        $salesData = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as total_sales')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $lowStockProducts = LicensePool::select('product_id', DB::raw('COUNT(*) as stock'))
            ->where('status', 'active')
            ->groupBy('product_id')
            ->having('stock', '<', 10)
            ->with('product')
            ->get();

        $pendingManualWarranty = WarrantyExchange::where('auto_approved', false)
            ->whereNull('approved_at')
            ->count();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentOrders',
            'recentActivations',
            'recentWarrantyClaims',
            'salesData',
            'lowStockProducts',
            'pendingManualWarranty'
        ));
    }

    public function systemInfo()
    {
        $info = [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'maintenance_mode' => app()->isDownForMaintenance() ? 'Enabled' : 'Disabled',
        ];

        return view('admin.dashboard.system', compact('info'));
    }
}