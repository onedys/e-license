<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\TripayPayment;
use Illuminate\Support\Facades\Log;

class PaymentProcessor
{
    protected $tripayService;
    protected $licenseAssigner;

    public function __construct(TripayService $tripayService, LicenseAssigner $licenseAssigner)
    {
        $this->tripayService = $tripayService;
        $this->licenseAssigner = $licenseAssigner;
    }

    public function processPaidOrder(Order $order): void
    {
        try {
            $assignedLicenses = $this->licenseAssigner->assignLicensesToOrder($order);
            
            Log::info('Payment processed successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'licenses_assigned' => count($assignedLicenses),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process paid order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    public function handleTripayCallback(array $callbackData): bool
    {
        if (!$this->tripayService->validateCallbackSignature($callbackData)) {
            Log::error('Invalid Tripay callback signature', $callbackData);
            return false;
        }
        
        $merchantRef = $callbackData['merchant_ref'];
        $status = strtolower($callbackData['status']);
        
        $order = Order::where('tripay_merchant_ref', $merchantRef)->first();
        
        if (!$order) {
            Log::error('Order not found for callback', ['merchant_ref' => $merchantRef]);
            return false;
        }
        
        $payment = TripayPayment::updateOrCreate(
            ['reference' => $callbackData['reference']],
            [
                'order_id' => $order->id,
                'merchant_ref' => $merchantRef,
                'payment_method' => $callbackData['payment_method'] ?? 'unknown',
                'amount' => $callbackData['amount'],
                'paid_amount' => $callbackData['amount_received'] ?? $callbackData['amount'],
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
                'callback_response' => $callbackData,
            ]
        );
        
        if ($status === 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
            
            $this->processPaidOrder($order);
            
        } elseif (in_array($status, ['failed', 'expired'])) {
            $order->update([
                'payment_status' => $status,
            ]);
        }
        
        Log::info('Tripay callback processed', [
            'order_id' => $order->id,
            'status' => $status,
            'merchant_ref' => $merchantRef,
        ]);
        
        return true;
    }
}