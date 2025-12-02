<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\LicenseActivated;
use App\Events\WarrantyApproved;
use App\Events\PaymentCompleted;
use App\Listeners\SendActivationNotification;
use App\Listeners\UpdateLicenseQuota;
use App\Listeners\LogAuditTrail;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        LicenseActivated::class => [
            SendActivationNotification::class,
            UpdateLicenseQuota::class,
            LogAuditTrail::class,
        ],
        WarrantyApproved::class => [
            LogAuditTrail::class,
        ],
        PaymentCompleted::class => [
            LogAuditTrail::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}