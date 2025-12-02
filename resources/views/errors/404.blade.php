@extends('layouts.app')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 text-muted">404</h1>
                <h2 class="mb-4"><i class="fas fa-exclamation-triangle text-warning"></i> Halaman Tidak Ditemukan</h2>
                <p class="lead mb-4">Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.</p>
                
                <div class="mb-5">
                    <a href="{{ url('/') }}" class="btn btn-primary me-2">
                        <i class="fas fa-home"></i> Kembali ke Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mungkin Anda mencari:</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('products.index') }}"><i class="fas fa-store"></i> Produk</a></li>
                            @auth
                            <li><a href="{{ route('user.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="{{ route('user.licenses.index') }}"><i class="fas fa-key"></i> Lisensi Saya</a></li>
                            @else
                            <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                            <li><a href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Register</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection