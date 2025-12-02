@extends('layouts.app')

@section('title', 'Pembayaran Berhasil - e-License')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center border-success">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-check-circle"></i> Pembayaran Berhasil!</h4>
            </div>
            
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h5>Terima kasih atas pembelian Anda!</h5>
                    <p class="text-muted">Order #{{ $order->order_number }}</p>
                </div>
                
                <div class="alert alert-success mb-4">
                    <i class="fas fa-box"></i>
                    <strong>Lisensi Anda sedang diproses...</strong><br>
                    Dalam beberapa saat, lisensi akan muncul di dashboard Anda.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="fas fa-receipt"></i> Detail Order</h6>
                                <p class="mb-1"><small>Produk: {{ $order->product->name }}</small></p>
                                <p class="mb-1"><small>Jumlah: {{ $order->quantity }}</small></p>
                                <p class="mb-1"><small>Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}</small></p>
                                <p class="mb-0"><small>Status: <span class="badge bg-success">{{ $order->payment_status }}</span></small></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="fas fa-shield-alt"></i> Garansi</h6>
                                <p class="mb-1"><small>Masa Garansi:</small></p>
                                <p class="mb-1"><strong>{{ $order->warranty_until->format('d F Y') }}</strong></p>
                                <p class="mb-0"><small>(7 hari dari tanggal pembelian)</small></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-block">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Ke Dashboard
                    </a>
                    
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-store"></i> Beli Lagi
                    </a>
                </div>
            </div>
            
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-clock"></i> Lisensi akan otomatis dikirim. Jika tidak muncul dalam 5 menit, hubungi admin.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
