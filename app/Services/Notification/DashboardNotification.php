<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Order;
use App\Models\UserLicense;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Log;

class DashboardNotification
{
    public function sendLicenseAssignedNotification(User $user, Order $order, array $assignedLicenses): void
    {
        try {
            $licenseCount = count($assignedLicenses);
            $firstLicense = $assignedLicenses[0] ?? null;
            
            $message = $licenseCount > 1 
                ? "{$licenseCount} lisensi telah ditambahkan ke akun Anda dari Order #{$order->order_number}"
                : "Lisensi telah ditambahkan ke akun Anda dari Order #{$order->order_number}";
            
            NotificationModel::create([
                'user_id' => $user->id,
                'type' => 'license_assigned',
                'data' => [
                    'title' => 'Lisensi Baru Tersedia',
                    'message' => $message,
                    'order_number' => $order->order_number,
                    'product_name' => $order->product->name,
                    'license_count' => $licenseCount,
                    'license_keys' => array_map(function($license) {
                        return $license->getPlainAttribute('license_key');
                    }, $assignedLicenses),
                    'action_url' => route('user.licenses.index'),
                ],
            ]);
            
            Log::info('License assigned notification sent', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'license_count' => $licenseCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send license assigned notification', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendActivationSuccessNotification(UserLicense $userLicense, string $confirmationId): void
    {
        try {
            NotificationModel::create([
                'user_id' => $userLicense->user_id,
                'type' => 'activation_success',
                'data' => [
                    'title' => 'Aktivasi Berhasil',
                    'message' => "Lisensi {$userLicense->getPlainAttribute('license_key')} berhasil diaktivasi",
                    'confirmation_id' => $confirmationId,
                    'license_key' => $userLicense->getPlainAttribute('license_key'),
                    'product_name' => $userLicense->order->product->name,
                    'action_url' => route('user.licenses.show', $userLicense->id),
                ],
            ]);
            
            Log::info('Activation success notification sent', [
                'user_license_id' => $userLicense->id,
                'user_id' => $userLicense->user_id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send activation success notification', [
                'user_license_id' => $userLicense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendActivationBlockedNotification(UserLicense $userLicense): void
    {
        try {
            NotificationModel::create([
                'user_id' => $userLicense->user_id,
                'type' => 'activation_blocked',
                'data' => [
                    'title' => 'Aktivasi Gagal - Key Blocked',
                    'message' => "Lisensi {$userLicense->getPlainAttribute('license_key')} blocked. Anda bisa klaim garansi dalam 7 hari.",
                    'license_key' => $userLicense->getPlainAttribute('license_key'),
                    'product_name' => $userLicense->order->product->name,
                    'warranty_until' => $userLicense->warranty_until->format('d F Y'),
                    'action_url' => route('user.warranty.claim.form'),
                ],
            ]);
            
            Log::info('Activation blocked notification sent', [
                'user_license_id' => $userLicense->id,
                'user_id' => $userLicense->user_id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send activation blocked notification', [
                'user_license_id' => $userLicense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendWarrantyApprovedNotification(
        UserLicense $originalLicense, 
        UserLicense $replacementLicense
    ): void {
        try {
            NotificationModel::create([
                'user_id' => $originalLicense->user_id,
                'type' => 'warranty_approved',
                'data' => [
                    'title' => 'Klaim Garansi Disetujui',
                    'message' => "Lisensi {$originalLicense->getPlainAttribute('license_key')} telah diganti dengan {$replacementLicense->getPlainAttribute('license_key')}",
                    'original_license' => $originalLicense->getPlainAttribute('license_key'),
                    'replacement_license' => $replacementLicense->getPlainAttribute('license_key'),
                    'product_name' => $originalLicense->order->product->name,
                    'action_url' => route('user.licenses.show', $replacementLicense->id),
                ],
            ]);
            
            Log::info('Warranty approved notification sent', [
                'original_license_id' => $originalLicense->id,
                'replacement_license_id' => $replacementLicense->id,
                'user_id' => $originalLicense->user_id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send warranty approved notification', [
                'original_license_id' => $originalLicense->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendPaymentSuccessNotification(Order $order): void
    {
        try {
            NotificationModel::create([
                'user_id' => $order->user_id,
                'type' => 'payment_success',
                'data' => [
                    'title' => 'Pembayaran Berhasil',
                    'message' => "Pembayaran untuk Order #{$order->order_number} berhasil. Lisensi akan segera dikirim.",
                    'order_number' => $order->order_number,
                    'amount' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                    'product_name' => $order->product->name,
                    'action_url' => route('user.orders.show', $order->order_number),
                ],
            ]);
            
            Log::info('Payment success notification sent', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment success notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendPaymentFailedNotification(Order $order, string $reason): void
    {
        try {
            NotificationModel::create([
                'user_id' => $order->user_id,
                'type' => 'payment_failed',
                'data' => [
                    'title' => 'Pembayaran Gagal',
                    'message' => "Pembayaran untuk Order #{$order->order_number} gagal: {$reason}",
                    'order_number' => $order->order_number,
                    'reason' => $reason,
                    'action_url' => route('checkout.index'),
                ],
            ]);
            
            Log::info('Payment failed notification sent', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'reason' => $reason,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment failed notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendLowStockNotification(int $productId, string $productName, int $remainingStock): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            foreach ($admins as $admin) {
                NotificationModel::create([
                    'user_id' => $admin->id,
                    'type' => 'low_stock',
                    'data' => [
                        'title' => 'Stok Lisensi Menipis',
                        'message' => "Produk {$productName} hanya memiliki {$remainingStock} lisensi tersisa di pool",
                        'product_name' => $productName,
                        'remaining_stock' => $remainingStock,
                        'action_url' => route('admin.license-pool.create'),
                    ],
                ]);
            }
            
            Log::info('Low stock notification sent to admins', [
                'product_id' => $productId,
                'product_name' => $productName,
                'remaining_stock' => $remainingStock,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send low stock notification', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendNewOrderNotification(Order $order): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            foreach ($admins as $admin) {
                NotificationModel::create([
                    'user_id' => $admin->id,
                    'type' => 'new_order',
                    'data' => [
                        'title' => 'Order Baru',
                        'message' => "Order #{$order->order_number} dari {$order->user->name}",
                        'order_number' => $order->order_number,
                        'customer_name' => $order->user->name,
                        'amount' => 'Rp ' . number_format($order->total_amount, 0, ',', '.'),
                        'product_name' => $order->product->name,
                        'action_url' => route('admin.orders.show', $order->id),
                    ],
                ]);
            }
            
            Log::info('New order notification sent to admins', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send new order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function markAsRead(int $notificationId): bool
    {
        try {
            $notification = NotificationModel::find($notificationId);
            
            if ($notification && !$notification->read_at) {
                $notification->update(['read_at' => now()]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function markAllAsRead(int $userId): bool
    {
        try {
            NotificationModel::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getUnreadCount(int $userId): int
    {
        return NotificationModel::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function getRecentNotifications(int $userId, int $limit = 10): array
    {
        $notifications = NotificationModel::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'action_url' => $notification->data['action_url'] ?? null,
                ];
            })
            ->toArray();
        
        return $notifications;
    }
}