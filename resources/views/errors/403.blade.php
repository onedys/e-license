@extends('layouts.app')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 text-muted">403</h1>
                <h2 class="mb-4"><i class="fas fa-ban text-danger"></i> Akses Ditolak</h2>
                <p class="lead mb-4">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                
                @if(auth()->check())
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i>
                        Anda login sebagai: <strong>{{ auth()->user()->name }}</strong>
                        @if(auth()->user()->is_admin)
                            <span class="badge bg-warning ms-2">Admin</span>
                        @endif
                    </div>
                @endif
                
                <div class="mb-5">
                    <a href="{{ url('/') }}" class="btn btn-primary me-2">
                        <i class="fas fa-home"></i> Kembali ke Home
                    </a>
                    @auth
                    <a href="{{ route('user.dashboard') }}" class="btn btn-success">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    @endauth
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Jika Anda merasa ini kesalahan:</h5>
                        <ul>
                            <li>Pastikan Anda login dengan akun yang benar</li>
                            <li>Hubungi administrator jika Anda memerlukan akses</li>
                            <li>Clear cache dan cookies browser Anda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection