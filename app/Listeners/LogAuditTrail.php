<?php

namespace App\Listeners;

use App\Events\LicenseActivated;
use App\Events\WarrantyApproved;
use App\Events\PaymentCompleted;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAuditTrail
{
    public function handle($event): void
    {
        if ($event instanceof LicenseActivated) {
            AuditLog::create([
                'user_id' => $event->userLicense->user_id,
                'action' => 'license_activated',
                'entity_type' => 'UserLicense',
                'entity_id' => $event->userLicense->id,
                'new_data' => [
                    'confirmation_id' => $event->confirmationId,
                    'activated_at' => now()->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
        
        if ($event instanceof WarrantyApproved) {
            AuditLog::create([
                'user_id' => $event->originalLicense->user_id,
                'action' => 'warranty_approved',
                'entity_type' => 'WarrantyExchange',
                'entity_id' => null,
                'new_data' => [
                    'original_license_id' => $event->originalLicense->id,
                    'replacement_license_id' => $event->replacementLicense->id,
                    'auto_approved' => $event->autoApproved,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
        
        if ($event instanceof PaymentCompleted) {
            AuditLog::create([
                'user_id' => $event->order->user_id,
                'action' => 'payment_completed',
                'entity_type' => 'Order',
                'entity_id' => $event->order->id,
                'new_data' => [
                    'order_number' => $event->order->order_number,
                    'total_amount' => $event->order->total_amount,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}