@extends('layouts.app')

@section('title', 'My Orders - e-License')

@section('content')
<div class="row">
    <div class="col">
        <h1><i class="fas fa-shopping-cart"></i> My Orders</h1>
        <p class="lead">All your purchase history</p>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Orders</h6>
                        <h3 class="mb-0">{{ $orders->total() }}</h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Spent</h6>
                        <h3 class="mb-0">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Licenses Bought</h6>
                        <h3 class="mb-0">{{ $totalLicenses }}</h3>
                    </div>
                    <i class="fas fa-key fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        @if($orders->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No orders found</h5>
                <p>You haven't made any purchases yet. <a href="{{ route('products.index') }}">Start shopping</a></p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Licenses</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                <br>
                                <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>{{ $order->product->name }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>{{ $order->quantity }}</td>
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
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('user.orders.show', $order->order_number) }}" 
                                       class="btn btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->payment_status === 'pending')
                                        <a href="{{ route('payment.redirect', $order->order_number) }}" 
                                           class="btn btn-outline-primary" title="Pay Now">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('products.index') }}" class="btn btn-primary">
        <i class="fas fa-store"></i> Continue Shopping
    </a>
    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>
@endsection
