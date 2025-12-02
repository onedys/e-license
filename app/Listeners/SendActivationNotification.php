<?php

namespace App\Listeners;

use App\Events\LicenseActivated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendActivationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LicenseActivated $event): void
    {
        $user = $event->userLicense->user;
        
        $user->notifications()->create([
            'type' => 'license_activated',
            'data' => [
                'license_key' => $event->userLicense->license_key_formatted,
                'confirmation_id' => $event->confirmationId,
                'message' => 'Lisensi berhasil diaktivasi!',
                'url' => route('user.licenses.show', $event->userLicense->id),
            ],
        ]);
    }
}