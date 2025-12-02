@extends('layouts.app')

@section('title', 'Klaim Garansi - e-License')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Klaim Garansi</h4>
            </div>
            
            <div class="card-body">
                @if($eligibleLicenses->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tidak ada lisensi yang eligible untuk garansi.</strong><br>
                        Garansi hanya berlaku untuk lisensi yang:
                        <ul class="mb-0 mt-2">
                            <li>Status: <span class="badge bg-danger">Blocked</span></li>
                            <li>Masa garansi masih valid (7 hari dari pembelian)</li>
                            <li>Belum pernah aktivasi sukses</li>
                            <li>Belum pernah diganti</li>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('user.licenses.index') }}" class="btn btn-primary">
                            <i class="fas fa-key"></i> Lihat Lisensi Saya
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('user.warranty.claim.process') }}" id="warrantyForm">
                        @csrf
                        
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Informasi Penting:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Garansi hanya 1x per lisensi</li>
                                <li>Masa garansi: 7 hari dari tanggal pembelian</li>
                                <li>Auto-approval: Sistem akan otomatis mengganti lisensi jika eligible</li>
                                <li>Kuota aktivasi akan bertambah dengan lisensi pengganti</li>
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-key"></i> Lisensi yang Bisa Diklaim</h5>
                            <p class="text-muted">Pilih lisensi yang ingin diklaim garansi</p>
                            
                            <div class="list-group" id="licenseList">
                                @foreach($eligibleLicenses as $license)
                                <div class="list-group-item license-option" 
                                     data-license-key="{{ $license->license_key_formatted }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="license_key_radio" 
                                               id="license_{{ $license->id }}"
                                               value="{{ $license->license_key_formatted }}">
                                        <label class="form-check-label" for="license_{{ $license->id }}">
                                            <strong>{{ $license->license_key_formatted }}</strong><br>
                                            <small class="text-muted">
                                                Order: {{ $license->order->order_number }} | 
                                                Produk: {{ $license->order->product->name }}<br>
                                                Masa Garansi: {{ $license->warranty_until->format('d F Y') }} |
                                                Sisa: {{ $license->warranty_until->diffForHumans() }}
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <input type="hidden" name="license_key" id="selectedLicenseKey">
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-clipboard-check"></i> Konfirmasi</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Dengan mengklaim garansi, Anda setuju bahwa:
                                <ul class="mb-0 mt-2">
                                    <li>Lisensi lama akan diganti dengan yang baru</li>
                                    <li>Kuota aktivasi Anda akan bertambah</li>
                                    <li>Masa garansi mengikuti lisensi asli (tidak diperpanjang)</li>
                                    <li>Proses ini tidak bisa dibatalkan</li>
                                </ul>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmationCheck" required>
                                <label class="form-check-label" for="confirmationCheck">
                                    Saya setuju dengan ketentuan garansi di atas.
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg" id="claimBtn" disabled>
                                <i class="fas fa-shield-alt"></i> Klaim Garansi
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-history"></i>
                    <a href="{{ route('user.warranty.history') }}" class="text-decoration-none">
                        Lihat riwayat klaim garansi
                    </a>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // License selection
    $('.license-option').click(function() {
        $('.license-option').removeClass('selected');
        $(this).addClass('selected');
        
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
        
        const licenseKey = $(this).data('license-key');
        $('#selectedLicenseKey').val(licenseKey);
        
        checkFormValidity();
    });
    
    // Auto-select if there's only one license
    if ($('.license-option').length === 1) {
        $('.license-option').first().click();
    }
    
    // Form validation
    $('#confirmationCheck').change(checkFormValidity);
    
    // Form submission
    $('#warrantyForm').submit(function(e) {
        if (!$('#selectedLicenseKey').val()) {
            e.preventDefault();
            alert('Pilih lisensi terlebih dahulu.');
            return false;
        }
        
        // Show loading
        $('#claimBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Memproses...'
        );
    });
});

function checkFormValidity() {
    const confirmationChecked = $('#confirmationCheck').is(':checked');
    const hasLicense = $('#selectedLicenseKey').val() !== '';
    
    $('#claimBtn').prop('disabled', !(confirmationChecked && hasLicense));
}
</script>
@endsection