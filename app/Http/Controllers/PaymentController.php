<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payment\TripayService;
use App\Services\License\LicenseAssigner;
use App\Services\Notification\DashboardNotification;
use App\Services\Notification\AdminAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $tripayService;
    protected $licenseAssigner;
    protected $notificationService;

    public function __construct(
        TripayService $tripayService, 
        LicenseAssigner $licenseAssigner,
        DashboardNotification $notificationService
    ) {
        $this->tripayService = $tripayService;
        $this->licenseAssigner = $licenseAssigner;
        $this->notificationService = $notificationService;
    }

    public function redirect($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
                     ->where('user_id', Auth::id())
                     ->where('payment_status', 'pending')
                     ->firstOrFail();
        
        $transactionData = session()->get('pending_order_' . $orderNumber);
        
        if (!$transactionData) {
            try {
                $tripayData = $this->tripayService->getTransactionDetail($order->tripay_reference);
                $transactionData = [
                    'reference' => $tripayData['reference'],
                    'checkout_url' => $tripayData['checkout_url'],
                ];
            } catch (\Exception $e) {
                return redirect()->route('dashboard')
                    ->with('error', 'Tidak dapat mengakses halaman pembayaran.');
            }
        }
        
        return view('payment.redirect', [
            'order' => $order,
            'checkoutUrl' => $transactionData['checkout_url'],
        ]);
    }

    public function callback(Request $request)
    {
        \Log::info('Tripay Callback Received:', $request->all());
        
        $isValid = $this->tripayService->validateCallbackSignature($request->all());
        
        if (!$isValid) {
            \Log::error('Invalid Tripay callback signature', $request->all());
            return response()->json(['error' => 'Invalid signature'], 403);
        }
        
        $merchantRef = $request->merchant_ref;
        $status = strtolower($request->status);
        
        $order = Order::where('tripay_merchant_ref', $merchantRef)->first();
        
        if (!$order) {
            \Log::error('Order not found for callback', ['merchant_ref' => $merchantRef]);
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        if ($status === 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
            
            try {
                $assignedLicenses = $this->licenseAssigner->assignLicensesToOrder($order);
                
                if (!empty($assignedLicenses)) {
                    $this->notificationService->sendLicenseAssignedNotification(
                        $order->user,
                        $order,
                        $assignedLicenses
                    );
                    
                    $this->notificationService->sendPaymentSuccessNotification($order);
                    
                    $adminAlertService = new AdminAlertService();
                    $adminAlertService->sendNewOrderNotification($order);
                }
                
                \Log::info('Licenses assigned successfully', [
                    'order_id' => $order->id,
                    'license_count' => count($assignedLicenses),
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Failed to assign licenses', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            \Log::info('Order marked as paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
            
        } elseif (in_array($status, ['failed', 'expired'])) {
            $order->update([
                'payment_status' => $status,
            ]);
            
            \Log::info('Order marked as ' . $status, [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    public function checkStatus($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
                     ->where('user_id', Auth::id())
                     ->firstOrFail();
        
        try {
            $tripayData = $this->tripayService->getTransactionDetail($order->tripay_reference);
            
            if ($order->payment_status !== $tripayData['status']) {
                $order->update([
                    'payment_status' => $tripayData['status'],
                    'paid_at' => $tripayData['status'] === 'paid' ? now() : null,
                ]);
            }
            
            return response()->json([
                'status' => $order->payment_status,
                'tripay_status' => $tripayData['status'],
                'paid_at' => $order->paid_at,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check payment status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}