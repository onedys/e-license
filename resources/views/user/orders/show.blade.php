@extends('layouts.app')

@section('title', 'Order Details - e-License')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-receipt"></i> Order Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Order Number</strong></td>
                                <td>{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date</strong></td>
                                <td>{{ $order->created_at->format('d F Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Product</strong></td>
                                <td>{{ $order->product->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Quantity</strong></td>
                                <td>{{ $order->quantity }} license(s)</td>
                            </tr>
                            <tr>
                                <td><strong>Unit Price</strong></td>
                                <td>Rp {{ number_format($order->unit_price, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount</strong></td>
                                <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Payment Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Status</strong></td>
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
                            @if($order->paid_at)
                            <tr>
                                <td><strong>Paid At</strong></td>
                                <td>{{ $order->paid_at->format('d F Y H:i') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Warranty Until</strong></td>
                                <td>
                                    {{ $order->warranty_until->format('d F Y') }}
                                    @if($order->is_warranty_valid)
                                        <span class="badge bg-success ms-2">
                                            {{ $order->warranty_until->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Payment Actions -->
                @if($order->payment_status === 'pending')
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Payment Pending!</strong> Please complete your payment.
                    <div class="mt-2">
                        <a href="{{ route('payment.redirect', $order->order_number) }}" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Complete Payment
                        </a>
                    </div>
                </div>
                @endif
                
                @if($order->payment_status === 'paid')
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i>
                    <strong>Payment Successful!</strong> Your licenses have been assigned.
                </div>
                @endif
            </div>
        </div>
        
        <!-- Licenses from this order -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Licenses from this Order</h5>
            </div>
            <div class="card-body">
                @if($order->licenses->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-key fa-2x mb-2"></i>
                        <p>No licenses assigned yet.</p>
                        @if($order->payment_status === 'paid')
                            <p class="small">Licenses will be assigned automatically. If not, please contact admin.</p>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>License Key</th>
                                    <th>Status</th>
                                    <th>Activated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->licenses as $license)
                                <tr>
                                    <td>
                                        <code class="small">{{ $license->license_key_formatted }}</code>
                                    </td>
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
                                    <td>
                                        @if($license->activated_at)
                                            {{ $license->activated_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Not yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('user.licenses.show', $license->id) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($license->status === 'pending')
                                            <a href="{{ route('user.activation.form') }}" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-play-circle"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('user.orders.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> All Orders
                    </a>
                    <a href="{{ route('user.licenses.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-key"></i> My Licenses
                    </a>
                    @if($order->payment_status === 'pending')
                    <a href="{{ route('payment.redirect', $order->order_number) }}" class="btn btn-warning">
                        <i class="fas fa-credit-card"></i> Complete Payment
                    </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-store"></i> Buy Again
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Warranty Info -->
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Warranty Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Warranty Period:</strong> 7 days from purchase</p>
                <p><strong>Valid Until:</strong> {{ $order->warranty_until->format('d F Y') }}</p>
                <p><strong>Status:</strong> 
                    @if($order->is_warranty_valid)
                        <span class="text-success">Active</span>
                        <br>
                        <small>Expires {{ $order->warranty_until->diffForHumans() }}</small>
                    @else
                        <span class="text-danger">Expired</span>
                    @endif
                </p>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <small>
                        <strong>Note:</strong> Warranty covers license replacement if activation fails 
                        (Key Blocked) within warranty period.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
