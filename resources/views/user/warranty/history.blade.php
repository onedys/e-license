@extends('layouts.app')

@section('title', 'Riwayat Garansi - e-License')

@section('content')
<div class="row">
    <div class="col">
        <h1><i class="fas fa-history"></i> Riwayat Klaim Garansi</h1>
        <p class="lead">Riwayat penggantian lisensi melalui garansi</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($warrantyClaims->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>Belum ada klaim garansi</h5>
                <p>Riwayat klaim garansi akan muncul di sini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Lisensi Lama</th>
                            <th>Lisensi Baru</th>
                            <th>Alasan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warrantyClaims as $claim)
                        <tr>
                            <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <code>{{ $claim->userLicense->license_key_formatted ?? 'N/A' }}</code><br>
                                <small class="text-muted">Order: {{ $claim->userLicense->order->order_number ?? '' }}</small>
                            </td>
                            <td>
                                @if($claim->replacementLicense)
                                    <code>{{ $claim->replacementLicense->license_key_formatted }}</code><br>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> Diganti
                                    </small>
                                @else
                                    <span class="text-muted">Sedang diproses</span>
                                @endif
                            </td>
                            <td>{{ $claim->reason }}</td>
                            <td>
                                @if($claim->auto_approved)
                                    <span class="badge bg-success">Auto-Approved</span>
                                @else
                                    <span class="badge bg-info">Manual</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $warrantyClaims->links() }}
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('user.warranty.claim.form') }}" class="btn btn-warning">
        <i class="fas fa-shield-alt"></i> Klaim Garansi Baru
    </a>
    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
