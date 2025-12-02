@extends('layouts.admin')

@section('title', 'User Details - e-License')
@section('page-title', 'User Details')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.users.index') }}">Users</a>
</li>
<li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-circle"></i> User Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="fas fa-user-circle fa-5x text-secondary"></i>
                    <h4 class="mt-3">{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->username }}</p>
                    <span class="badge bg-{{ $user->is_admin ? 'danger' : 'secondary' }}">
                        {{ $user->is_admin ? 'Administrator' : 'Regular User' }}
                    </span>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Email</strong></td>
                        <td>{{ $user->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone</strong></td>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Member Since</strong></td>
                        <td>
                            {{ $user->created_at->format('d F Y') }}
                            <br>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Last Login</strong></td>
                        <td>
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('d/m/Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $user->orders_count }}</h3>
                                <small class="text-muted">Total Orders</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3 class="mb-0">{{ $user->licenses_count }}</h3>
                                <small class="text-muted">Total Licenses</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" 
                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-grid">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-{{ $user->is_admin ? 'secondary' : 'danger' }}">
                            @if($user->is_admin)
                                <i class="fas fa-user"></i> Make Regular User
                            @else
                                <i class="fas fa-user-shield"></i> Make Admin
                            @endif
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.users.orders', $user) }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                    
                    <a href="{{ route('admin.users.licenses', $user) }}" class="btn btn-outline-info">
                        <i class="fas fa-key"></i> View Licenses
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Orders</h5>
            </div>
            <div class="card-body">
                @if($recentOrders->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-3"></i>
                        <p>No orders found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->product->name }}</td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->payment_status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($order->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('admin.users.orders', $user) }}" class="btn btn-sm btn-outline-primary">
                            View All Orders
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Licenses -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Recent Licenses</h5>
            </div>
            <div class="card-body">
                @if($recentLicenses->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-key fa-2x mb-3"></i>
                        <p>No licenses found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>License Key</th>
                                    <th>Product</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Activated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLicenses as $license)
                                <tr>
                                    <td>
                                        <code class="small">{{ Str::limit($license->license_key_formatted, 20) }}</code>
                                    </td>
                                    <td>{{ $license->order->product->name }}</td>
                                    <td>
                                        @if($license->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($license->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($license->status === 'blocked')
                                            <span class="badge bg-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($license->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $license->order->order_number }}</td>
                                    <td>
                                        @if($license->activated_at)
                                            {{ $license->activated_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Not yet</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('admin.users.licenses', $user) }}" class="btn btn-sm btn-outline-primary">
                            View All Licenses
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password for {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        User will need to use this new password to login.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control" 
                               required minlength="8">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="new_password_confirmation" 
                               class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
