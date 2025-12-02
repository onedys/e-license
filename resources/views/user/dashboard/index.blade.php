@extends('layouts.app')

@section('title', 'Dashboard - e-License')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="lead">Selamat datang, {{ Auth::user()->name }}!</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Order</h6>
                        <h2>{{ $stats['total_orders'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Lisensi</h6>
                        <h2>{{ $stats['total_licenses'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-key fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Lisensi Aktif</h6>
                        <h2>{{ $stats['active_licenses'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Belum Aktivasi</h6>
                        <h2>{{ $stats['pending_licenses'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Order Terbaru</h5>
            </div>
            <div class="card-body">
                @if($recentOrders->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Belum ada order</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Produk</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('user.orders.show', $order->order_number) }}" 
                                           class="text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->product->name }}</td>
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
                    <a href="{{ route('user.orders.index') }}" class="btn btn-outline-primary btn-sm">
                        Lihat Semua Order
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Recent Licenses -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Lisensi Terbaru</h5>
            </div>
            <div class="card-body">
                @if($recentLicenses->isEmpty())
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-key fa-2x mb-2"></i>
                        <p>Belum ada lisensi</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Lisensi</th>
                                    <th>Status</th>
                                    <th>Garansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLicenses as $license)
                                <tr>
                                    <td>
                                        <a href="{{ route('user.licenses.show', $license->id) }}" 
                                           class="text-decoration-none">
                                            {{ Str::limit($license->license_key_formatted, 20) }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $license->order->product->name }}</small>
                                    </td>
                                    <td>
                                        @if($license->status === 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($license->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($license->status === 'blocked')
                                            <span class="badge bg-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($license->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($license->is_warranty_valid)
                                            <small class="text-success">
                                                <i class="fas fa-shield-alt"></i> 
                                                {{ $license->warranty_until->diffForHumans() }}
                                            </small>
                                        @else
                                            <small class="text-muted">Expired</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('user.licenses.index') }}" class="btn btn-outline-primary btn-sm">
                        Lihat Semua Lisensi
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('products.index') }}" class="btn btn-primary w-100">
                            <i class="fas fa-store"></i> Beli Lisensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('user.licenses.index') }}" class="btn btn-success w-100">
                            <i class="fas fa-key"></i> Lihat Lisensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('user.activation.form') }}" class="btn btn-info w-100">
                            <i class="fas fa-play-circle"></i> Aktivasi Lisensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('user.warranty.claim.form') }}" class="btn btn-warning w-100">
                            <i class="fas fa-shield-alt"></i> Klaim Garansi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
