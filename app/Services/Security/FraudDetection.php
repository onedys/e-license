<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\ActivationLog;
use App\Models\InstallationIdTracking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FraudDetection
{
    private $highRiskThreshold = 5;
    private $mediumRiskThreshold = 3;
    private $suspiciousActivities = [];

    /**
     * Check for suspicious activation patterns
     */
    public function checkActivationPattern(User $user, string $installationId, string $licenseKey): array
    {
        $checks = [];
        $totalScore = 0;

        // Check 1: Too many activations in short time
        $recentActivations = ActivationLog::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        if ($recentActivations >= 10) {
            $checks[] = [
                'type' => 'rapid_activations',
                'message' => 'Too many activation attempts in last hour',
                'count' => $recentActivations,
                'score' => 3,
            ];
            $totalScore += 3;
        }

        // Check 2: Same installation ID used multiple times
        $installationIdHash = hash('sha256', $installationId);
        $sameInstallationCount = InstallationIdTracking::where('installation_id_hash', $installationIdHash)
            ->where('user_id', '!=', $user->id)
            ->count();

        if ($sameInstallationCount > 0) {
            $checks[] = [
                'type' => 'shared_installation_id',
                'message' => 'Installation ID used by multiple users',
                'count' => $sameInstallationCount,
                'score' => 5, // High risk
            ];
            $totalScore += 5;
        }

        // Check 3: Multiple failed activations
        $failedActivations = ActivationLog::where('user_id', $user->id)
            ->where('status', '!=', 'success')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();

        if ($failedActivations >= 5) {
            $checks[] = [
                'type' => 'multiple_failures',
                'message' => 'Multiple failed activation attempts in 24 hours',
                'count' => $failedActivations,
                'score' => 2,
            ];
            $totalScore += 2;
        }

        // Check 4: Geographic anomalies (if we have IP tracking)
        $ipAddress = request()->ip();
        $ipKey = 'activation_ip_' . $user->id;
        $lastIp = Cache::get($ipKey);
        
        if ($lastIp && $lastIp !== $ipAddress) {
            $checks[] = [
                'type' => 'ip_change',
                'message' => 'Activation from different IP address',
                'previous_ip' => $lastIp,
                'current_ip' => $ipAddress,
                'score' => 1,
            ];
            $totalScore += 1;
        }
        
        Cache::put($ipKey, $ipAddress, 3600); // Store for 1 hour

        // Check 5: License key pattern anomalies
        if ($this->isSuspiciousLicensePattern($licenseKey)) {
            $checks[] = [
                'type' => 'suspicious_license_pattern',
                'message' => 'License key pattern appears suspicious',
                'score' => 4,
            ];
            $totalScore += 4;
        }

        // Determine risk level
        $riskLevel = 'low';
        if ($totalScore >= $this->highRiskThreshold) {
            $riskLevel = 'high';
        } elseif ($totalScore >= $this->mediumRiskThreshold) {
            $riskLevel = 'medium';
        }

        // Log suspicious activity
        if ($riskLevel !== 'low') {
            $this->logSuspiciousActivity($user, $checks, $totalScore, $riskLevel);
        }

        return [
            'risk_level' => $riskLevel,
            'total_score' => $totalScore,
            'checks' => $checks,
            'recommendation' => $this->getRecommendation($riskLevel),
        ];
    }

    /**
     * Detect suspicious warranty claim patterns
     */
    public function checkWarrantyPattern(User $user, string $licenseKey): array
    {
        $checks = [];
        $totalScore = 0;

        // Check 1: Frequent warranty claims
        $recentClaims = $user->licenses()
            ->where('status', 'replaced')
            ->where('replaced_at', '>=', Carbon::now()->subDays(30))
            ->count();

        if ($recentClaims >= 3) {
            $checks[] = [
                'type' => 'frequent_claims',
                'message' => 'Multiple warranty claims in 30 days',
                'count' => $recentClaims,
                'score' => 4,
            ];
            $totalScore += 4;
        }

        // Check 2: Claims on recently activated licenses
        $license = $user->licenses()
            ->where('license_key', app(EncryptionService::class)->encrypt($licenseKey))
            ->first();

        if ($license && $license->activated_at) {
            $hoursSinceActivation = $license->activated_at->diffInHours(now());
            
            if ($hoursSinceActivation < 24) {
                $checks[] = [
                    'type' => 'recent_activation',
                    'message' => 'Warranty claim on recently activated license',
                    'hours_since_activation' => $hoursSinceActivation,
                    'score' => 3,
                ];
                $totalScore += 3;
            }
        }

        // Check 3: Pattern of claims on same product
        $productClaims = $user->licenses()
            ->whereHas('order.product', function ($query) use ($license) {
                if ($license && $license->order) {
                    $query->where('id', $license->order->product_id);
                }
            })
            ->where('status', 'replaced')
            ->count();

        if ($productClaims >= 2) {
            $checks[] = [
                'type' => 'same_product_claims',
                'message' => 'Multiple claims on same product type',
                'count' => $productClaims,
                'score' => 2,
            ];
            $totalScore += 2;
        }

        $riskLevel = $totalScore >= 3 ? 'medium' : 'low';

        if ($riskLevel !== 'low') {
            Log::warning('Suspicious warranty claim detected', [
                'user_id' => $user->id,
                'license_key' => app(EncryptionService::class)->maskSensitiveData($licenseKey),
                'risk_level' => $riskLevel,
                'checks' => $checks,
            ]);
        }

        return [
            'risk_level' => $riskLevel,
            'total_score' => $totalScore,
            'checks' => $checks,
            'recommendation' => $riskLevel === 'high' ? 'manual_review' : 'auto_approve',
        ];
    }

    /**
     * Check for suspicious license key patterns
     */
    private function isSuspiciousLicensePattern(string $licenseKey): bool
    {
        // Remove dashes for checking
        $cleanKey = str_replace('-', '', $licenseKey);
        
        // Check 1: Repeated patterns
        if (preg_match('/([A-Z0-9])\1{3,}/', $cleanKey)) {
            return true;
        }
        
        // Check 2: Sequential patterns
        $sequentialPatterns = ['ABCD', '1234', 'AAAA', '1111'];
        foreach ($sequentialPatterns as $pattern) {
            if (strpos($cleanKey, $pattern) !== false) {
                return true;
            }
        }
        
        // Check 3: Too many similar characters
        $charCounts = count_chars($cleanKey, 1);
        if (max($charCounts) > strlen($cleanKey) * 0.5) {
            return true;
        }
        
        return false;
    }

    /**
     * Log suspicious activity for review
     */
    private function logSuspiciousActivity(User $user, array $checks, int $score, string $riskLevel): void
    {
        $logData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'risk_level' => $riskLevel,
            'score' => $score,
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ];

        // Store in cache for real-time monitoring
        $key = 'fraud_alert_' . $user->id . '_' . time();
        Cache::put($key, $logData, 86400); // Store for 24 hours

        // Also log to database/file
        Log::warning('Suspicious activity detected', $logData);

        // Add to in-memory list for current request
        $this->suspiciousActivities[] = $logData;
    }

    /**
     * Get recommendation based on risk level
     */
    private function getRecommendation(string $riskLevel): string
    {
        return match($riskLevel) {
            'high' => 'block_and_review',
            'medium' => 'additional_verification',
            'low' => 'allow',
            default => 'allow',
        };
    }

    /**
     * Get recent suspicious activities
     */
    public function getRecentSuspiciousActivities(int $limit = 50): array
    {
        $pattern = 'fraud_alert_*';
        $keys = Cache::getStore() instanceof \Illuminate\Cache\RedisStore
            ? Cache::getRedis()->keys('*' . $pattern . '*')
            : [];

        $activities = [];
        foreach ($keys as $key) {
            $activity = Cache::get($key);
            if ($activity) {
                $activities[] = $activity;
            }
        }

        // Sort by timestamp descending
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Block user temporarily due to suspicious activity
     */
    public function blockUserTemporarily(User $user, string $reason, int $minutes = 60): void
    {
        $blockKey = 'user_blocked_' . $user->id;
        Cache::put($blockKey, [
            'reason' => $reason,
            'blocked_at' => now()->toISOString(),
            'expires_at' => now()->addMinutes($minutes)->toISOString(),
        ], $minutes * 60);

        Log::warning('User temporarily blocked', [
            'user_id' => $user->id,
            'username' => $user->username,
            'reason' => $reason,
            'block_duration' => $minutes . ' minutes',
        ]);
    }

    /**
     * Check if user is temporarily blocked
     */
    public function isUserBlocked(User $user): bool
    {
        $blockKey = 'user_blocked_' . $user->id;
        return Cache::has($blockKey);
    }

    /**
     * Get block information for user
     */
    public function getUserBlockInfo(User $user): ?array
    {
        $blockKey = 'user_blocked_' . $user->id;
        return Cache::get($blockKey);
    }
}