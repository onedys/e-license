<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserLicense;
use App\Models\WarrantyExchange;
use App\Services\License\LicenseAssigner;
use App\Services\Notification\DashboardNotification;
use App\Services\Security\FraudDetection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarrantyController extends Controller
{
    protected $licenseAssigner;
    protected $notificationService;
    protected $fraudDetection;

    public function __construct(LicenseAssigner $licenseAssigner, DashboardNotification $notificationService, FraudDetection $fraudDetection)
    {
        $this->licenseAssigner = $licenseAssigner;
        $this->notificationService = $notificationService;
        $this->fraudDetection = $fraudDetection;
    }

    public function showClaimForm()
    {
        $user = Auth::user();
        
        $eligibleLicenses = $user->licenses()
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
            });
        
        return view('user.warranty.claim', compact('eligibleLicenses'));
    }

    public function claim(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);
        
        $user = Auth::user();
        $licenseKey = $request->license_key;
        
        $fraudCheck = $this->fraudDetection->checkWarrantyPattern($user, $licenseKey);
        
        if ($fraudCheck['risk_level'] === 'high') {
            return back()->withErrors([
                'license_key' => 'Klaim garansi memerlukan review manual. Silakan hubungi admin.',
            ]);
        }
        
        $license = UserLicense::where('license_key', encrypt($licenseKey))
                             ->where('user_id', $user->id)
                             ->first();
        
        if (!$license) {
            return back()->withErrors([
                'license_key' => 'Lisensi tidak ditemukan atau bukan milik Anda.',
            ]);
        }
        
        $eligibilityCheck = $this->checkWarrantyEligibility($license);
        
        if (!$eligibilityCheck['eligible']) {
            return back()->withErrors([
                'license_key' => $eligibilityCheck['message'],
            ]);
        }
        
        $recentClaim = WarrantyExchange::where('user_license_id', $license->id)
                                      ->where('created_at', '>', now()->subHours(1))
                                      ->exists();
        
        if ($recentClaim) {
            return back()->withErrors([
                'license_key' => 'Klaim garansi untuk lisensi ini sudah diproses dalam 1 jam terakhir.',
            ]);
        }
        
        $replacementLicense = $this->processAutoApproval($license);
        
        if (!$replacementLicense) {
            return back()->withErrors([
                'license_key' => 'Stok lisensi pengganti habis. Silakan hubungi admin.',
            ]);
        }
        
        WarrantyExchange::create([
            'user_license_id' => $license->id,
            'new_license_pool_id' => $replacementLicense->license_pool_id,
            'replacement_user_license_id' => $replacementLicense->id,
            'reason' => 'Key Blocked',
            'approved_at' => now(),
            'auto_approved' => true,
        ]);
        
        $license->markAsReplaced($replacementLicense->id);
        
        $this->notificationService->sendWarrantyApprovedNotification($license, $replacementLicense);
        
        Log::info('Warranty claim auto-approved', [
            'user_id' => $user->id,
            'original_license_id' => $license->id,
            'replacement_license_id' => $replacementLicense->id,
        ]);
        
        return redirect()->route('user.licenses.show', $replacementLicense->id)
            ->with('success', 'Garansi berhasil! Lisensi baru telah ditambahkan: ' . 
                   $replacementLicense->license_key_formatted);
    }

    private function checkWarrantyEligibility(UserLicense $license): array
    {
        if ($license->status !== 'blocked') {
            return [
                'eligible' => false,
                'message' => 'Lisensi ini tidak dalam status blocked.',
            ];
        }
        
        if (!$license->is_warranty_valid) {
            return [
                'eligible' => false,
                'message' => 'Masa garansi sudah habis.',
            ];
        }
        
        if ($license->replaced_by) {
            return [
                'eligible' => false,
                'message' => 'Lisensi ini sudah diganti sebelumnya.',
            ];
        }
        
        $hasSuccessfulActivation = $license->activationLogs()
            ->where('status', 'success')
            ->exists();
        
        if ($hasSuccessfulActivation) {
            return [
                'eligible' => false,
                'message' => 'Lisensi ini sudah pernah aktivasi sukses. Tidak bisa klaim garansi.',
            ];
        }
        
        if ($license->activation_attempts === 0) {
            return [
                'eligible' => false,
                'message' => 'Lisensi ini belum pernah dicoba diaktivasi.',
            ];
        }
        
        return [
            'eligible' => true,
            'message' => 'Lisensi eligible untuk garansi.',
        ];
    }

    private function processAutoApproval(UserLicense $originalLicense): ?UserLicense
    {
        $order = $originalLicense->order;
        
        try {
            $tempOrder = new \App\Models\Order();
            $tempOrder->fill([
                'user_id' => $originalLicense->user_id,
                'product_id' => $order->product_id,
                'quantity' => 1,
                'total_amount' => 0,
                'warranty_until' => $originalLicense->warranty_until,
            ]);
            
            $assignedLicenses = $this->licenseAssigner->assignLicensesToOrder($tempOrder);
            
            if (empty($assignedLicenses)) {
                return null;
            }
            
            $replacementLicense = $assignedLicenses[0];
            
            $replacementLicense->update([
                'is_replacement' => true,
                'replaced_license_id' => $originalLicense->id,
            ]);
            
            return $replacementLicense;
            
        } catch (\Exception $e) {
            Log::error('Failed to assign replacement license', [
                'original_license_id' => $originalLicense->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    public function history()
    {
        $user = Auth::user();
        
        $warrantyClaims = WarrantyExchange::whereHas('userLicense', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['userLicense', 'replacementLicense'])
        ->latest()
        ->paginate(20);
        
        return view('user.warranty.history', compact('warrantyClaims'));
    }
}