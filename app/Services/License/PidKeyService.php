<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PidKeyService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('pidkey.api_key');
        $this->baseUrl = config('pidkey.base_url', 'https://pidkey.com/ajax');
    }

    public function validateKey(string|array $keys): array
    {
        if (is_array($keys)) {
            return $this->validateMultipleKeys($keys);
        }
        
        return $this->validateSingleKey($keys);
    }

    private function validateSingleKey(string $key): array
    {
        try {
            $url = $this->baseUrl . '/pidms_api';
            
            $response = Http::timeout(30)->get($url, [
                'keys' => $key,
                'justgetdescription' => 0,
                'apikey' => $this->apiKey,
            ]);
            
            $data = $response->json();
            
            if (empty($data)) {
                Log::error('PIDKey API returned empty response', ['key' => $key]);
                return [
                    'is_valid' => false,
                    'errorcode' => 'API_ERROR',
                    'message' => 'Empty response from validation API',
                ];
            }
            
            $result = $data[0] ?? [];
            
            return [
                'is_valid' => $this->isValidErrorCode($result['errorcode'] ?? ''),
                'errorcode' => $result['errorcode'] ?? 'UNKNOWN',
                'product_name' => $result['prd'] ?? null,
                'is_retail' => $result['is_retail'] ?? 0,
                'blocked' => $result['blocked'] ?? 0,
                'remaining' => $result['remaining'] ?? null,
                'raw_response' => $result,
            ];
            
        } catch (\Exception $e) {
            Log::error('PIDKey API validation failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'is_valid' => false,
                'errorcode' => 'NETWORK_ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function validateMultipleKeys(array $keys): array
    {
        try {
            $url = $this->baseUrl . '/pidms_api';
            
            $keysString = implode("\r\n", $keys);
            
            $response = Http::timeout(60)->get($url, [
                'keys' => $keysString,
                'justgetdescription' => 0,
                'apikey' => $this->apiKey,
            ]);
            
            $data = $response->json();
            
            if (!is_array($data)) {
                Log::error('PIDKey API returned invalid response for multiple keys');
                return [
                    'is_valid' => false,
                    'errorcode' => 'API_ERROR',
                    'message' => 'Invalid response format',
                    'results' => [],
                ];
            }
            
            $results = [];
            foreach ($data as $item) {
                $results[] = [
                    'key' => $item['keyname_with_dash'] ?? '',
                    'is_valid' => $this->isValidErrorCode($item['errorcode'] ?? ''),
                    'errorcode' => $item['errorcode'] ?? 'UNKNOWN',
                    'product_name' => $item['prd'] ?? null,
                    'is_retail' => $item['is_retail'] ?? 0,
                    'blocked' => $item['blocked'] ?? 0,
                    'remaining' => $item['remaining'] ?? null,
                ];
            }
            
            return [
                'is_valid' => true,
                'results' => $results,
            ];
            
        } catch (\Exception $e) {
            Log::error('PIDKey API multiple validation failed', [
                'error' => $e->getMessage(),
                'key_count' => count($keys),
            ]);
            
            return [
                'is_valid' => false,
                'errorcode' => 'NETWORK_ERROR',
                'message' => $e->getMessage(),
                'results' => [],
            ];
        }
    }

    private function isValidErrorCode(string $errorcode): bool
    {
        $validCodes = [
            '0xC004C008',
            'Online Key',
        ];
        
        return in_array($errorcode, $validCodes);
    }

    public function bulkValidateFromText(string $text): array
    {
        $lines = explode("\n", $text);
        $keys = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $keys[] = $line;
            }
        }
        
        if (empty($keys)) {
            return [
                'success' => false,
                'message' => 'No keys found in text',
                'valid_keys' => [],
                'invalid_keys' => [],
            ];
        }
        
        $chunks = array_chunk($keys, 10);
        $allResults = [];
        
        foreach ($chunks as $chunk) {
            $result = $this->validateMultipleKeys($chunk);
            
            if (!$result['is_valid']) {
                Log::warning('Bulk validation chunk failed', [
                    'chunk' => $chunk,
                    'error' => $result['message'] ?? 'Unknown error',
                ]);
                continue;
            }
            
            $allResults = array_merge($allResults, $result['results'] ?? []);
        }
        
        $validKeys = [];
        $invalidKeys = [];
        
        foreach ($allResults as $result) {
            if ($result['is_valid']) {
                $validKeys[] = [
                    'key' => $result['key'],
                    'product_name' => $result['product_name'],
                    'errorcode' => $result['errorcode'],
                ];
            } else {
                $invalidKeys[] = [
                    'key' => $result['key'],
                    'errorcode' => $result['errorcode'],
                ];
            }
        }
        
        return [
            'success' => true,
            'total_keys' => count($keys),
            'valid_count' => count($validKeys),
            'invalid_count' => count($invalidKeys),
            'valid_keys' => $validKeys,
            'invalid_keys' => $invalidKeys,
        ];
    }
}