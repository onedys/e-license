<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicensePool;
use App\Models\Product;
use App\Services\License\PidKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicensePoolController extends Controller
{
    protected $pidKeyService;

    public function __construct(PidKeyService $pidKeyService)
    {
        $this->pidKeyService = $pidKeyService;
    }

    public function index(Request $request)
    {
        $query = LicensePool::with('product');
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keyname_with_dash', 'LIKE', "%{$search}%")
                  ->orWhere('errorcode', 'LIKE', "%{$search}%")
                  ->orWhere('product_name', 'LIKE', "%{$search}%");
            });
        }
        
        $licensePools = $query->latest()->paginate(50);
        $products = Product::active()->get();
        
        $stats = [
            'total' => LicensePool::count(),
            'active' => LicensePool::where('status', 'active')->count(),
            'blocked' => LicensePool::where('status', 'blocked')->count(),
            'invalid' => LicensePool::where('status', 'invalid')->count(),
            'exhausted' => LicensePool::where('status', 'exhausted')->count(),
        ];
        
        return view('admin.licenses.pool.index', compact('licensePools', 'products', 'stats'));
    }

    public function create()
    {
        $products = Product::active()->get();
        return view('admin.licenses.pool.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'keys' => 'required|string|min:10',
        ]);
        
        $keysText = trim($request->keys);
        $keys = array_filter(array_map('trim', explode("\n", $keysText)));
        
        if (empty($keys)) {
            return back()->withErrors(['keys' => 'Tidak ada lisensi yang valid ditemukan.'])->withInput();
        }
        
        if (count($keys) > 100) {
            return back()->withErrors(['keys' => 'Maksimal 100 lisensi per batch.'])->withInput();
        }
        
        $validationResult = $this->pidKeyService->bulkValidateFromText($keysText);
        
        if (!$validationResult['success']) {
            return back()->withErrors(['keys' => 'Validasi gagal: ' . $validationResult['message']])->withInput();
        }
        
        $validKeys = $validationResult['valid_keys'];
        $invalidKeys = $validationResult['invalid_keys'];
        
        if (empty($validKeys)) {
            return back()->withErrors(['keys' => 'Tidak ada lisensi yang valid. Semua lisensi gagal validasi.'])->withInput();
        }
        
        $savedCount = 0;
        $existingCount = 0;
        
        foreach ($validKeys as $keyData) {
            $existing = LicensePool::where('keyname_with_dash', $keyData['key'])->exists();
            
            if ($existing) {
                $existingCount++;
                continue;
            }
            
            LicensePool::create([
                'product_id' => $request->product_id,
                'license_key' => $keyData['key'],
                'keyname_with_dash' => $keyData['key'],
                'errorcode' => $keyData['errorcode'],
                'product_name' => $keyData['product_name'] ?? 'Unknown',
                'status' => 'active',
                'validated_at' => now(),
                'last_validated_at' => now(),
            ]);
            
            $savedCount++;
        }
        
        Log::info('License pool bulk upload', [
            'admin_id' => auth()->id(),
            'product_id' => $request->product_id,
            'total_keys' => count($keys),
            'valid_count' => count($validKeys),
            'invalid_count' => count($invalidKeys),
            'saved_count' => $savedCount,
            'existing_count' => $existingCount,
        ]);
        
        $message = "Berhasil upload {$savedCount} lisensi baru. ";
        
        if ($existingCount > 0) {
            $message .= "{$existingCount} lisensi sudah ada (diabaikan). ";
        }
        
        if (!empty($invalidKeys)) {
            $message .= "{$invalidKeys} lisensi invalid.";
        }
        
        return redirect()->route('admin.license-pool.index')
            ->with('success', $message)
            ->with('invalid_keys', $invalidKeys);
    }

    public function show(LicensePool $licensePool)
    {
        $licensePool->load(['product', 'userLicenses.user', 'userLicenses.order']);
        
        $usageStats = [
            'total_assignments' => $licensePool->userLicenses->count(),
            'active_assignments' => $licensePool->userLicenses->where('status', 'active')->count(),
            'pending_assignments' => $licensePool->userLicenses->where('status', 'pending')->count(),
            'blocked_assignments' => $licensePool->userLicenses->where('status', 'blocked')->count(),
        ];
        
        return view('admin.licenses.pool.show', compact('licensePool', 'usageStats'));
    }

    public function revalidate(Request $request, $id)
    {
        $licensePool = LicensePool::findOrFail($id);
        
        try {
            $validation = $this->pidKeyService->validateKey($licensePool->getPlainAttribute('license_key'));
            
            $licensePool->update([
                'errorcode' => $validation['errorcode'],
                'status' => $validation['is_valid'] ? 'active' : 'blocked',
                'last_validated_at' => now(),
                'validation_count' => $licensePool->validation_count + 1,
            ]);
            
            $message = $validation['is_valid'] 
                ? 'Lisensi valid (' . $validation['errorcode'] . ')'
                : 'Lisensi invalid (' . $validation['errorcode'] . ')';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_status' => $licensePool->status,
                'errorcode' => $licensePool->errorcode,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkRevalidate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        
        $licenses = LicensePool::where('product_id', $request->product_id)
            ->where('status', 'active')
            ->get();
        
        $total = $licenses->count();
        $validated = 0;
        $blocked = 0;
        $errors = 0;
        
        foreach ($licenses as $license) {
            try {
                $validation = $this->pidKeyService->validateKey($license->getPlainAttribute('license_key'));
                
                $newStatus = $validation['is_valid'] ? 'active' : 'blocked';
                
                $license->update([
                    'errorcode' => $validation['errorcode'],
                    'status' => $newStatus,
                    'last_validated_at' => now(),
                    'validation_count' => $license->validation_count + 1,
                ]);
                
                $newStatus === 'active' ? $validated++ : $blocked++;
                
            } catch (\Exception $e) {
                $errors++;
                Log::error('Bulk revalidation error', [
                    'license_pool_id' => $license->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $message = "Bulk revalidation selesai. ";
        $message .= "Total: {$total}, Valid: {$validated}, Blocked: {$blocked}, Errors: {$errors}";
        
        return back()->with('success', $message);
    }

    public function update(Request $request, LicensePool $licensePool)
    {
        $request->validate([
            'status' => 'required|in:active,blocked,invalid,exhausted',
        ]);
        
        $oldStatus = $licensePool->status;
        $licensePool->update(['status' => $request->status]);
        
        Log::info('License pool status updated', [
            'admin_id' => auth()->id(),
            'license_pool_id' => $licensePool->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);
        
        return back()->with('success', 'Status lisensi berhasil diupdate.');
    }

    public function destroy(LicensePool $licensePool)
    {
        if ($licensePool->userLicenses()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus lisensi yang sudah digunakan.');
        }
        
        $licensePool->delete();
        
        Log::info('License pool deleted', [
            'admin_id' => auth()->id(),
            'license_pool_id' => $licensePool->id,
        ]);
        
        return back()->with('success', 'Lisensi berhasil dihapus.');
    }

    public function showInvalidKeys(Request $request)
    {
        $invalidKeys = json_decode($request->session()->get('invalid_keys', '[]'), true);
        
        return view('admin.licenses.pool.invalid-keys', compact('invalidKeys'));
    }

    public function export(Request $request)
    {
        $query = LicensePool::with('product');
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $licenses = $query->get();
        
        $fileName = 'license-pool-' . date('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        $callback = function() use ($licenses) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'License Key',
                'Product',
                'Status',
                'Error Code',
                'Validated At',
                'Last Validated',
                'Validation Count',
                'Created At',
            ]);
            
            foreach ($licenses as $license) {
                fputcsv($file, [
                    $license->getPlainAttribute('license_key'),
                    $license->product->name,
                    $license->status,
                    $license->errorcode,
                    $license->validated_at?->format('Y-m-d H:i:s'),
                    $license->last_validated_at?->format('Y-m-d H:i:s'),
                    $license->validation_count,
                    $license->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}