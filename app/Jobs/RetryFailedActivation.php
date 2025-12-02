<?php

namespace App\Jobs;

use App\Models\UserLicense;
use App\Services\License\CidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFailedActivation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $userLicenseId;
    protected $installationId;

    public function __construct(int $userLicenseId, string $installationId)
    {
        $this->userLicenseId = $userLicenseId;
        $this->installationId = $installationId;
    }

    public function handle(CidService $cidService): void
    {
        $userLicense = UserLicense::find($this->userLicenseId);
        
        if (!$userLicense) {
            Log::error('User license not found for retry', [
                'user_license_id' => $this->userLicenseId,
            ]);
            return;
        }
        
        if ($userLicense->status !== 'pending') {
            Log::info('License is no longer pending, skipping retry', [
                'user_license_id' => $this->userLicenseId,
                'status' => $userLicense->status,
            ]);
            return;
        }
        
        try {
            $result = $cidService->generateCID($this->installationId);
            
            if ($result['success']) {
                $userLicense->markAsActivated(
                    $result['confirmation_id'],
                    $this->installationId
                );
                
                Log::info('Retry activation succeeded', [
                    'user_license_id' => $this->userLicenseId,
                    'confirmation_id' => $result['confirmation_id'],
                ]);
                
            } elseif ($result['type'] === 'key_blocked') {
                $userLicense->markAsBlocked();
                
                Log::info('Retry activation resulted in key blocked', [
                    'user_license_id' => $this->userLicenseId,
                ]);
                
            } else {
                Log::warning('Retry activation failed', [
                    'user_license_id' => $this->userLicenseId,
                    'error' => $result['error'],
                    'type' => $result['type'],
                ]);
                
                // You could retry again or mark as error
                $this->release(300); // Retry in 5 minutes
            }
            
        } catch (\Exception $e) {
            Log::error('Error in retry activation job', [
                'user_license_id' => $this->userLicenseId,
                'error' => $e->getMessage(),
            ]);
            
            $this->release(600); // Retry in 10 minutes
        }
    }
}