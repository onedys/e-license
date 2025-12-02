<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyExchange;
use App\Models\LicensePool;
use App\Models\UserLicense;
use App\Services\License\LicenseAssigner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarrantyController extends Controller
{
    protected $licenseAssigner;

    public function __construct(LicenseAssigner $licenseAssigner)
    {
        $this->licenseAssigner = $licenseAssigner;
    }

    /**
     * Display a listing of warranty claims.
     */
    public function index(Request $request)
    {
        $query = WarrantyExchange::with(['userLicense.user', 'replacementLicense', 'admin']);
        
        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('approved_at');
            } elseif ($request->status === 'approved') {
                $query->whereNotNull('approved_at');
            }
        }
        
        if ($request->filled('type')) {
            $query->where('auto_approved', $request->type === 'auto');
        }
        
        $warrantyClaims = $query->latest()->paginate(50);
        
        $stats = [
            'total' => WarrantyExchange::count(),
            'pending' => WarrantyExchange::whereNull('approved_at')->count(),
            'auto_approved' => WarrantyExchange::where('auto_approved', true)->count(),
            'today' => WarrantyExchange::whereDate('created_at', today())->count(),
        ];
        
        return view('admin.warranty.index', compact('warrantyClaims', 'stats'));
    }

    /**
     * Display pending warranty claims (non-auto approved).
     */
    public function pending()
    {
        $pendingClaims = WarrantyExchange::where('auto_approved', false)
            ->whereNull('approved_at')
            ->with(['userLicense.user', 'userLicense.order.product'])
            ->latest()
            ->paginate(50);
        
        return view('admin.warranty.pending', compact('pendingClaims'));
    }

    /**
     * Display the specified warranty claim.
     */
    public function show(WarrantyExchange $warrantyExchange)
    {
        $warrantyExchange->load([
            'userLicense.user', 
            'userLicense.order.product',
            'replacementLicense',
            'admin',
            'newLicensePool'
        ]);
        
        return view('admin.warranty.show', compact('warrantyExchange'));
    }

    /**
     * Approve a warranty claim.
     */
    public function approve(Request $request, WarrantyExchange $warrantyExchange)
    {
        // Check if already approved
        if ($warrantyExchange->approved_at) {
            return back()->with('error', 'Klaim garansi sudah disetujui sebelumnya.');
        }
        
        // Get the original license
        $originalLicense = $warrantyExchange->userLicense;
        
        if (!$originalLicense) {
            return back()->with('error', 'Lisensi asli tidak ditemukan.');
        }
        
        // Find replacement license from pool
        $replacementLicensePool = LicensePool::where('product_id', $originalLicense->order->product_id)
            ->where('status', 'active')
            ->first();
        
        if (!$replacementLicensePool) {
            return back()->with('error', 'Tidak ada lisensi pengganti yang tersedia di pool.');
        }
        
        // Create replacement user license
        $replacementLicense = UserLicense::create([
            'user_id' => $originalLicense->user_id,
            'order_id' => $originalLicense->order_id,
            'license_pool_id' => $replacementLicensePool->id,
            'license_key' => $replacementLicensePool->license_key,
            'status' => 'pending',
            'warranty_until' => $originalLicense->warranty_until,
            'is_replacement' => true,
            'replaced_license_id' => $originalLicense->id,
        ]);
        
        // Update warranty exchange
        $warrantyExchange->update([
            'approved_at' => now(),
            'replacement_user_license_id' => $replacementLicense->id,
            'admin_id' => auth()->id(),
        ]);
        
        // Mark original license as replaced
        $originalLicense->markAsReplaced($replacementLicense->id);
        
        Log::info('Warranty claim manually approved', [
            'admin_id' => auth()->id(),
            'warranty_exchange_id' => $warrantyExchange->id,
            'original_license_id' => $originalLicense->id,
            'replacement_license_id' => $replacementLicense->id,
        ]);
        
        return back()->with('success', 'Klaim garansi disetujui. Lisensi pengganti telah dikirim.');
    }

    /**
     * Reject a warranty claim.
     */
    public function reject(Request $request, WarrantyExchange $warrantyExchange)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);
        
        // Check if already processed
        if ($warrantyExchange->approved_at) {
            return back()->with('error', 'Klaim garansi sudah diproses sebelumnya.');
        }
        
        // Update warranty exchange with rejection
        $warrantyExchange->update([
            'approved_at' => now(), // Mark as processed
            'admin_id' => auth()->id(),
            'reason' => $warrantyExchange->reason . ' | DITOLAK: ' . $request->rejection_reason,
        ]);
        
        Log::info('Warranty claim rejected', [
            'admin_id' => auth()->id(),
            'warranty_exchange_id' => $warrantyExchange->id,
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return back()->with('success', 'Klaim garansi ditolak.');
    }
}