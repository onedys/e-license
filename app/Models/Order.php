<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_number',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_status',
        'payment_method',
        'tripay_reference',
        'tripay_merchant_ref',
        'paid_at',
        'warranty_until',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'warranty_until' => 'datetime',
        'metadata' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function licenses()
    {
        return $this->hasMany(UserLicense::class);
    }

    public function payment()
    {
        return $this->hasOne(TripayPayment::class, 'order_id');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    // Generate order number
    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . date('Ymd');
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . '-' . $newNumber;
    }

    // Check if warranty is still valid
    public function getIsWarrantyValidAttribute()
    {
        if (!$this->warranty_until) {
            return false;
        }
        
        return now()->lte($this->warranty_until);
    }
}