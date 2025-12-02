<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CidService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('pidkey.api_key');
        $this->baseUrl = config('pidkey.base_url', 'https://pidkey.com/ajax');
    }

    public function generateCID(string $installationId): array
    {
        try {
            $url = $this->baseUrl . '/cidms_api';
            
            $response = Http::timeout(30)->get($url, [
                'iids' => $installationId,
                'justforcheck' => 0,
                'apikey' => $this->apiKey,
            ]);
            
            $data = $response->json();
            
            if (!is_array($data)) {
                Log::error('GetCID API returned invalid response', [
                    'installation_id' => $installationId,
                    'response' => $data,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid API response format',
                    'type' => 'api_error',
                ];
            }
            
            if (isset($data['short_result']) && str_contains($data['short_result'], 'Confirmation ID')) {
                return [
                    'success' => true,
                    'confirmation_id' => $data['confirmation_id_with_dash'] ?? '',
                    'confirmation_id_no_dash' => $data['confirmation_id_no_dash'] ?? '',
                    'short_result' => $data['short_result'],
                    'result' => $data['result'],
                    'have_cid' => $data['have_cid'] ?? 0,
                    'raw_response' => $data,
                ];
            }
            
            if (isset($data['short_result']) && $data['short_result'] === 'Key blocked!') {
                return [
                    'success' => false,
                    'error' => 'Key blocked!',
                    'type' => 'key_blocked',
                    'result' => $data['result'] ?? '',
                    'have_cid' => $data['have_cid'] ?? -1,
                    'raw_response' => $data,
                ];
            }
            
            return [
                'success' => false,
                'error' => $data['short_result'] ?? $data['result'] ?? 'Unknown error',
                'type' => 'other_error',
                'raw_response' => $data,
            ];
            
        } catch (\Exception $e) {
            Log::error('GetCID API failed', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'network_error',
            ];
        }
    }

    public function validateInstallationId(string $installationId): bool
    {
        return preg_match('/^\d{54}$|^\d{63}$/', $installationId) === 1;
    }

    public function normalizeInstallationId(string $installationId): string
    {
        return preg_replace('/[^0-9]/', '', $installationId);
    }

    public function isValidLength(string $installationId): bool
    {
        $normalized = $this->normalizeInstallationId($installationId);
        $length = strlen($normalized);
        
        return $length === 54 || $length === 63;
    }
}