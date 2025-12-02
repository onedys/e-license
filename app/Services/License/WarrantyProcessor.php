<?php

namespace App\Services\License;

use App\Models\UserLicense;
use App\Models\LicensePool;
use App\Models\WarrantyExchange;
use Illuminate\Support\Facades\Log;

class WarrantyProcessor
{
    protected $licenseAssigner;

    public function __construct(LicenseAssigner $licenseAssigner)
    {
        $this->licenseAssigner = $licenseAssigner;
    }

    public function checkWarrantyEligibility(UserLicense $license): array
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

    public function processWarrantyClaim(UserLicense $license): ?UserLicense
    {
        $eligibility = $this->checkWarrantyEligibility($license);
        
        if (!$eligibility['eligible']) {
            throw new \Exception($eligibility['message']);
        }
        
        $order = $license->order;
        
        $replacementPool = LicensePool::where('product_id', $order->product_id)
            ->where('status', 'active')
            ->first();
        
        if (!$replacementPool) {
            throw new \Exception('Stok lisensi pengganti habis.');
        }
        
        $replacementLicense = UserLicense::create([
            'user_id' => $license->user_id,
            'order_id' => $license->order_id,
            'license_pool_id' => $replacementPool->id,
            'license_key' => $replacementPool->license_key,
            'status' => 'pending',
            'warranty_until' => $license->warranty_until,
            'is_replacement' => true,
            'replaced_license_id' => $license->id,
        ]);
        
        WarrantyExchange::create([
            'user_license_id' => $license->id,
            'new_license_pool_id' => $replacementPool->id,
            'replacement_user_license_id' => $replacementLicense->id,
            'reason' => 'Key Blocked',
            'approved_at' => now(),
            'auto_approved' => true,
        ]);
        
        $license->markAsReplaced($replacementLicense->id);
        
        Log::info('Warranty claim processed', [
            'original_license_id' => $license->id,
            'replacement_license_id' => $replacementLicense->id,
            'user_id' => $license->user_id,
        ]);
        
        return $replacementLicense;
    }

    public function getPendingManualClaims()
    {
        return WarrantyExchange::where('auto_approved', false)
            ->whereNull('approved_at')
            ->with(['userLicense.user', 'userLicense.order.product'])
            ->get();
    }

    public function approveManualClaim(WarrantyExchange $claim, int $adminId): bool
    {
        try {
            $license = $claim->userLicense;
            $replacementLicense = $this->processWarrantyClaim($license);
            
            $claim->update([
                'approved_at' => now(),
                'replacement_user_license_id' => $replacementLicense->id,
                'admin_id' => $adminId,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to approve manual warranty claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}