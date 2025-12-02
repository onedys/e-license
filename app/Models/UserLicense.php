<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsAttributes;

class UserLicense extends Model
{
    use HasFactory, EncryptsAttributes;

    protected $fillable = [
        'user_id',
        'order_id',
        'license_pool_id',
        'license_key',
        'status',
        'activation_attempts',
        'installation_id',
        'confirmation_id',
        'activated_at',
        'blocked_at',
        'warranty_until',
        'replaced_by',
        'replaced_at',
        'is_replacement',
    ];

    protected $casts = [
        'activation_attempts' => 'integer',
        'activated_at' => 'datetime',
        'blocked_at' => 'datetime',
        'warranty_until' => 'datetime',
        'replaced_at' => 'datetime',
        'is_replacement' => 'boolean',
    ];

    protected $encryptedAttributes = [
        'license_key',
        'installation_id',
        'confirmation_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function licensePool()
    {
        return $this->belongsTo(LicensePool::class);
    }

    public function replacementLicense()
    {
        return $this->belongsTo(UserLicense::class, 'replaced_by');
    }

    public function replacedLicense()
    {
        return $this->hasOne(UserLicense::class, 'replaced_by');
    }

    public function activationLogs()
    {
        return $this->hasMany(ActivationLog::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeWarrantyValid($query)
    {
        return $query->where('warranty_until', '>=', now());
    }

    public function getLicenseKeyFormattedAttribute()
    {
        return $this->getPlainAttribute('license_key');
    }

    public function getConfirmationIdFormattedAttribute()
    {
        return $this->getPlainAttribute('confirmation_id');
    }

    public function getIsWarrantyValidAttribute()
    {
        return $this->warranty_until && now()->lte($this->warranty_until);
    }

    public function getCanClaimWarrantyAttribute()
    {
        return $this->status === 'blocked' && 
               $this->is_warranty_valid &&
               !$this->replaced_by &&
               $this->activation_attempts > 0;
    }

    public function markAsActivated(string $confirmationId, string $installationId)
    {
        $this->update([
            'status' => 'active',
            'confirmation_id' => $confirmationId,
            'installation_id' => $installationId,
            'activated_at' => now(),
            'activation_attempts' => $this->activation_attempts + 1,
        ]);
    }

    public function markAsBlocked()
    {
        $this->update([
            'status' => 'blocked',
            'blocked_at' => now(),
            'activation_attempts' => $this->activation_attempts + 1,
        ]);
    }

    public function markAsReplaced($replacementLicenseId)
    {
        $this->update([
            'status' => 'replaced',
            'replaced_by' => $replacementLicenseId,
            'replaced_at' => now(),
        ]);
    }
}