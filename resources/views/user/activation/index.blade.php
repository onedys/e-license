@extends('layouts.app')

@section('title', 'Aktivasi Lisensi - e-License')

@section('styles')
<style>
    .license-option {
        cursor: pointer;
        transition: all 0.3s;
    }
    .license-option:hover {
        background-color: #f8f9fa;
    }
    .license-option.selected {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .installation-id-info {
        font-family: monospace;
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 4px solid #0d6efd;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-play-circle"></i> Aktivasi Lisensi</h4>
            </div>
            
            <div class="card-body">
                @if($pendingLicenses->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tidak ada lisensi yang perlu diaktivasi.</strong><br>
                        Beli lisensi terlebih dahulu atau cek lisensi Anda di dashboard.
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('products.index') }}" class="btn btn-primary me-2">
                            <i class="fas fa-store"></i> Beli Lisensi
                        </a>
                        <a href="{{ route('user.licenses.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-key"></i> Lihat Lisensi Saya
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('user.activation.process') }}" id="activationForm">
                        @csrf
                        
                        <!-- Step 1: Pilih Lisensi -->
                        <div class="mb-4">
                            <h5><i class="fas fa-key"></i> Step 1: Pilih Lisensi</h5>
                            <p class="text-muted">Pilih lisensi yang ingin diaktivasi</p>
                            
                            <div class="list-group" id="licenseList">
                                @foreach($pendingLicenses as $license)
                                <div class="list-group-item license-option" 
                                     data-id="{{ $license['id'] }}"
                                     data-license-key="{{ $license['license_key'] }}"
                                     data-order-number="{{ $license['order_number'] }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="license_id" 
                                               id="license_{{ $license['id'] }}"
                                               value="{{ $license['id'] }}">
                                        <label class="form-check-label" for="license_{{ $license['id'] }}">
                                            <strong>{{ $license['license_key'] }}</strong><br>
                                            <small class="text-muted">
                                                Order: {{ $license['order_number'] }} | 
                                                Produk: {{ $license['product_name'] }}
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <input type="hidden" name="license_key" id="selectedLicenseKey">
                            <input type="hidden" name="order_number" id="selectedOrderNumber">
                        </div>
                        
                        <!-- Step 2: Input Installation ID -->
                        <div class="mb-4">
                            <h5><i class="fas fa-id-card"></i> Step 2: Input Installation ID</h5>
                            <p class="text-muted">Masukkan Installation ID dari komputer Anda</p>
                            
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Perhatian:</strong> Setiap lisensi membutuhkan Installation ID yang berbeda.
                                Installation ID terkait dengan hardware komputer.
                            </div>
                            
                            <div class="mb-3">
                                <label for="installation_id" class="form-label">
                                    Installation ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('installation_id') is-invalid @enderror" 
                                       id="installation_id" 
                                       name="installation_id" 
                                       placeholder="Masukkan 54 atau 63 digit angka"
                                       value="{{ old('installation_id') }}"
                                       required>
                                @error('installation_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> 
                                    Installation ID didapat dari komputer yang akan diaktivasi.
                                    Format: 54 digit (Windows 7/8) atau 63 digit (Windows 10/11).
                                </div>
                            </div>
                            
                            <div class="installation-id-info mb-3">
                                <small>
                                    <strong>Contoh Installation ID:</strong><br>
                                    54 digit: <code>000311278450147054800702369490197125650261384183423558151438486</code><br>
                                    63 digit: <code>123456789012345678901234567890123456789012345678901234567890123</code>
                                </small>
                            </div>
                            
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-3" 
                                    onclick="checkInstallationId()">
                                <i class="fas fa-check"></i> Cek Ketersediaan Installation ID
                            </button>
                            
                            <div id="installationIdCheckResult"></div>
                        </div>
                        
                        <!-- Step 3: Konfirmasi -->
                        <div class="mb-4">
                            <h5><i class="fas fa-check-circle"></i> Step 3: Konfirmasi</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Informasi Aktivasi:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Kuota aktivasi akan berkurang jika berhasil atau "Key Blocked"</li>
                                    <li>Error jaringan/server TIDAK mengurangi kuota</li>
                                    <li>Proses aktivasi membutuhkan waktu beberapa detik</li>
                                    <li>Confirmation ID akan ditampilkan setelah berhasil</li>
                                </ul>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmationCheck" required>
                                <label class="form-check-label" for="confirmationCheck">
                                    Saya mengerti bahwa Installation ID harus berbeda untuk setiap lisensi
                                    dan proses ini akan mengurangi kuota aktivasi saya.
                                </label>
                            </div>
                        </div>
                        
                        @error('activation')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                        
                        @error('license_key')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                        
                        @error('order_number')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="activateBtn" disabled>
                                <i class="fas fa-play-circle"></i> Aktivasi Sekarang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-question-circle"></i>
                    <strong>Butuh bantuan?</strong> Hubungi admin jika ada masalah dengan aktivasi.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk hasil aktivasi -->
<div class="modal fade" id="activationResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hasil Aktivasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activationResultContent">
                <!-- Hasil akan diisi via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="copyConfirmationId()">
                    <i class="fas fa-copy"></i> Salin Confirmation ID
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedLicenseId = null;
let selectedLicenseKey = null;
let selectedOrderNumber = null;
let confirmationId = null;

$(document).ready(function() {
    // License selection
    $('.license-option').click(function() {
        $('.license-option').removeClass('selected');
        $(this).addClass('selected');
        
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
        
        selectedLicenseId = $(this).data('id');
        selectedLicenseKey = $(this).data('license-key');
        selectedOrderNumber = $(this).data('order-number');
        
        // Update hidden fields
        $('#selectedLicenseKey').val(selectedLicenseKey);
        $('#selectedOrderNumber').val(selectedOrderNumber);
        
        // Update form fields for better UX
        $('#license_key_display').val(selectedLicenseKey);
        $('#order_number_display').val(selectedOrderNumber);
        
        checkFormValidity();
    });
    
    // Auto-select if there's only one license
    if ($('.license-option').length === 1) {
        $('.license-option').first().click();
    }
    
    // Form validation
    $('#installation_id, #confirmationCheck').on('input change', checkFormValidity);
    
    // Form submission
    $('#activationForm').submit(function(e) {
        if (!selectedLicenseId) {
            e.preventDefault();
            alert('Pilih lisensi terlebih dahulu.');
            return false;
        }
        
        // Validate installation ID format
        const installationId = $('#installation_id').val().trim();
        const digitsOnly = installationId.replace(/\D/g, '');
        
        if (![54, 63].includes(digitsOnly.length)) {
            e.preventDefault();
            alert('Installation ID harus 54 atau 63 digit angka.');
            return false;
        }
        
        // Show loading
        $('#activateBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Memproses...'
        );
    });
});

function checkFormValidity() {
    const installationId = $('#installation_id').val().trim();
    const confirmationChecked = $('#confirmationCheck').is(':checked');
    const hasLicense = selectedLicenseId !== null;
    
    const isValid = hasLicense && 
                    installationId.length > 0 && 
                    confirmationChecked;
    
    $('#activateBtn').prop('disabled', !isValid);
}

function checkInstallationId() {
    const installationId = $('#installation_id').val().trim();
    
    if (!installationId) {
        $('#installationIdCheckResult').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Masukkan Installation ID terlebih dahulu.
            </div>
        `);
        return;
    }
    
    // Validate format
    const digitsOnly = installationId.replace(/\D/g, '');
    if (![54, 63].includes(digitsOnly.length)) {
        $('#installationIdCheckResult').html(`
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> 
                Format tidak valid. Installation ID harus 54 atau 63 digit angka.
            </div>
        `);
        return;
    }
    
    // Show loading
    $('#installationIdCheckResult').html(`
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin"></i> Mengecek ketersediaan...
        </div>
    `);
    
    // AJAX check
    $.ajax({
        url: '{{ route("user.activation.checkInstallationId") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            installation_id: installationId
        },
        success: function(response) {
            if (response.valid) {
                $('#installationIdCheckResult').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> ${response.message}
                    </div>
                `);
            } else {
                $('#installationIdCheckResult').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> ${response.message}
                    </div>
                `);
            }
        },
        error: function() {
            $('#installationIdCheckResult').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Gagal memeriksa. Pastikan koneksi internet stabil.
                </div>
            `);
        }
    });
}

function showActivationResult(success, message, cid = null) {
    confirmationId = cid;
    
    let html = '';
    if (success) {
        html = `
            <div class="text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Aktivasi Berhasil!</h5>
                <p>${message}</p>
                <div class="alert alert-success">
                    <strong>Confirmation ID:</strong><br>
                    <code id="confirmationIdText" class="d-block p-2 mt-2">${cid}</code>
                </div>
                <p>Salin Confirmation ID untuk aktivasi Windows/Office.</p>
            </div>
        `;
    } else {
        html = `
            <div class="text-center">
                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                <h5>Aktivasi Gagal</h5>
                <div class="alert alert-danger">
                    ${message}
                </div>
                <p>Silakan coba lagi atau hubungi admin jika masalah berlanjut.</p>
            </div>
        `;
    }
    
    $('#activationResultContent').html(html);
    $('#activationResultModal').modal('show');
}

function copyConfirmationId() {
    if (!confirmationId) return;
    
    navigator.clipboard.writeText(confirmationId).then(function() {
        alert('Confirmation ID berhasil disalin!');
    }, function() {
        alert('Gagal menyalin. Silakan salin manual.');
    });
}
</script>
@endsection