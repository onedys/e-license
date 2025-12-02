@extends('layouts.admin')

@section('title', 'License Details - e-License')
@section('page-title', 'License Details')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.license-pool.index') }}">License Pool</a>
</li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.license-pool.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-key"></i> License Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>License Key</strong></td>
                        <td>
                            <code>{{ $licensePool->getPlainAttribute('license_key') }}</code>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $licensePool->getPlainAttribute('license_key') }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Product</strong></td>
                        <td>{{ $licensePool->product->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <span class="badge bg-{{ $licensePool->status == 'active' ? 'success' : ($licensePool->status == 'blocked' ? 'danger' : ($licensePool->status == 'invalid' ? 'warning' : 'secondary')) }}">
                                {{ ucfirst($licensePool->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Error Code</strong></td>
                        <td>
                            <span class="badge bg-{{ in_array($licensePool->errorcode, ['0xC004C008', 'Online Key']) ? 'success' : 'danger' }}">
                                {{ $licensePool->errorcode }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Product Name (API)</strong></td>
                        <td>{{ $licensePool->product_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Is Retail</strong></td>
                        <td>{{ $licensePool->is_retail ? 'Yes' : 'No' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Remaining</strong></td>
                        <td>{{ $licensePool->remaining ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Blocked</strong></td>
                        <td>{{ $licensePool->blocked == 1 ? 'Yes' : ($licensePool->blocked == -1 ? 'No' : 'Unknown') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created</strong></td>
                        <td>{{ $licensePool->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Validated</strong></td>
                        <td>
                            @if($licensePool->last_validated_at)
                                {{ $licensePool->last_validated_at->format('d/m/Y H:i') }}
                                <br>
                                <small class="text-muted">({{ $licensePool->validation_count }} validations)</small>
                            @else
                                Never
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Usage Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="mb-0">{{ $usageStats['total_assignments'] }}</h2>
                                <small class="text-muted">Total Assignments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h2 class="mb-0">{{ $usageStats['active_assignments'] }}</h2>
                                <small>Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h2 class="mb-0">{{ $usageStats['pending_assignments'] }}</h2>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($usageStats['total_assignments'] > 0)
                <div class="mt-3">
                    <h6>Assigned to Users:</h6>
                    <div class="list-group">
                        @foreach($licensePool->userLicenses as $userLicense)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $userLicense->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Order: {{ $userLicense->order->order_number }} | 
                                        Status: 
                                        <span class="badge bg-{{ $userLicense->status == 'active' ? 'success' : ($userLicense->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($userLicense->status) }}
                                        </span>
                                    </small>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        {{ $userLicense->created_at->format('d/m/Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-users-slash fa-2x mb-3"></i>
                    <p>This license has not been assigned to any user yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <button class="btn btn-warning w-100" onclick="revalidateLicense({{ $licensePool->id }})">
                    <i class="fas fa-sync-alt"></i> Revalidate
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.license-pool.update', $licensePool) }}" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> Mark Active
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.license-pool.update', $licensePool) }}" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="blocked">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-ban"></i> Mark Blocked
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form method="POST" action="{{ route('admin.license-pool.destroy', $licensePool) }}" 
                      onsubmit="return confirmDelete('Delete this license permanently?')" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    }, function() {
        alert('Failed to copy. Please copy manually.');
    });
}

function revalidateLicense(licenseId) {
    if (!confirm('Revalidate this license key?')) return;
    
    $.ajax({
        url: '{{ route("admin.license-pool.revalidate", ":id") }}'.replace(':id', licenseId),
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error revalidating license: ' + xhr.responseJSON?.message || 'Unknown error');
        }
    });
}
</script>
@endsection
