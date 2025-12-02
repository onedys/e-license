<?php

namespace App\Events;

use App\Models\UserLicense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userLicense;
    public $confirmationId;

    public function __construct(UserLicense $userLicense, string $confirmationId)
    {
        $this->userLicense = $userLicense;
        $this->confirmationId = $confirmationId;
    }
}