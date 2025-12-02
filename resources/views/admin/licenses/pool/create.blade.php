@extends('layouts.admin')

@section('title', 'Upload Licenses - e-License')
@section('page-title', 'Upload Licenses')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.license-pool.index') }}">License Pool</a>
</li>
<li class="breadcrumb-item active">Upload</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.license-pool.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-upload"></i> Bulk Upload License Keys</h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.license-pool.store') }}" id="uploadForm">
                    @csrf
                    
                    <div class="mb-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Format yang didukung:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Satu lisensi per baris</li>
                                <li>Format dengan atau tanpa dash: <code>XXXXX-XXXXX-XXXXX-XXXXX-XXXXX</code></li>
                                <li>Maksimal 100 lisensi per batch</li>
                                <li>Lisensi akan divalidasi otomatis via PIDKey API</li>
                                <li>Hanya lisensi valid (errorcode 0xC004C008/Online Key) yang akan disimpan</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="product_id" class="form-label">
                            <i class="fas fa-box"></i> Product <span class="text-danger">*</span>
                        </label>
                        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="keys" class="form-label">
                            <i class="fas fa-key"></i> License Keys <span class="text-danger">*</span>
                        </label>
                        <textarea name="keys" id="keys" 
                                  class="form-control @error('keys') is-invalid @enderror" 
                                  rows="15" 
                                  placeholder="Paste license keys here, one per line..." 
                                  required>{{ old('keys') }}</textarea>
                        @error('keys')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <div class="form-text">
                            <i class="fas fa-lightbulb"></i> 
                            <strong>Contoh format:</strong>
                            <pre class="mt-2 bg-light p-2 small">
GDW83-6NFDW-Y87YH-CJV2H-7QW92
M2GHF-VXN22-WFH64-2HPH9-BKM3P
HCBVN-6M7F7-4YHY3-68K73-8K892
Y2KFR-HXNH9-CXYFR-F6P6W-DGPX2</pre>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Proses upload membutuhkan waktu (1-2 detik per lisensi)</li>
                                <li>Jangan tutup halaman selama proses berlangsung</li>
                                <li>Lisensi yang sudah ada di database akan diabaikan</li>
                                <li>Lisensi invalid akan ditampilkan setelah proses selesai</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn">
                            <i class="fas fa-upload"></i> Upload & Validate
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-history"></i>
                    <a href="{{ route('admin.license-pool.invalid-keys') }}" class="text-decoration-none">
                        View previous invalid keys
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
    // Form submission
    $('#uploadForm').submit(function(e) {
        const keys = $('#keys').val().trim();
        const lines = keys.split('\n').filter(line => line.trim() !== '');
        
        if (lines.length > 100) {
            e.preventDefault();
            alert('Maksimal 100 lisensi per batch. Anda memasukkan ' + lines.length + ' lisensi.');
            return false;
        }
        
        if (lines.length === 0) {
            e.preventDefault();
            alert('Masukkan lisensi terlebih dahulu.');
            return false;
        }
        
        // Show loading
        $('#uploadBtn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Processing...'
        );
    });
});
</script>
@endsection