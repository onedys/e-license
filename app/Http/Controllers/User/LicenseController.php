<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->licenses()->with(['order.product', 'licensePool']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('product')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('product_id', $request->product);
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('license_key', 'LIKE', "%{$search}%")
                ->orWhereHas('order', function($q2) use ($search) {
                    $q2->where('order_number', 'LIKE', "%{$search}%");
                });
            });
        }
        
        $licenses = $query->latest()->paginate(20);
        
        return view('user.licenses.index', compact('licenses'));
    }
}