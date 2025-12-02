<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category',
        'image',
        'is_active',
        'stock_type',
        'available_stock',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function licensePools()
    {
        return $this->hasMany(LicensePool::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getIsInStockAttribute()
    {
        if ($this->stock_type === 1) {
            return true;
        }
        
        return $this->available_stock > 0;
    }
}