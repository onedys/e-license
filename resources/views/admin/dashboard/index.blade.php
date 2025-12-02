@extends('layouts.admin')

@section('title', 'Admin Dashboard - e-License')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-cog"></i> Quick Actions
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="{{ route('admin.license-pool.create') }}">
                <i class="fas fa-plus"></i> Tambah Lisensi
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.products.create') }}">
                <i class="fas fa-box"></i> Tambah Produk
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.warranty.pending') }}">
                <i class="fas fa-shield-alt"></i> Pending Warranty
                @if($pendingManualWarranty > 0)
                <span class="badge bg-warning float-end">{{ $pendingManualWarranty }}</span>
                @endif
            </a>
        </li>
    </ul>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 fw-bold">{{ $stats['total_users'] }}</div>
                        <div class="mt-2 mb-0 text-muted text-xs">
                            <span class="text-success">
                                <i class="fas fa-user-plus"></i> Today: {{ $stats['today_orders'] }}
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Today Sales
                        </div>
                        <div class="h5 mb-0 fw-bold">Rp {{ number_format($stats['today_sales'], 0, ',', '.') }}</div>
                        <div class="mt-2 mb-0 text-muted text-xs">
                            <span>
                                <i class="fas fa-shopping-cart"></i> Orders: {{ $stats['today_orders'] }}
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-info border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                            License Pool
                        </div>
                        <div class="h5 mb-0 fw-bold">{{ $stats['available_pool'] }} / {{ $stats['total_license_pool'] }}</div>
                        <div class="mt-2 mb-0 text-muted text-xs">
                            <span class="{{ $stats['available_pool'] < 10 ? 'text-danger' : 'text-success' }}">
                                <i class="fas fa-key"></i> Available
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-database fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                            Today Activations
                        </div>
                        <div class="h5 mb-0 fw-bold">{{ $stats['today_activations'] }}</div>
                        <div class="mt-2 mb-0 text-muted text-xs">
                            <span>
                                <i class="fas fa-play-circle"></i> Blocked: {{ $stats['blocked_licenses'] }}
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line"></i> Sales Last 7 Days
                </h6>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="150"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-xl-4 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                </h6>
            </div>
            <div class="card-body">
                @if($lowStockProducts->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p>All products have sufficient stock</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $item->product->name }}</strong><br>
                                <small class="text-muted">Stock: {{ $item->stock }}</small>
                            </div>
                            <a href="{{ route('admin.license-pool.create') }}" 
                               class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-plus"></i> Add
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Recent Orders -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-shopping-cart"></i> Recent Orders
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
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
                                <td>{{ $order->user->name }}</td>
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                    View All Orders
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Activations -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-check-circle"></i> Recent Activations
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>License</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivations as $activation)
                            <tr>
                                <td>
                                    <code class="small">{{ Str::limit($activation->license_key_formatted, 15) }}</code>
                                </td>
                                <td>{{ $activation->user->name }}</td>
                                <td>{{ $activation->order->product->name }}</td>
                                <td>{{ $activation->activated_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Recent Warranty Claims -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-shield-alt"></i> Recent Warranty Claims
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Old License</th>
                                <th>New License</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentWarrantyClaims as $claim)
                            <tr>
                                <td>{{ $claim->created_at->format('d/m H:i') }}</td>
                                <td>{{ $claim->userLicense->user->name }}</td>
                                <td>
                                    <code class="small">{{ Str::limit($claim->userLicense->license_key_formatted, 10) }}</code>
                                </td>
                                <td>
                                    @if($claim->replacementLicense)
                                        <code class="small">{{ Str::limit($claim->replacementLicense->license_key_formatted, 10) }}</code>
                                    @else
                                        <span class="text-muted">Processing</span>
                                    @endif
                                </td>
                                <td>
                                    @if($claim->auto_approved)
                                        <span class="badge bg-success">Auto</span>
                                    @elseif($claim->approved_at)
                                        <span class="badge bg-info">Manual</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('admin.warranty.index') }}" class="btn btn-sm btn-outline-primary">
                    View All Claims
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: @json($salesData->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M'))),
        datasets: [{
            label: 'Orders',
            data: @json($salesData->pluck('total_orders')),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.3
        }, {
            label: 'Sales (Rp)',
            data: @json($salesData->pluck('total_sales')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.3,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Orders'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Sales (Rp)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});
</script>
@endsection