@extends('layouts.app')

@section('title', '429 - Terlalu Banyak Request')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 text-muted">429</h1>
                <h2 class="mb-4"><i class="fas fa-tachometer-alt text-warning"></i> Terlalu Banyak Request</h2>
                <p class="lead mb-4">Anda telah mengirim terlalu banyak request ke server. Silakan coba lagi nanti.</p>
                
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    Ini biasanya terjadi karena:
                    <ul class="mb-0 mt-2">
                        <li>Terlalu banyak percobaan login</li>
                        <li>Terlalu banyak request aktivasi lisensi</li>
                        <li>Terlalu banyak klaim garansi dalam waktu singkat</li>
                    </ul>
                </div>
                
                <div class="mb-5">
                    <a href="{{ url('/') }}" class="btn btn-primary me-2">
                        <i class="fas fa-home"></i> Kembali ke Home
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Refresh Halaman
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Saran:</h5>
                        <ul>
                            <li>Tunggu beberapa menit sebelum mencoba lagi</li>
                            <li>Jika melakukan aktivasi, pastikan Installation ID benar</li>
                            <li>Gunakan fitur dengan bijak, hindari spam</li>
                            <li>Hubungi admin jika masalah berlanjut</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
