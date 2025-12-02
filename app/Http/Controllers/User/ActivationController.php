<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserLicense;
use App\Models\Order;
use App\Models\ActivationLog;
use App\Models\InstallationIdTracking;
use App\Services\License\CidService;
use App\Services\Notification\DashboardNotification;
use App\Services\Security\FraudDetection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivationController extends Controller
{
    protected $cidService;
    protected $notificationService;
    protected $fraudDetection;

    public function __construct(CidService $cidService, DashboardNotification $notificationService, FraudDetection $fraudDetection)
    {
        $this->cidService = $cidService;
        $this->notificationService = $notificationService;
        $this->fraudDetection = $fraudDetection;
    }

    public function showForm()
    {
        $user = Auth::user();
        
        $pendingLicenses = $user->licenses()
            ->pending()
            ->with('order')
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'license_key' => $license->license_key_formatted,
                    'order_number' => $license->order->order_number,
                    'product_name' => $license->order->product->name,
                ];
            });
        
        return view('user.activation.index', compact('pendingLicenses'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'license_key' => 'required|string',
            'installation_id' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        $order = Order::where('order_number', $request->order_number)
                     ->where('user_id', $user->id)
                     ->first();
        
        if (!$order) {
            return back()->withErrors([
                'order_number' => 'Order tidak ditemukan atau bukan milik Anda.',
            ]);
        }
        
        $license = UserLicense::where('license_key', encrypt($request->license_key))
                             ->where('order_id', $order->id)
                             ->where('user_id', $user->id)
                             ->first();
        
        if (!$license) {
            return back()->withErrors([
                'license_key' => 'Lisensi tidak ditemukan atau tidak sesuai dengan order.',
            ]);
        }
        
        if ($license->status === 'active') {
            return back()->with('info', 'Lisensi ini sudah diaktivasi. CID: ' . $license->confirmation_id_formatted);
        }
        
        if ($license->status === 'blocked') {
            return back()->withErrors([
                'license_key' => 'Lisensi ini blocked. Silakan klaim garansi.',
            ]);
        }
        
        if ($license->status === 'replaced') {
            return back()->withErrors([
                'license_key' => 'Lisensi ini sudah diganti. Gunakan lisensi pengganti.',
            ]);
        }
        
        $normalizedInstallationId = $this->cidService->normalizeInstallationId($request->installation_id);
        
        if (!$this->cidService->isValidLength($normalizedInstallationId)) {
            return back()->withErrors([
                'installation_id' => 'Installation ID harus 54 atau 63 digit angka.',
            ]);
        }
        
        $installationIdHash = hash('sha256', $normalizedInstallationId);
        
        $existingUsage = InstallationIdTracking::where('installation_id_hash', $installationIdHash)
                                             ->where('user_id', $user->id)
                                             ->first();
        
        if ($existingUsage && $existingUsage->user_license_id !== $license->id) {
            $previousLicense = UserLicense::find($existingUsage->user_license_id);
            
            $errorMessage = "Installation ID ini sudah digunakan untuk lisensi lain: ";
            $errorMessage .= $previousLicense->license_key_formatted . " ";
            $errorMessage .= "pada " . $existingUsage->first_used_at->format('d-m-Y H:i');
            
            return back()->withErrors([
                'installation_id' => $errorMessage,
            ]);
        }
        
        $fraudCheck = $this->fraudDetection->checkActivationPattern($user, $normalizedInstallationId, $request->license_key);
        
        if ($fraudCheck['risk_level'] === 'high') {
            $this->fraudDetection->blockUserTemporarily(
                $user, 
                'Suspicious activation pattern detected',
                30
            );
            
            return back()->withErrors([
                'activation' => 'Aktivasi diblokir sementara karena aktivitas mencurigakan. Silakan coba lagi nanti atau hubungi admin.',
            ]);
        }
        
        if ($fraudCheck['risk_level'] === 'medium') {
            Log::warning('Medium risk activation detected', [
                'user_id' => $user->id,
                'checks' => $fraudCheck['checks'],
            ]);
        }
        
        $result = $this->cidService->generateCID($normalizedInstallationId);
        
        ActivationLog::create([
            'user_license_id' => $license->id,
            'installation_id' => encrypt($normalizedInstallationId),
            'api_response' => json_encode($result),
            'status' => $result['success'] ? 'success' : ($result['type'] === 'key_blocked' ? 'blocked' : 'error'),
        ]);
        
        if ($result['success']) {
            $license->markAsActivated(
                $result['confirmation_id'],
                $normalizedInstallationId
            );
            
            if (!$existingUsage) {
                InstallationIdTracking::create([
                    'user_id' => $user->id,
                    'installation_id_hash' => $installationIdHash,
                    'user_license_id' => $license->id,
                    'first_used_at' => now(),
                ]);
            }
            
            $this->notificationService->sendActivationSuccessNotification(
                $license, 
                $result['confirmation_id']
            );
            
            return redirect()->route('user.licenses.show', $license->id)
                ->with('success', 'Aktivasi berhasil! Confirmation ID: ' . $result['confirmation_id']);
            
        } elseif ($result['type'] === 'key_blocked') {
            $license->markAsBlocked();
            
            $this->notificationService->sendActivationBlockedNotification($license);
            
            return back()->withErrors([
                'activation' => 'Key blocked! Lisensi ini tidak valid. ' . 
                              ($license->is_warranty_valid ? 
                               'Anda bisa klaim garansi.' : 
                               'Masa garansi sudah habis.'),
            ]);
            
        } else {
            Log::error('Activation failed', [
                'license_id' => $license->id,
                'installation_id' => $normalizedInstallationId,
                'error' => $result['error'],
                'type' => $result['type'],
            ]);
            
            return back()->withErrors([
                'activation' => 'Aktivasi gagal: ' . $result['error'] . 
                              '. Silakan coba lagi. Kuota TIDAK berkurang.',
            ]);
        }
    }

    public function checkInstallationId(Request $request)
    {
        $request->validate([
            'installation_id' => 'required|string',
        ]);
        
        $user = Auth::user();
        $normalizedId = $this->cidService->normalizeInstallationId($request->installation_id);
        
        if (!$this->cidService->isValidLength($normalizedId)) {
            return response()->json([
                'valid' => false,
                'message' => 'Installation ID harus 54 atau 63 digit angka.',
            ]);
        }
        
        $installationIdHash = hash('sha256', $normalizedId);
        
        $existingUsage = InstallationIdTracking::where('installation_id_hash', $installationIdHash)
                                             ->where('user_id', $user->id)
                                             ->first();
        
        if ($existingUsage) {
            $license = UserLicense::find($existingUsage->user_license_id);
            
            return response()->json([
                'valid' => false,
                'message' => 'Installation ID ini sudah digunakan untuk lisensi: ' . 
                           $license->license_key_formatted . 
                           ' pada ' . $existingUsage->first_used_at->format('d-m-Y H:i'),
            ]);
        }
        
        return response()->json([
            'valid' => true,
            'message' => 'Installation ID tersedia.',
        ]);
    }
}