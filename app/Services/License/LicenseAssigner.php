<?php

namespace App\Services\License;

use App\Models\LicensePool;
use App\Models\Order;
use App\Models\UserLicense;
use Illuminate\Support\Facades\Log;

class LicenseAssigner
{
    protected $pidKeyService;

    public function __construct(PidKeyService $pidKeyService)
    {
        $this->pidKeyService = $pidKeyService;
    }

    public function assignLicensesToOrder(Order $order): array
    {
        $results = [];
        $assignedLicenses = [];
        
        for ($i = 0; $i < $order->quantity; $i++) {
            try {
                $license = $this->assignSingleLicense($order);
                
                if ($license) {
                    $assignedLicenses[] = $license;
                    $results[] = [
                        'success' => true,
                        'license_key' => $license->getPlainAttribute('license_key'),
                        'message' => 'License assigned successfully',
                    ];
                } else {
                    $results[] = [
                        'success' => false,
                        'message' => 'No available license in pool',
                    ];
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to assign license', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                
                $results[] = [
                    'success' => false,
                    'message' => 'Failed to assign license: ' . $e->getMessage(),
                ];
            }
        }
        
        $order->update([
            'metadata' => array_merge($order->metadata ?? [], [
                'license_assignment' => [
                    'completed_at' => now(),
                    'total_requested' => $order->quantity,
                    'total_assigned' => count($assignedLicenses),
                    'results' => $results,
                ]
            ])
        ]);
        
        return $assignedLicenses;
    }

    private function assignSingleLicense(Order $order): ?UserLicense
    {
        $licensePool = $this->findAvailableLicense($order->product_id);
        
        if (!$licensePool) {
            Log::warning('No available license in pool', [
                'product_id' => $order->product_id,
                'order_id' => $order->id,
            ]);
            
            return null;
        }
        
        $validation = $this->pidKeyService->validateKey(
            $licensePool->getPlainAttribute('license_key')
        );
        
        if (!$validation['is_valid']) {
            $licensePool->update(['status' => 'invalid']);
            Log::warning('License invalid during assignment', [
                'license_pool_id' => $licensePool->id,
                'errorcode' => $validation['errorcode'],
            ]);
            
            return $this->assignSingleLicense($order);
        }
        
        $userLicense = UserLicense::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'license_pool_id' => $licensePool->id,
            'license_key' => $licensePool->license_key,
            'status' => 'pending',
            'warranty_until' => $order->warranty_until,
        ]);
        
        $licensePool->update([
            'last_validated_at' => now(),
            'validation_count' => $licensePool->validation_count + 1,
        ]);
        
        Log::info('License assigned to user', [
            'user_license_id' => $userLicense->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'license_pool_id' => $licensePool->id,
        ]);
        
        return $userLicense;
    }

    private function findAvailableLicense($productId): ?LicensePool
    {
        $licensePool = LicensePool::where('product_id', $productId)
            ->where('status', 'active')
            ->whereDoesntHave('userLicenses')
            ->first();
        
        if ($licensePool) {
            return $licensePool;
        }
        
        $licensePool = LicensePool::where('product_id', $productId)
            ->where('status', 'active')
            ->whereHas('userLicenses', function ($query) {
                $query->where('status', '!=', 'replaced');
            }, '<', 100)
            ->first();
        
        return $licensePool;
    }

    private function userHasLicenseKey($userId, $licensePoolId): bool
    {
        return UserLicense::where('user_id', $userId)
            ->where('license_pool_id', $licensePoolId)
            ->whereIn('status', ['pending', 'active'])
            ->exists();
    }
}