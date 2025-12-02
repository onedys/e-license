<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\LicensePool;
use App\Models\WarrantyExchange;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Log;

class AdminAlertService
{
    public function checkAndSendLowStockAlerts(): void
    {
        try {
            $lowStockProducts = LicensePool::select('product_id', 'product_name')
                ->selectRaw('COUNT(*) as stock_count')
                ->where('status', 'active')
                ->groupBy('product_id', 'product_name')
                ->having('stock_count', '<', 10)
                ->get();
            
            foreach ($lowStockProducts as $product) {
                $dashboardNotification = new DashboardNotification();
                $dashboardNotification->sendLowStockNotification(
                    $product->product_id,
                    $product->product_name,
                    $product->stock_count
                );
            }
            
            Log::info('Low stock alerts checked', [
                'low_stock_count' => $lowStockProducts->count(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to check low stock alerts', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendPendingWarrantyAlert(): void
    {
        try {
            $pendingCount = WarrantyExchange::where('auto_approved', false)
                ->whereNull('approved_at')
                ->count();
            
            if ($pendingCount > 0) {
                $admins = User::where('is_admin', true)->get();
                
                foreach ($admins as $admin) {
                    NotificationModel::create([
                        'user_id' => $admin->id,
                        'type' => 'pending_warranty',
                        'data' => [
                            'title' => 'Klaim Garansi Pending',
                            'message' => "Ada {$pendingCount} klaim garansi yang membutuhkan approval manual",
                            'pending_count' => $pendingCount,
                            'action_url' => route('admin.warranty.pending'),
                        ],
                    ]);
                }
                
                Log::info('Pending warranty alert sent', [
                    'pending_count' => $pendingCount,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send pending warranty alert', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendSystemHealthAlert(string $issue, string $details): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            foreach ($admins as $admin) {
                NotificationModel::create([
                    'user_id' => $admin->id,
                    'type' => 'system_health',
                    'data' => [
                        'title' => 'System Health Alert: ' . $issue,
                        'message' => $details,
                        'issue' => $issue,
                        'details' => $details,
                        'action_url' => route('admin.system.info'),
                    ],
                ]);
            }
            
            Log::warning('System health alert sent', [
                'issue' => $issue,
                'details' => $details,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send system health alert', [
                'error' => $e->getMessage(),
                'issue' => $issue,
            ]);
        }
    }

    public function sendApiFailureAlert(string $apiName, string $error): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            foreach ($admins as $admin) {
                NotificationModel::create([
                    'user_id' => $admin->id,
                    'type' => 'api_failure',
                    'data' => [
                        'title' => "API Failure: {$apiName}",
                        'message' => "API {$apiName} mengalami error: {$error}",
                        'api_name' => $apiName,
                        'error' => $error,
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                    ],
                ]);
            }
            
            Log::error('API failure alert sent', [
                'api_name' => $apiName,
                'error' => $error,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send API failure alert', [
                'error' => $e->getMessage(),
                'api_name' => $apiName,
            ]);
        }
    }

    public function sendSuspiciousActivityAlert(
        int $userId, 
        string $username, 
        string $activity, 
        string $details
    ): void {
        try {
            $admins = User::where('is_admin', true)->get();
            
            foreach ($admins as $admin) {
                NotificationModel::create([
                    'user_id' => $admin->id,
                    'type' => 'suspicious_activity',
                    'data' => [
                        'title' => 'Aktivitas Mencurigakan Terdeteksi',
                        'message' => "User {$username} melakukan: {$activity}",
                        'user_id' => $userId,
                        'username' => $username,
                        'activity' => $activity,
                        'details' => $details,
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                        'action_url' => route('admin.users.show', $userId),
                    ],
                ]);
            }
            
            Log::warning('Suspicious activity alert sent', [
                'user_id' => $userId,
                'username' => $username,
                'activity' => $activity,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send suspicious activity alert', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }
}