@extends('layouts.admin')

@section('title', 'Order Details - e-License')
@section('page-title', 'Order Details')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.orders.index') }}">Orders</a>
</li>
<li class="breadcrumb-item active">#{{ $order->order_number }}</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-receipt"></i> Order Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Order Number</strong></td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Customer</strong></td>
                        <td>
                            {{ $order->user->name }} ({{ $order->user->username }})
                            <br>
                            <small class="text-muted">
                                @if($order->user->email) {{ $order->user->email }}<br> @endif
                                @if($order->user->phone) {{ $order->user->phone }} @endif
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Product</strong></td>
                        <td>{{ $order->product->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Quantity</strong></td>
                        <td>{{ $order->quantity }}</td>
                    </tr>
                    <tr>
                        <td><strong>Unit Price</strong></td>
                        <td>Rp {{ number_format($order->unit_price, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount</strong></td>
                        <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($order->payment_status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($order->payment_status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method</strong></td>
                        <td>{{ $order->payment_method ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tripay Reference</strong></td>
                        <td>{{ $order->tripay_reference ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created At</strong></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Paid At</strong></td>
                        <td>{{ $order->paid_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Warranty Until</strong></td>
                        <td>
                            {{ $order->warranty_until?->format('d F Y') ?? 'N/A' }}
                            @if($order->warranty_until && $order->warranty_until->isFuture())
                                <br>
                                <small class="text-success">
                                    <i class="fas fa-clock"></i> 
                                    {{ $order->warranty_until->diffForHumans() }}
                                </small>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-key"></i> License Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Assigned Licenses ({{ $order->licenses->count() }}/{{ $order->quantity }})</h6>
                        @if($order->licenses->count() < $order->quantity && $order->payment_status === 'paid')
                        <form method="POST" action="{{ route('admin.orders.resend-license', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="fas fa-paper-plane"></i> Send License
                            </button>
                        </form>
                        @endif
                    </div>
                    
                    @if($order->licenses->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No licenses assigned yet.
                            @if($order->payment_status === 'paid')
                                <br>
                                <small>Click "Send License" to assign licenses to customer.</small>
                            @endif
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($order->licenses as $license)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <code class="small">{{ $license->license_key_formatted }}</code>
                                        <br>
                                        <small class="text-muted">
                                            Status: 
                                            <span class="badge bg-{{ $license->status == 'active' ? 'success' : ($license->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($license->status) }}
                                            </span>
                                            @if($license->activated_at)
                                                | Activated: {{ $license->activated_at->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.license-pool.show', $license->license_pool_id) }}" 
                                           class="btn btn-sm btn-outline-info" title="View License Pool">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Payment Information -->
                @if($order->payment)
                <div class="mt-4">
                    <h6><i class="fas fa-credit-card"></i> Payment Details</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Reference</td>
                            <td>{{ $order->payment->reference }}</td>
                        </tr>
                        <tr>
                            <td>Merchant Ref</td>
                            <td>{{ $order->payment->merchant_ref }}</td>
                        </tr>
                        <tr>
                            <td>Amount</td>
                            <td>Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Paid Amount</td>
                            <td>{{ $order->payment->paid_amount ? 'Rp ' . number_format($order->payment->paid_amount, 0, ',', '.') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>
                                <span class="badge bg-{{ $order->payment->status == 'paid' ? 'success' : ($order->payment->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order->payment->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                @endif
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
        <div class="row">
            @if($order->payment_status === 'pending')
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="paid">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> Mark as Paid
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="failed">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-times"></i> Mark as Failed
                    </button>
                </form>
            </div>
            @endif
            
            @if($order->payment_status === 'paid' && $order->licenses->count() < $order->quantity)
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.orders.resend-license', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-paper-plane"></i> Send License
                    </button>
                </form>
            </div>
            @endif
            
            <div class="col-md-3 mb-2">
                <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-info w-100">
                    <i class="fas fa-user"></i> View Customer
                </a>
            </div>
        </div>
    </div>
</div>
@endsection