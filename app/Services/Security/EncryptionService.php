<?php

namespace App\Services\Security;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptionService
{
    /**
     * Encrypt sensitive data
     */
    public function encrypt(string $data): string
    {
        try {
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            Log::error('Encryption failed', [
                'error' => $e->getMessage(),
                'data_length' => strlen($data),
            ]);
            throw new \RuntimeException('Failed to encrypt data: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): ?string
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (DecryptException $e) {
            Log::warning('Decryption failed', [
                'error' => $e->getMessage(),
                'data' => substr($encryptedData, 0, 50) . '...',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Decryption error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate a secure random string
     */
    public function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash data with salt
     */
    public function hashWithSalt(string $data, string $salt = null): array
    {
        $salt = $salt ?? $this->generateRandomString(16);
        $hash = hash('sha256', $data . $salt);
        
        return [
            'hash' => $hash,
            'salt' => $salt,
        ];
    }

    /**
     * Verify hashed data
     */
    public function verifyHash(string $data, string $hash, string $salt): bool
    {
        $newHash = hash('sha256', $data . $salt);
        return hash_equals($hash, $newHash);
    }

    /**
     * Encrypt license key with additional metadata
     */
    public function encryptLicenseKey(string $licenseKey, array $metadata = []): array
    {
        $encryptedKey = $this->encrypt($licenseKey);
        $checksum = hash('sha256', $licenseKey);
        $timestamp = now()->timestamp;
        
        return [
            'encrypted_key' => $encryptedKey,
            'checksum' => $checksum,
            'timestamp' => $timestamp,
            'metadata' => $metadata,
        ];
    }

    /**
     * Decrypt and verify license key
     */
    public function decryptLicenseKey(array $encryptedData): ?array
    {
        if (!isset($encryptedData['encrypted_key'], $encryptedData['checksum'])) {
            Log::warning('Invalid encrypted data structure');
            return null;
        }

        $licenseKey = $this->decrypt($encryptedData['encrypted_key']);
        
        if (!$licenseKey) {
            Log::warning('Failed to decrypt license key');
            return null;
        }

        // Verify checksum
        $calculatedChecksum = hash('sha256', $licenseKey);
        
        if (!hash_equals($calculatedChecksum, $encryptedData['checksum'])) {
            Log::warning('License key checksum mismatch', [
                'expected' => $encryptedData['checksum'],
                'actual' => $calculatedChecksum,
            ]);
            return null;
        }

        return [
            'license_key' => $licenseKey,
            'timestamp' => $encryptedData['timestamp'] ?? null,
            'metadata' => $encryptedData['metadata'] ?? [],
        ];
    }

    /**
     * Mask sensitive data for logging
     */
    public function maskSensitiveData(string $data, int $visibleChars = 4): string
    {
        $length = strlen($data);
        if ($length <= $visibleChars * 2) {
            return str_repeat('*', $length);
        }
        
        $firstPart = substr($data, 0, $visibleChars);
        $lastPart = substr($data, -$visibleChars);
        
        return $firstPart . str_repeat('*', $length - ($visibleChars * 2)) . $lastPart;
    }
}