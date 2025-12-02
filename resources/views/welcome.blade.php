@extends('layouts.app')

@section('title', 'e-License - Platform Lisensi Digital')

@section('content')
<div class="hero-section text-center py-5 mb-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
    <div class="container">
        <h1 class="display-4 mb-3">
            <i class="fas fa-key"></i> e-License
        </h1>
        <p class="lead mb-4">Platform penjualan lisensi digital terpercaya</p>
        
        @auth
            @if(Auth::user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-cog"></i> Admin Dashboard
                </a>
            @else
                <a href="{{ route('user.dashboard') }}" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-tachometer-alt"></i> User Dashboard
                </a>
            @endif
        @else
            <a href="{{ route('register') }}" class="btn btn-light btn-lg me-2">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        @endauth
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4 mb-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-bolt fa-2x"></i>
                </div>
                <h4 class="card-title">Cepat & Mudah</h4>
                <p class="card-text">Lisensi dikirim otomatis setelah pembayaran. Tidak perlu menunggu lama.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-shield-alt fa-2x"></i>
                </div>
                <h4 class="card-title">Garansi 7 Hari</h4>
                <p class="card-text">Klaim garansi otomatis jika lisensi bermasalah. Aman dan terpercaya.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-headset fa-2x"></i>
                </div>
                <h4 class="card-title">Support 24/7</h4>
                <p class="card-text">Bantuan aktivasi melalui dashboard. Tidak perlu kontak manual.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-store"></i> Produk Terpopuler</h4>
            </div>
            <div class="card-body">
                @php
                    $products = \App\Models\Product::active()->limit(3)->get();
                @endphp
                
                @if($products->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p>Belum ada produk yang tersedia</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($products as $product)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5>{{ $product->name }}</h5>
                                    <h4 class="text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</h4>
                                    <p class="text-muted small">{{ Str::limit($product->description, 50) }}</p>
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-store"></i> Lihat Semua Produk
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-question-circle"></i> Cara Berbelanja</h4>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <span>1</span>
                            </div>
                            <span>Register/Login akun</span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <span>2</span>
                            </div>
                            <span>Pilih produk & tambah ke keranjang</span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <span>3</span>
                            </div>
                            <span>Checkout & bayar via Tripay</span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <span>4</span>
                            </div>
                            <span>Lisensi otomatis muncul di dashboard</span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <span>5</span>
                            </div>
                            <span>Aktivasi dengan Installation ID</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection