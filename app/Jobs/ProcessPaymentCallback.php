<?php

namespace App\Jobs;

use App\Services\Payment\PaymentProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180, 300];
    public $timeout = 30;

    protected $callbackData;

    public function __construct(array $callbackData)
    {
        $this->callbackData = $callbackData;
    }

    public function handle(PaymentProcessor $paymentProcessor): void
    {
        try {
            $success = $paymentProcessor->handleTripayCallback($this->callbackData);
            
            if (!$success) {
                Log::error('Payment callback processing failed', $this->callbackData);
                $this->fail(new \Exception('Payment callback processing failed'));
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing payment callback job', [
                'error' => $e->getMessage(),
                'callback_data' => $this->callbackData,
            ]);
            
            $this->fail($e);
        }
    }
}