@extends('layouts.admin')

@section('title', 'Order Management - e-License')
@section('page-title', 'Order Management')

@section('breadcrumbs')
<li class="breadcrumb-item active">Orders</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-filter"></i> Filter
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('admin.orders.index') }}?status=all">All Orders</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('admin.orders.index') }}?status=pending">
            <span class="badge bg-warning float-end">{{ $stats['pending'] }}</span>
            Pending
        </a>
        <a class="dropdown-item" href="{{ route('admin.orders.index') }}?status=paid">
            <span class="badge bg-success float-end">{{ $stats['paid'] }}</span>
            Paid
        </a>
        <a class="dropdown-item" href="{{ route('admin.orders.index') }}?status=failed">
            Failed
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Orders</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Sales</h6>
                        <h3 class="mb-0">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Pending</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Today</h6>
                        <h3 class="mb-0">{{ $stats['today'] }}</h3>
                    </div>
                    <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
    @csrf
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Order #, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-12 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                <strong>{{ $order->order_number }}</strong>
                            </a>
                            <br>
                            <small class="text-muted">Ref: {{ $order->tripay_reference ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ $order->user->name }}
                            <br>
                            <small class="text-muted">{{ $order->user->username }}</small>
                        </td>
                        <td>{{ $order->product->name }}</td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                <br>
                                <small class="text-muted">{{ $order->paid_at?->format('d/m/Y H:i') }}</small>
                            @elseif($order->payment_status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($order->payment_status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </td>
                        <td>{{ $order->payment_method ?? 'N/A' }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($order->payment_status === 'pending')
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="paid">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-check"></i> Mark as Paid
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="failed">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-times"></i> Mark as Failed
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
    </div>
</div>
@endsection
