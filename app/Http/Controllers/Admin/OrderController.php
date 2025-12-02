<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\License\LicenseAssigner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $licenseAssigner;

    public function __construct(LicenseAssigner $licenseAssigner)
    {
        $this->licenseAssigner = $licenseAssigner;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'product']);
        
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('tripay_reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('username', 'LIKE', "%{$search}%")
                         ->orWhere('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->latest()->paginate(50);
        
        $stats = [
            'total' => Order::count(),
            'total_sales' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending' => Order::where('payment_status', 'pending')->count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'today' => Order::whereDate('created_at', today())->count(),
            'today_sales' => Order::whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total_amount'),
        ];
        
        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'product', 'licenses', 'payment']);
        
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,expired',
        ]);
        
        $oldStatus = $order->payment_status;
        $order->update([
            'payment_status' => $request->status,
            'paid_at' => $request->status === 'paid' ? now() : null,
        ]);
        
        if ($oldStatus !== 'paid' && $request->status === 'paid') {
            try {
                $this->licenseAssigner->assignLicensesToOrder($order);
                Log::info('Licenses assigned after manual status update', [
                    'order_id' => $order->id,
                    'admin_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to assign licenses after manual status update', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info('Order status updated by admin', [
            'admin_id' => auth()->id(),
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);
        
        return back()->with('success', 'Order status updated successfully.');
    }

    public function resendLicense(Order $order)
    {
        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Order belum dibayar.');
        }
        
        if ($order->licenses->isEmpty()) {
            try {
                $this->licenseAssigner->assignLicensesToOrder($order);
                
                Log::info('Licenses reassigned by admin', [
                    'admin_id' => auth()->id(),
                    'order_id' => $order->id,
                ]);
                
                return back()->with('success', 'Lisensi berhasil dikirim ulang.');
                
            } catch (\Exception $e) {
                Log::error('Failed to resend licenses', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                
                return back()->with('error', 'Gagal mengirim lisensi: ' . $e->getMessage());
            }
        }
        
        return back()->with('info', 'Lisensi sudah pernah dikirim sebelumnya.');
    }
}