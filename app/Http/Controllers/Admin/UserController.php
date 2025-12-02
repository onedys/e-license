<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->is_admin === 'yes');
        }
        
        $users = $query->latest()->paginate(50);
        
        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'today' => User::whereDate('created_at', today())->count(),
            'active_today' => User::whereHas('licenses', function($q) {
                $q->whereDate('activated_at', today());
            })->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->loadCount(['orders', 'licenses']);
        
        $recentOrders = $user->orders()
            ->with('product')
            ->latest()
            ->limit(10)
            ->get();
        
        $recentLicenses = $user->licenses()
            ->with(['order.product'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('admin.users.show', compact('user', 'recentOrders', 'recentLicenses'));
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        Log::info('User password reset by admin', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'username' => $user->username,
        ]);
        
        return back()->with('success', 'Password berhasil direset.');
    }

    /**
     * Toggle user status (enable/disable).
     */
    public function toggleStatus(User $user)
    {
        // Note: We might want to add a 'is_active' field to users table
        // For now, we'll just toggle is_admin
        $user->update([
            'is_admin' => !$user->is_admin,
        ]);
        
        $status = $user->is_admin ? 'admin' : 'user';
        
        Log::info('User status toggled', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'new_status' => $status,
        ]);
        
        return back()->with('success', "User status changed to {$status}.");
    }

    /**
     * Get user's licenses.
     */
    public function userLicenses(User $user)
    {
        $licenses = $user->licenses()
            ->with(['order.product', 'licensePool'])
            ->latest()
            ->paginate(50);
        
        return view('admin.users.licenses', compact('user', 'licenses'));
    }

    /**
     * Get user's orders.
     */
    public function userOrders(User $user)
    {
        $orders = $user->orders()
            ->with('product')
            ->latest()
            ->paginate(50);
        
        return view('admin.users.orders', compact('user', 'orders'));
    }
}