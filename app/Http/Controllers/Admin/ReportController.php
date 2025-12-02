<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserLicense;
use App\Models\LicensePool;
use App\Models\WarrantyExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $query = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        } else {
            $query->where('payment_status', 'paid');
        }
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        $orders = $query->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $summary = [
            'total_orders' => $orders->total(),
            'total_sales' => $orders->sum('total_amount'),
            'average_order_value' => $orders->average('total_amount') ?? 0,
            'successful_orders' => $orders->where('payment_status', 'paid')->count(),
            'pending_orders' => $orders->where('payment_status', 'pending')->count(),
        ];
        
        $dailySales = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $topProducts = Order::select(
                'product_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.reports.sales', compact(
            'orders', 
            'summary', 
            'dailySales', 
            'topProducts',
            'startDate',
            'endDate'
        ));
    }

    public function salesData(Request $request)
    {
        $startDate = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $productId = $request->get('product_id');
        $paymentStatus = $request->get('payment_status');
        
        $statsQuery = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $paidQuery = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid');
        
        if ($productId) {
            $statsQuery->where('product_id', $productId);
            $paidQuery->where('product_id', $productId);
        }
        
        if ($paymentStatus) {
            $statsQuery->where('payment_status', $paymentStatus);
        }
        
        $totalOrders = $statsQuery->count();
        $successfulOrders = $statsQuery->where('payment_status', 'paid')->count();
        
        $stats = [
            'total_sales' => $paidQuery->sum('total_amount') ?? 0,
            'total_orders' => $totalOrders,
            'avg_order' => $totalOrders > 0 ? ($paidQuery->sum('total_amount') / $successfulOrders) : 0,
            'success_rate' => $totalOrders > 0 ? ($successfulOrders / $totalOrders * 100) : 0,
        ];
        
        $dailySalesQuery = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as amount')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid');
        
        if ($productId) {
            $dailySalesQuery->where('product_id', $productId);
        }
        
        $dailySales = $dailySalesQuery
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $productSalesQuery = Order::select(
                'product_id',
                DB::raw('SUM(total_amount) as amount')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid');
        
        if ($productId) {
            $productSalesQuery->where('product_id', $productId);
        }
        
        $productSales = $productSalesQuery
            ->with('product')
            ->groupBy('product_id')
            ->get();
        
        $ordersQuery = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($productId) {
            $ordersQuery->where('product_id', $productId);
        }
        
        if ($paymentStatus) {
            $ordersQuery->where('payment_status', $paymentStatus);
        }
        
        $orders = $ordersQuery->with(['user', 'product', 'userLicenses'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'order_number' => $order->order_number,
                    'date' => $order->created_at->format('Y-m-d H:i'),
                    'customer' => $order->user->name,
                    'product' => $order->product->name,
                    'quantity' => $order->quantity ?? 1,
                    'amount' => $order->total_amount,
                    'status' => $order->payment_status,
                    'license_sent' => $order->userLicenses->count() > 0,
                ];
            });
        
        return response()->json([
            'stats' => $stats,
            'charts' => [
                'sales' => [
                    'labels' => $dailySales->pluck('date')->toArray(),
                    'data' => $dailySales->pluck('amount')->toArray(),
                ],
                'products' => [
                    'labels' => $productSales->pluck('product.name')->toArray(),
                    'data' => $productSales->pluck('amount')->toArray(),
                ]
            ],
            'data' => $orders->toArray(),
        ]);
    }

    public function activations(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $query = UserLicense::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('product_id')) {
            $query->whereHas('order.product', function($q) use ($request) {
                $q->where('id', $request->product_id);
            });
        }
        
        $licenses = $query->with(['user', 'order.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $summary = [
            'total_licenses' => $licenses->total(),
            'active_licenses' => $licenses->where('status', 'active')->count(),
            'pending_licenses' => $licenses->where('status', 'pending')->count(),
            'blocked_licenses' => $licenses->where('status', 'blocked')->count(),
            'activation_rate' => $licenses->total() > 0 ? 
                ($licenses->where('status', 'active')->count() / $licenses->total() * 100) : 0,
        ];
        
        $dailyActivations = UserLicense::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $statusDistribution = UserLicense::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('status')
            ->get();
        
        return view('admin.reports.activations', compact(
            'licenses', 
            'summary', 
            'dailyActivations', 
            'statusDistribution',
            'startDate',
            'endDate'
        ));
    }

    public function activationsData(Request $request)
    {
        $startDate = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $productId = $request->get('product_id');
        $status = $request->get('status');
        
        $query = UserLicense::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($productId) {
            $query->whereHas('order.product', function($q) use ($productId) {
                $q->where('id', $productId);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $stats = [
            'total_licenses' => $query->count(),
            'active_licenses' => $query->where('status', 'active')->count(),
            'pending_licenses' => $query->where('status', 'pending')->count(),
            'blocked_licenses' => $query->where('status', 'blocked')->count(),
            'activation_rate' => $query->count() > 0 ? 
                ($query->where('status', 'active')->count() / $query->count() * 100) : 0,
        ];
        
        $dailyActivations = UserLicense::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($productId) {
            $dailyActivations->whereHas('order.product', function($q) use ($productId) {
                $q->where('id', $productId);
            });
        }
        
        if ($status) {
            $dailyActivations->where('status', $status);
        }
        
        $dailyActivations = $dailyActivations
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $statusDistribution = UserLicense::select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($productId) {
            $statusDistribution->whereHas('order.product', function($q) use ($productId) {
                $q->where('id', $productId);
            });
        }
        
        if ($status) {
            $statusDistribution->where('status', $status);
        }
        
        $statusDistribution = $statusDistribution
            ->groupBy('status')
            ->get();
        
        $licenses = $query->with(['user', 'order.product'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($license) {
                return [
                    'license_key' => substr($license->getPlainAttribute('license_key'), 0, 8) . '...',
                    'activated_date' => $license->activated_at ? $license->activated_at->format('Y-m-d H:i') : 'N/A',
                    'customer' => $license->user->name,
                    'product' => $license->order->product->name,
                    'status' => $license->status,
                    'activation_attempts' => $license->activation_attempts,
                    'last_activity' => $license->last_validated_at ? 
                        $license->last_validated_at->diffForHumans() : 'Never',
                ];
            });
        
        return response()->json([
            'stats' => $stats,
            'charts' => [
                'activation_trend' => [
                    'labels' => $dailyActivations->pluck('date')->toArray(),
                    'total' => $dailyActivations->pluck('total')->toArray(),
                    'active' => $dailyActivations->pluck('active')->toArray(),
                ],
                'status_distribution' => [
                    'labels' => $statusDistribution->pluck('status')->toArray(),
                    'data' => $statusDistribution->pluck('count')->toArray(),
                ]
            ],
            'data' => $licenses->toArray(),
        ]);
    }

    public function licenseUsage(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $poolStats = [
            'total_keys' => LicensePool::count(),
            'active_keys' => LicensePool::where('status', 'active')->count(),
            'blocked_keys' => LicensePool::where('status', 'blocked')->count(),
            'invalid_keys' => LicensePool::where('status', 'invalid')->count(),
            'available_keys' => LicensePool::where('status', 'active')
                ->whereDoesntHave('userLicenses')
                ->count(),
        ];
        
        $mostUsedLicenses = LicensePool::withCount(['userLicenses'])
            ->with('product')
            ->having('userLicenses_count', '>', 0)
            ->orderBy('userLicenses_count', 'desc')
            ->limit(20)
            ->get();
        
        $assignmentTrend = UserLicense::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as assignments')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $productUsage = LicensePool::select(
                'product_id',
                DB::raw('COUNT(*) as total_keys'),
                DB::raw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_keys'),
                DB::raw('COUNT(user_licenses.id) as assigned_keys')
            )
            ->leftJoin('user_licenses', 'license_pools.id', '=', 'user_licenses.license_pool_id')
            ->with('product')
            ->groupBy('product_id')
            ->get();
        
        return view('admin.reports.license-usage', compact(
            'poolStats',
            'mostUsedLicenses',
            'assignmentTrend',
            'productUsage',
            'startDate',
            'endDate'
        ));
    }

    public function licenseUsageData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $productId = $request->get('product_id');
        $licenseStatus = $request->get('license_status');
        
        $licenseQuery = LicensePool::query();
        $assignedQuery = LicensePool::whereHas('userLicenses');
        
        if ($productId) {
            $licenseQuery->where('product_id', $productId);
            $assignedQuery->where('product_id', $productId);
        }
        
        if ($licenseStatus) {
            $licenseQuery->where('status', $licenseStatus);
            $assignedQuery->where('status', $licenseStatus);
        }
        
        $totalKeys = $licenseQuery->count();
        $activeKeys = $licenseQuery->where('status', 'active')->count();
        $availableKeys = LicensePool::where('status', 'active')
            ->whereDoesntHave('userLicenses');
        
        if ($productId) {
            $availableKeys->where('product_id', $productId);
        }
        
        $availableKeys = $availableKeys->count();
        
        $stats = [
            'total_keys' => $totalKeys,
            'active_keys' => $activeKeys,
            'available_keys' => $availableKeys,
            'usage_rate' => $totalKeys > 0 ? 
                (($totalKeys - $availableKeys) / $totalKeys * 100) : 0,
        ];
        
        $assignmentTrend = UserLicense::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as assignments')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if ($productId) {
            $assignmentTrend->whereHas('licensePool', function($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }
        
        $assignmentTrend = $assignmentTrend
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $statusDistribution = LicensePool::select(
                'status',
                DB::raw('COUNT(*) as count')
            );
        
        if ($productId) {
            $statusDistribution->where('product_id', $productId);
        }
        
        if ($licenseStatus) {
            $statusDistribution->where('status', $licenseStatus);
        }
        
        $statusDistribution = $statusDistribution
            ->groupBy('status')
            ->get();
        
        $productUsage = LicensePool::select(
                'product_id',
                DB::raw('COUNT(*) as total_keys'),
                DB::raw('COUNT(user_licenses.id) as assigned_keys')
            )
            ->leftJoin('user_licenses', 'license_pools.id', '=', 'user_licenses.license_pool_id')
            ->with('product')
            ->groupBy('product_id');
        
        if ($licenseStatus) {
            $productUsage->where('license_pools.status', $licenseStatus);
        }
        
        $productUsage = $productUsage->get();
        
        $mostUsedQuery = LicensePool::withCount(['userLicenses'])
            ->with(['product', 'userLicenses'])
            ->having('userLicenses_count', '>', 0)
            ->orderBy('userLicenses_count', 'desc')
            ->limit(20);
        
        if ($productId) {
            $mostUsedQuery->where('product_id', $productId);
        }
        
        if ($licenseStatus) {
            $mostUsedQuery->where('status', $licenseStatus);
        }
        
        $mostUsedLicenses = $mostUsedQuery->get()
            ->map(function($license) {
                $activeAssignments = $license->userLicenses->where('status', 'active')->count();
                $firstUsed = $license->userLicenses->min('created_at');
                $lastUsed = $license->userLicenses->max('last_validated_at');
                
                return [
                    'license_key' => substr($license->getPlainAttribute('license_key'), 0, 10) . '...',
                    'product' => $license->product->name,
                    'status' => $license->status,
                    'total_assignments' => $license->userLicenses_count,
                    'active_assignments' => $activeAssignments,
                    'first_used' => $firstUsed ? $firstUsed->format('Y-m-d') : 'N/A',
                    'last_used' => $lastUsed ? $lastUsed->format('Y-m-d') : 'N/A',
                ];
            });
        
        return response()->json([
            'stats' => $stats,
            'charts' => [
                'assignment_trend' => [
                    'labels' => $assignmentTrend->pluck('date')->toArray(),
                    'data' => $assignmentTrend->pluck('assignments')->toArray(),
                ],
                'status_distribution' => [
                    'labels' => $statusDistribution->pluck('status')->toArray(),
                    'data' => $statusDistribution->pluck('count')->toArray(),
                ],
                'product_usage' => [
                    'labels' => $productUsage->pluck('product.name')->toArray(),
                    'total_keys' => $productUsage->pluck('total_keys')->toArray(),
                    'assigned_keys' => $productUsage->pluck('assigned_keys')->toArray(),
                ]
            ],
            'data' => $mostUsedLicenses->toArray(),
        ]);
    }

    public function warrantyClaims(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $query = WarrantyExchange::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        $claims = $query->with(['userLicense.user', 'replacementLicense', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        $summary = [
            'total_claims' => $claims->total(),
            'auto_approved' => $claims->where('auto_approved', true)->count(),
            'manually_approved' => $claims->where('auto_approved', false)->whereNotNull('approved_at')->count(),
            'pending' => $claims->whereNull('approved_at')->count(),
            'rejected' => 0,
        ];
        
        $dailyClaims = WarrantyExchange::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN auto_approved = 1 THEN 1 ELSE 0 END) as auto_approved'),
                DB::raw('SUM(CASE WHEN auto_approved = 0 AND approved_at IS NOT NULL THEN 1 ELSE 0 END) as manual_approved')
            )
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.reports.warranty-claims', compact(
            'claims',
            'summary',
            'dailyClaims',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'sales');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $fileName = $type . '-report-' . date('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        $callback = function() use ($type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            switch ($type) {
                case 'sales':
                    $this->exportSalesReport($file, $startDate, $endDate);
                    break;
                case 'activations':
                    $this->exportActivationsReport($file, $startDate, $endDate);
                    break;
                case 'license-usage':
                    $this->exportLicenseUsageReport($file);
                    break;
                case 'warranty':
                    $this->exportWarrantyReport($file, $startDate, $endDate);
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    private function exportSalesReport($file, $startDate, $endDate)
    {
        fputcsv($file, [
            'Order Number',
            'Customer',
            'Product',
            'Amount',
            'Status',
            'Payment Method',
            'Date',
        ]);
        
        $orders = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('payment_status', 'paid')
            ->with(['user', 'product'])
            ->get();
        
        foreach ($orders as $order) {
            fputcsv($file, [
                $order->order_number,
                $order->user->name,
                $order->product->name,
                $order->total_amount,
                $order->payment_status,
                $order->payment_method,
                $order->created_at->format('Y-m-d H:i:s'),
            ]);
        }
    }

    private function exportActivationsReport($file, $startDate, $endDate)
    {
        fputcsv($file, [
            'License Key',
            'Customer',
            'Product',
            'Status',
            'Activation Attempts',
            'Activated At',
            'Created At',
        ]);
        
        $licenses = UserLicense::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['user', 'order.product'])
            ->get();
        
        foreach ($licenses as $license) {
            fputcsv($file, [
                $license->getPlainAttribute('license_key'),
                $license->user->name,
                $license->order->product->name,
                $license->status,
                $license->activation_attempts,
                $license->activated_at?->format('Y-m-d H:i:s'),
                $license->created_at->format('Y-m-d H:i:s'),
            ]);
        }
    }

    private function exportLicenseUsageReport($file)
    {
        fputcsv($file, [
            'License Key',
            'Product',
            'Status',
            'Total Assignments',
            'Active Assignments',
            'Last Validated',
        ]);
        
        $licenses = LicensePool::withCount(['userLicenses'])
            ->with(['product', 'userLicenses'])
            ->get();
        
        foreach ($licenses as $license) {
            $activeAssignments = $license->userLicenses->where('status', 'active')->count();
            
            fputcsv($file, [
                $license->getPlainAttribute('license_key'),
                $license->product->name,
                $license->status,
                $license->userLicenses_count,
                $activeAssignments,
                $license->last_validated_at?->format('Y-m-d H:i:s'),
            ]);
        }
    }

    private function exportWarrantyReport($file, $startDate, $endDate)
    {
        fputcsv($file, [
            'Claim Date',
            'Customer',
            'Old License',
            'New License',
            'Reason',
            'Auto Approved',
            'Approved At',
            'Approved By',
        ]);
        
        $claims = WarrantyExchange::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['userLicense.user', 'replacementLicense', 'admin'])
            ->get();
        
        foreach ($claims as $claim) {
            fputcsv($file, [
                $claim->created_at->format('Y-m-d H:i:s'),
                $claim->userLicense->user->name,
                $claim->userLicense->getPlainAttribute('license_key'),
                $claim->replacementLicense ? $claim->replacementLicense->getPlainAttribute('license_key') : 'N/A',
                $claim->reason,
                $claim->auto_approved ? 'Yes' : 'No',
                $claim->approved_at?->format('Y-m-d H:i:s'),
                $claim->admin ? $claim->admin->name : 'N/A',
            ]);
        }
    }
}