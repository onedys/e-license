<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsAttributes;

class ActivationLog extends Model
{
    use HasFactory, EncryptsAttributes;

    protected $fillable = [
        'user_license_id',
        'installation_id',
        'api_response',
        'status',
    ];

    protected $casts = [
        'api_response' => 'array',
    ];

    protected $encryptedAttributes = [
        'installation_id',
    ];

    // Relations
    public function userLicense()
    {
        return $this->belongsTo(UserLicense::class);
    }
}