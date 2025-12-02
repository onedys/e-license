<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationIdTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'installation_id_hash',
        'user_license_id',
        'first_used_at',
    ];

    protected $casts = [
        'first_used_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userLicense()
    {
        return $this->belongsTo(UserLicense::class);
    }
}