<?php

namespace App\Events;

use App\Models\UserLicense;
use App\Models\UserLicense as ReplacementLicense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarrantyApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $originalLicense;
    public $replacementLicense;
    public $autoApproved;

    public function __construct(
        UserLicense $originalLicense,
        UserLicense $replacementLicense,
        bool $autoApproved = true
    ) {
        $this->originalLicense = $originalLicense;
        $this->replacementLicense = $replacementLicense;
        $this->autoApproved = $autoApproved;
    }
}