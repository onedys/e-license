@extends('layouts.admin')

@section('title', 'User Orders - e-License')
@section('page-title', 'User Orders')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.users.index') }}">Users</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
</li>
<li class="breadcrumb-item active">Orders</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to User
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart"></i> Orders for {{ $user->name }}
            <span class="badge bg-primary">{{ $orders->total() }} orders</span>
        </h5>
    </div>
    <div class="card-body">
        @if($orders->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No orders found</h5>
                <p>This user hasn't made any purchases yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Paid At</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->product->name }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($order->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($order->payment_status === 'expired')
                                    <span class="badge bg-secondary">Expired</span>
                                @endif
                            </td>
                            <td>{{ $order->payment_method ?? 'N/A' }}</td>
                            <td>
                                @if($order->paid_at)
                                    {{ $order->paid_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
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
@endsection