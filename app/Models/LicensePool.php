<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\EncryptsAttributes;

class LicensePool extends Model
{
    use HasFactory, SoftDeletes, EncryptsAttributes;

    protected $fillable = [
        'product_id',
        'license_key',
        'keyname_with_dash',
        'errorcode',
        'product_name',
        'is_retail',
        'remaining',
        'blocked',
        'status',
        'validated_at',
        'last_validated_at',
        'validation_count',
    ];

    protected $casts = [
        'is_retail' => 'boolean',
        'validated_at' => 'datetime',
        'last_validated_at' => 'datetime',
    ];

    // Atribut yang akan dienkripsi
    protected $encryptedAttributes = [
        'license_key',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function userLicenses()
    {
        return $this->hasMany(UserLicense::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
                    ->whereDoesntHave('userLicenses', function ($q) {
                        $q->whereIn('status', ['active', 'pending']);
                    });
    }

    // Accessor untuk license key tanpa dash
    public function getKeynameWithoutDashAttribute()
    {
        return str_replace('-', '', $this->keyname_with_dash);
    }

    // Check if key is valid for sale
    public function getIsValidForSaleAttribute()
    {
        return $this->status === 'active' && 
               in_array($this->errorcode, ['0xC004C008', 'Online Key']);
    }

    // Mark as validated
    public function markAsValidated($errorcode)
    {
        $this->update([
            'errorcode' => $errorcode,
            'validated_at' => now(),
            'last_validated_at' => now(),
            'validation_count' => $this->validation_count + 1,
            'status' => in_array($errorcode, ['0xC004C008', 'Online Key']) ? 'active' : 'blocked',
        ]);
    }
}