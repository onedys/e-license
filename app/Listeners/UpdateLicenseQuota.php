<?php

namespace App\Listeners;

use App\Events\LicenseActivated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLicenseQuota implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LicenseActivated $event): void
    {
        // This is where you would update any license quota or usage tracking
        // For now, we'll just log it
        
        \Log::info('License quota updated', [
            'user_license_id' => $event->userLicense->id,
            'user_id' => $event->userLicense->user_id,
            'order_id' => $event->userLicense->order_id,
        ]);
    }
}