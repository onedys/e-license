@extends('layouts.app')

@section('title', 'Notifications - e-License')

@section('content')
<div class="row">
    <div class="col">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p class="lead">Your system notifications</p>
    </div>
    <div class="col-auto">
        <form method="POST" action="{{ route('user.notifications.mark-all-read') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($notifications->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                <h5>No notifications</h5>
                <p>You don't have any notifications yet.</p>
            </div>
        @else
            <div class="list-group">
                @foreach($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'list-group-item-primary' }}">
                    <div class="d-flex w-100 justify-content-between">
                        <div class="mb-1">
                            @if($notification->type === 'license_assigned')
                                <i class="fas fa-key text-success me-2"></i>
                                <strong>License Assigned</strong>
                            @elseif($notification->type === 'activation_success')
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Activation Success</strong>
                            @elseif($notification->type === 'warranty_approved')
                                <i class="fas fa-shield-alt text-warning me-2"></i>
                                <strong>Warranty Approved</strong>
                            @elseif($notification->type === 'payment_success')
                                <i class="fas fa-credit-card text-info me-2"></i>
                                <strong>Payment Success</strong>
                            @else
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                <strong>System Notification</strong>
                            @endif
                            
                            <p class="mb-1 mt-2">{{ $notification->data['message'] ?? 'No message' }}</p>
                            
                            @if(isset($notification->data['license_key']))
                                <small class="text-muted">
                                    License: <code>{{ $notification->data['license_key'] }}</code>
                                </small>
                            @endif
                        </div>
                        <div class="text-end">
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            <br>
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('user.notifications.mark-read', $notification) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-check"></i> Mark Read
                                    </button>
                                </form>
                            @else
                                <small class="text-success">
                                    <i class="fas fa-check"></i> Read
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>
@endsection
