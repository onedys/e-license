<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripayPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reference',
        'merchant_ref',
        'payment_method',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'callback_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'callback_response' => 'array',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}