@extends('layouts.admin')

@section('title', 'Warranty Claims Management - e-License')
@section('page-title', 'Warranty Claims Management')

@section('breadcrumbs')
<li class="breadcrumb-item active">Warranty Claims</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.warranty.pending') }}" class="btn btn-warning">
    <i class="fas fa-clock"></i> Pending Claims
    @if($stats['pending'] > 0)
    <span class="badge bg-danger">{{ $stats['pending'] }}</span>
    @endif
</a>
@endsection

@section('content')
<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Claims</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Pending</h6>
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Auto Approved</h6>
                        <h3 class="mb-0">{{ $stats['auto_approved'] }}</h3>
                    </div>
                    <i class="fas fa-robot fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Today</h6>
                        <h3 class="mb-0">{{ $stats['today'] }}</h3>
                    </div>
                    <i class="fas fa-calendar-day fa-2x opacity-50"></i>
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
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="auto" {{ request('type') == 'auto' ? 'selected' : '' }}>Auto Approved</option>
                    <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Customer name, license key..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Warranty Claims Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Original License</th>
                        <th>New License</th>
                        <th>Reason</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warrantyClaims as $claim)
                    <tr>
                        <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <strong>{{ $claim->userLicense->user->name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ $claim->userLicense->user->username ?? '' }}</small>
                        </td>
                        <td>
                            <code class="small">{{ Str::limit($claim->userLicense->license_key_formatted ?? 'N/A', 15) }}</code>
                            <br>
                            <small class="text-muted">Order: {{ $claim->userLicense->order->order_number ?? '' }}</small>
                        </td>
                        <td>
                            @if($claim->replacementLicense)
                                <code class="small">{{ Str::limit($claim->replacementLicense->license_key_formatted, 15) }}</code>
                                <br>
                                <small class="text-success">
                                    <i class="fas fa-check-circle"></i> Replaced
                                </small>
                            @else
                                <span class="text-warning">Processing...</span>
                            @endif
                        </td>
                        <td>{{ $claim->reason }}</td>
                        <td>
                            @if($claim->auto_approved)
                                <span class="badge bg-success">Auto</span>
                            @else
                                <span class="badge bg-info">Manual</span>
                            @endif
                        </td>
                        <td>
                            @if($claim->approved_at)
                                <span class="badge bg-success">Approved</span>
                                <br>
                                <small class="text-muted">{{ $claim->approved_at->format('d/m/Y') }}</small>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.warranty.show', $claim) }}" 
                                   class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if(!$claim->approved_at && !$claim->auto_approved)
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" action="{{ route('admin.warranty.approve', $claim) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="showRejectForm({{ $claim->id }})">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
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
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="rejectForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times text-danger"></i> Reject Warranty Claim
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Rejecting a warranty claim will notify the user 
                        and cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" 
                                  placeholder="Enter reason for rejection..." required></textarea>
                        <div class="form-text">This reason will be shown to the user.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showRejectForm(claimId) {
    // Set form action
    $('#rejectForm').attr('action', '{{ route("admin.warranty.reject", ":id") }}'.replace(':id', claimId));
    
    // Show modal
    $('#rejectModal').modal('show');
}

$(document).ready(function() {
    // Initialize DataTable
    $('.datatable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>
@endsection
