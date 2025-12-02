@extends('layouts.app')

@section('title', 'Detail Lisensi - e-License')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-key"></i> Detail Lisensi</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Lisensi</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Lisensi</strong></td>
                                <td><code>{{ $license->license_key_formatted }}</code></td>
                            </tr>
                            <tr>
                                <td><strong>Produk</strong></td>
                                <td>{{ $license->order->product->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Order</strong></td>
                                <td>{{ $license->order->order_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
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
                            </tr>
                            <tr>
                                <td><strong>Garansi Sampai</strong></td>
                                <td>
                                    {{ $license->warranty_until->format('d F Y') }}
                                    @if($license->is_warranty_valid)
                                        <span class="badge bg-success ms-2">
                                            {{ $license->warranty_until->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Informasi Aktivasi</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Percobaan</strong></td>
                                <td>{{ $license->activation_attempts }}x</td>
                            </tr>
                            @if($license->activated_at)
                            <tr>
                                <td><strong>Aktivasi Terakhir</strong></td>
                                <td>{{ $license->activated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                            @if($license->confirmation_id_formatted)
                            <tr>
                                <td><strong>Confirmation ID</strong></td>
                                <td>
                                    <code>{{ $license->confirmation_id_formatted }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyToClipboard('{{ $license->confirmation_id_formatted }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @if($license->blocked_at)
                            <tr>
                                <td><strong>Diblokir Pada</strong></td>
                                <td>{{ $license->blocked_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <!-- Actions based on status -->
                <div class="mt-4">
                    @if($license->status === 'pending')
                        <a href="{{ route('user.activation.form') }}" class="btn btn-primary">
                            <i class="fas fa-play-circle"></i> Aktivasi Sekarang
                        </a>
                    @endif
                    
                    @if($license->can_claim_warranty)
                        <a href="{{ route('user.warranty.claim.form') }}" class="btn btn-warning">
                            <i class="fas fa-shield-alt"></i> Klaim Garansi
                        </a>
                    @endif
                    
                    @if($license->status === 'active' && $license->confirmation_id_formatted)
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i>
                            <strong>Lisensi sudah aktif!</strong><br>
                            Confirmation ID: <code>{{ $license->confirmation_id_formatted }}</code>
                            <button class="btn btn-sm btn-outline-success ms-2" 
                                    onclick="copyToClipboard('{{ $license->confirmation_id_formatted }}')">
                                <i class="fas fa-copy"></i> Salin
                            </button>
                        </div>
                    @endif
                    
                    @if($license->status === 'blocked' && !$license->is_warranty_valid)
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-times-circle"></i>
                            <strong>Lisensi blocked dan masa garansi habis.</strong><br>
                            Tidak bisa klaim garansi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Activation Logs -->
        @if($license->activationLogs->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Aktivasi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Installation ID</th>
                                <th>Status</th>
                                <th>Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($license->activationLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <small>
                                        {{ substr($log->getPlainAttribute('installation_id'), 0, 20) }}...
                                    </small>
                                </td>
                                <td>
                                    @if($log->status === 'success')
                                        <span class="badge bg-success">Success</span>
                                    @elseif($log->status === 'blocked')
                                        <span class="badge bg-danger">Blocked</span>
                                    @else
                                        <span class="badge bg-warning">Error</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="showLogResponse({{ $log->id }})">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('user.licenses.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Semua Lisensi
                    </a>
                    <a href="{{ route('user.orders.show', $license->order->order_number) }}" 
                       class="btn btn-outline-info">
                        <i class="fas fa-receipt"></i> Detail Order
                    </a>
                    <a href="{{ route('user.activation.form') }}" class="btn btn-outline-success">
                        <i class="fas fa-play-circle"></i> Aktivasi Lain
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-warning">
                        <i class="fas fa-store"></i> Beli Lagi
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Warranty Info -->
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Informasi Garansi</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-calendar-check text-success"></i>
                        <strong>Masa Garansi:</strong><br>
                        {{ $license->warranty_until->format('d F Y') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-info"></i>
                        <strong>Sisa Waktu:</strong><br>
                        @if($license->is_warranty_valid)
                            {{ $license->warranty_until->diffForHumans() }}
                        @else
                            <span class="text-danger">Telah habis</span>
                        @endif
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-exchange-alt text-primary"></i>
                        <strong>Penggantian:</strong><br>
                        @if($license->replaced_by)
                            <span class="text-success">Sudah diganti</span>
                        @else
                            <span class="text-info">Belum pernah</span>
                        @endif
                    </li>
                    <li>
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <strong>Syarat Klaim:</strong><br>
                        <small>Status blocked + masa garansi valid</small>
                    </li>
                </ul>
                
                @if($license->can_claim_warranty)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Eligible untuk garansi!</strong><br>
                    <a href="{{ route('user.warranty.claim.form') }}" class="btn btn-warning btn-sm mt-2">
                        <i class="fas fa-shield-alt"></i> Klaim Sekarang
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for log response -->
<div class="modal fade" id="logResponseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Response API</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="logResponseContent" class="bg-light p-3" style="max-height: 400px; overflow: auto;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Berhasil disalin!');
    }, function() {
        alert('Gagal menyalin. Silakan salin manual.');
    });
}

function showLogResponse(logId) {
    // In a real implementation, you would fetch this via AJAX
    // For now, we'll show a placeholder
    $('#logResponseContent').html('Loading...');
    $('#logResponseModal').modal('show');
    
    // AJAX implementation example:
    /*
    $.ajax({
        url: '/user/logs/' + logId + '/response',
        type: 'GET',
        success: function(response) {
            $('#logResponseContent').html(JSON.stringify(response, null, 2));
        }
    });
    */
}
</script>
@endsection