<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    private $apiKey;
    private $privateKey;
    private $merchantCode;
    private $mode;

    public function __construct()
    {
        $this->mode = config('tripay.mode', 'sandbox');
        
        $apiKey = config("tripay.{$this->mode}.api_key");
        $privateKey = config("tripay.{$this->mode}.private_key");
        $merchantCode = config("tripay.{$this->mode}.merchant_code");
        
        $this->apiKey = $apiKey ?? config('tripay.api_key') ?? env('TRIPAY_API_KEY');
        $this->privateKey = $privateKey ?? config('tripay.private_key') ?? env('TRIPAY_PRIVATE_KEY');
        $this->merchantCode = $merchantCode ?? config('tripay.merchant_code') ?? env('TRIPAY_MERCHANT_CODE');
    }

    private function getBaseUrl(): string
    {
        return $this->mode === 'production' 
            ? 'https://tripay.co.id/api/' 
            : 'https://tripay.co.id/api-sandbox/';
    }

    public function createTransaction(array $data): array
    {
        try {
            $url = $this->getBaseUrl() . 'transaction/create';
            
            $signature = hash_hmac('sha256', 
                $this->merchantCode . $data['merchant_ref'] . $data['amount'], 
                $this->privateKey
            );

            $payload = array_merge($data, [
                'signature' => $signature,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($url, $payload);

            $result = $response->json();

            if (!$result['success']) {
                Log::error('Tripay create transaction failed', [
                    'error' => $result['message'],
                    'payload' => $payload
                ]);
                
                throw new \Exception('Payment gateway error: ' . $result['message']);
            }

            return $result['data'];

        } catch (\Exception $e) {
            Log::error('Tripay service error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            throw $e;
        }
    }

    public function getTransactionDetail(string $reference): array
    {
        try {
            $url = $this->getBaseUrl() . 'transaction/detail?reference=' . $reference;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($url);

            $result = $response->json();

            if (!$result['success']) {
                throw new \Exception('Failed to get transaction detail: ' . $result['message']);
            }

            return $result['data'];

        } catch (\Exception $e) {
            Log::error('Tripay get detail error', [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);
            
            throw $e;
        }
    }

    public function validateCallbackSignature(array $callbackData): bool
    {
        $signature = $callbackData['signature'] ?? '';
        
        $localSignature = hash_hmac('sha256', 
            $this->merchantCode . 
            $callbackData['merchant_ref'] . 
            $callbackData['amount'], 
            $this->privateKey
        );

        return hash_equals($localSignature, $signature);
    }

    public function getPaymentChannels(): array
    {
        try {
            $url = $this->getBaseUrl() . 'merchant/payment-channel';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($url);

            $result = $response->json();

            if (!$result['success']) {
                throw new \Exception('Failed to get payment channels');
            }

            return $result['data'];

        } catch (\Exception $e) {
            Log::error('Tripay get channels error', ['error' => $e->getMessage()]);
            return [];
        }
    }
}