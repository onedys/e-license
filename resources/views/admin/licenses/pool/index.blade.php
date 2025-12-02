@extends('layouts.admin')

@section('title', 'License Pool Management - e-License')
@section('page-title', 'License Pool Management')

@section('breadcrumbs')
<li class="breadcrumb-item active">License Pool</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.license-pool.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Licenses
    </a>
    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
        <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkRevalidateModal">
                <i class="fas fa-sync-alt"></i> Bulk Revalidate
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.license-pool.export') }}">
                <i class="fas fa-download"></i> Export to CSV
            </a>
        </li>
    </ul>
</div>
@endsection

@section('content')
<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <i class="fas fa-database fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Active</h6>
                        <h3 class="mb-0">{{ $stats['active'] }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Blocked</h6>
                        <h3 class="mb-0">{{ $stats['blocked'] }}</h3>
                    </div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Invalid</h6>
                        <h3 class="mb-0">{{ $stats['invalid'] }}</h3>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
    @csrf
            <div class="col-md-4">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                    <option value="invalid" {{ request('status') == 'invalid' ? 'selected' : '' }}>Invalid</option>
                    <option value="exhausted" {{ request('status') == 'exhausted' ? 'selected' : '' }}>Exhausted</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="License key, error code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- License Pool Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Error Code</th>
                        <th>Validated</th>
                        <th>Assignments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($licensePools as $license)
                    <tr>
                        <td>
                            <code class="small">{{ $license->getPlainAttribute('license_key') }}</code>
                            <br>
                            <small class="text-muted">{{ $license->keyname_with_dash }}</small>
                        </td>
                        <td>{{ $license->product->name }}</td>
                        <td>
                            <span class="badge bg-{{ $license->status == 'active' ? 'success' : ($license->status == 'blocked' ? 'danger' : ($license->status == 'invalid' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst($license->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ in_array($license->errorcode, ['0xC004C008', 'Online Key']) ? 'success' : 'danger' }}">
                                {{ $license->errorcode }}
                            </span>
                        </td>
                        <td>
                            @if($license->last_validated_at)
                                {{ $license->last_validated_at->diffForHumans() }}
                                <br>
                                <small class="text-muted">({{ $license->validation_count }}x)</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            {{ $license->userLicenses->count() }}
                            @if($license->userLicenses->count() > 0)
                            <br>
                            <small class="text-muted">
                                Active: {{ $license->userLicenses->where('status', 'active')->count() }}
                            </small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.license-pool.show', $license) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-warning revalidate-btn" 
                                        data-id="{{ $license->id }}" title="Revalidate">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" action="{{ route('admin.license-pool.update', $license) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-check text-success"></i> Mark as Active
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.license-pool.update', $license) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="blocked">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-ban text-danger"></i> Mark as Blocked
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.license-pool.destroy', $license) }}" 
                                                  onsubmit="return confirmDelete('Hapus lisensi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $licensePools->links() }}
        </div>
    </div>
</div>

<!-- Bulk Revalidate Modal -->
<div class="modal fade" id="bulkRevalidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.license-pool.bulk-revalidate') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sync-alt"></i> Bulk Revalidation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Proses ini akan memvalidasi ulang semua lisensi aktif 
                        untuk produk yang dipilih. Mungkin memerlukan waktu beberapa menit.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt"></i> Start Revalidation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Revalidate button click
    $('.revalidate-btn').click(function() {
        const licenseId = $(this).data('id');
        const btn = $(this);
        
        if (!confirm('Revalidate license key?')) return;
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Fix: Gunakan route dengan parameter yang benar
        $.ajax({
            url: '{{ route("admin.license-pool.revalidate", ":id") }}'.replace(':id', licenseId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'POST'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Error revalidating license: ' + xhr.responseJSON?.message || 'Unknown error');
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
            }
        });
    });
});
</script>
@endsection
