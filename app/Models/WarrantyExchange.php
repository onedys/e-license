<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_license_id',
        'new_license_pool_id',
        'replacement_user_license_id',
        'reason',
        'approved_at',
        'auto_approved',
        'admin_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'auto_approved' => 'boolean',
    ];

    public function userLicense()
    {
        return $this->belongsTo(UserLicense::class);
    }

    public function newLicensePool()
    {
        return $this->belongsTo(LicensePool::class, 'new_license_pool_id');
    }

    public function replacementLicense()
    {
        return $this->belongsTo(UserLicense::class, 'replacement_user_license_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}