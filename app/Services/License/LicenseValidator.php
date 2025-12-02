<?php

namespace App\Services\License;

use App\Models\LicensePool;
use App\Models\UserLicense;
use Illuminate\Support\Facades\Log;

class LicenseValidator
{
    protected $pidKeyService;
    protected $cidService;

    public function __construct(PidKeyService $pidKeyService, CidService $cidService)
    {
        $this->pidKeyService = $pidKeyService;
        $this->cidService = $cidService;
    }

    public function validateLicenseForSale(string $licenseKey): array
    {
        $validation = $this->pidKeyService->validateKey($licenseKey);
        
        return [
            'is_valid' => $validation['is_valid'],
            'errorcode' => $validation['errorcode'],
            'product_name' => $validation['product_name'],
            'is_retail' => $validation['is_retail'],
            'blocked' => $validation['blocked'],
            'remaining' => $validation['remaining'],
        ];
    }

    public function validateInstallationId(string $installationId): bool
    {
        return $this->cidService->validateInstallationId($installationId);
    }

    public function activateLicense(UserLicense $userLicense, string $installationId): array
    {
        if (!$this->validateInstallationId($installationId)) {
            return [
                'success' => false,
                'error' => 'Installation ID harus 54 atau 63 digit angka.',
                'type' => 'invalid_format',
            ];
        }
        
        $result = $this->cidService->generateCID($installationId);
        
        if ($result['success']) {
            return [
                'success' => true,
                'confirmation_id' => $result['confirmation_id'],
                'type' => 'success',
                'raw_response' => $result,
            ];
        }
        
        return [
            'success' => false,
            'error' => $result['error'],
            'type' => $result['type'],
            'raw_response' => $result,
        ];
    }

    public function bulkValidateLicensePool(int $productId): array
    {
        $licenses = LicensePool::where('product_id', $productId)
            ->where('status', 'active')
            ->get();
        
        $results = [
            'total' => $licenses->count(),
            'valid' => 0,
            'blocked' => 0,
            'errors' => 0,
            'details' => [],
        ];
        
        foreach ($licenses as $license) {
            try {
                $validation = $this->validateLicenseForSale(
                    $license->getPlainAttribute('license_key')
                );
                
                if ($validation['is_valid']) {
                    $license->update([
                        'errorcode' => $validation['errorcode'],
                        'last_validated_at' => now(),
                        'validation_count' => $license->validation_count + 1,
                    ]);
                    $results['valid']++;
                } else {
                    $license->update([
                        'errorcode' => $validation['errorcode'],
                        'status' => 'blocked',
                        'last_validated_at' => now(),
                        'validation_count' => $license->validation_count + 1,
                    ]);
                    $results['blocked']++;
                }
                
                $results['details'][] = [
                    'license_id' => $license->id,
                    'license_key' => $license->keyname_with_dash,
                    'is_valid' => $validation['is_valid'],
                    'errorcode' => $validation['errorcode'],
                ];
                
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'license_id' => $license->id,
                    'license_key' => $license->keyname_with_dash,
                    'error' => $e->getMessage(),
                ];
            }
            
            usleep(500000);
        }
        
        return $results;
    }
}