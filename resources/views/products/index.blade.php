@extends('layouts.app')

@section('title', 'Produk - e-License')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1><i class="fas fa-store"></i> Produk Lisensi</h1>
        <p class="lead">Pilih lisensi yang Anda butuhkan</p>
    </div>
</div>

<div class="row">
    @foreach($products as $product)
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">{{ $product->name }}</h5>
                <h6 class="card-subtitle mb-2 text-success">
                    {{ $product->formatted_price }}
                </h6>
                
                <p class="card-text text-muted">
                    {{ Str::limit($product->description, 100) }}
                </p>
                
                <div class="mb-3">
                    @if($product->features)
                        @foreach($product->features as $feature)
                            <span class="badge bg-info me-1">{{ $feature }}</span>
                        @endforeach
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $product->is_in_stock ? 'success' : 'danger' }}">
                        <i class="fas fa-{{ $product->is_in_stock ? 'check' : 'times' }}"></i>
                        {{ $product->is_in_stock ? 'Tersedia' : 'Habis' }}
                    </span>
                    
                    <a href="{{ route('products.show', $product->slug) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($products->isEmpty())
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Tidak ada produk yang tersedia saat ini.
        </div>
    </div>
    @endif
</div>
@endsection