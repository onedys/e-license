@extends('layouts.app')

@section('title', $product->name . ' - e-License')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="img-fluid rounded">
                        @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                             style="height: 250px;">
                            <i class="fas fa-box fa-4x text-muted"></i>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h2 class="mb-2">{{ $product->name }}</h2>
                        <h4 class="text-primary mb-3">Rp {{ number_format($product->price, 0, ',', '.') }}</h4>
                        
                        <div class="mb-3">
                            <span class="badge bg-{{ $product->is_in_stock ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $product->is_in_stock ? 'check' : 'times' }}"></i>
                                {{ $product->is_in_stock ? 'Stok Tersedia' : 'Stok Habis' }}
                            </span>
                            <span class="badge bg-secondary ms-2">{{ $product->category }}</span>
                        </div>
                        
                        <div class="mb-4">
                            <p>{{ $product->description }}</p>
                        </div>
                        
                        @if($product->features)
                        <div class="mb-4">
                            <h6><i class="fas fa-star"></i> Fitur:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->features as $feature)
                                <span class="badge bg-info">{{ $feature }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        @if($product->is_in_stock)
                        <form method="POST" action="{{ route('cart.add', $product->id) }}" class="mt-4">
                            @csrf
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Jumlah</span>
                                        <input type="number" name="quantity" value="1" min="1" 
                                               max="{{ $product->stock_type === 2 ? $product->available_stock : 10 }}" 
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Produk ini sedang tidak tersedia. Silakan coba lagi nanti.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Produk</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-key"></i> Jenis Lisensi</h6>
                        <p>Lisensi digital retail untuk aktivasi permanen</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-shipping-fast"></i> Pengiriman</h6>
                        <p>Digital delivery langsung ke dashboard setelah pembayaran</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-shield-alt"></i> Garansi</h6>
                        <p>7 hari penggantian untuk lisensi "Key Blocked"</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-headset"></i> Support</h6>
                        <p>Bantuan aktivasi melalui dashboard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Proses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; margin-right: 15px;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">1. Tambah ke Keranjang</h6>
                                <small class="text-muted">Pilih jumlah dan tambahkan</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; margin-right: 15px;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">2. Checkout & Bayar</h6>
                                <small class="text-muted">Pilih metode pembayaran</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; margin-right: 15px;">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">3. Terima Lisensi</h6>
                                <small class="text-muted">Otomatis muncul di dashboard</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px; margin-right: 15px;">
                                <i class="fas fa-play-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">4. Aktivasi</h6>
                                <small class="text-muted">Input Installation ID di dashboard</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-question-circle"></i> FAQ</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Berapa lama lisensi dikirim?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Lisensi dikirim otomatis dalam 1-5 menit setelah pembayaran berhasil.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Bagaimana cara aktivasi?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Buka dashboard → Pilih lisensi → Input Installation ID → Dapat Confirmation ID.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Apa itu Installation ID?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Installation ID adalah kode unik dari komputer Anda (54 atau 63 digit) yang didapat saat aktivasi Windows/Office.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Perhatian</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        <li>Setiap lisensi untuk 1 aktivasi</li>
                        <li>Installation ID harus berbeda per lisensi</li>
                        <li>Garansi hanya untuk lisensi "Key Blocked"</li>
                        <li>Error server tidak mengurangi kuota</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection