<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\Notification\DashboardNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(DashboardNotification $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        
        return view('user.notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);
        
        $this->notificationService->markAsRead($notification->id);
        
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $this->notificationService->markAllAsRead((int) Auth::id());
        
        return back()->with('success', 'All notifications marked as read.');
    }
}