@extends('layouts.admin')

@section('title', 'Pending Warranty Claims - e-License')
@section('page-title', 'Pending Warranty Claims')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.warranty.index') }}">Warranty Claims</a>
</li>
<li class="breadcrumb-item active">Pending</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.warranty.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to All Claims
</a>
@endsection

@section('content')
@if($pendingClaims->isEmpty())
<div class="card">
    <div class="card-body">
        <div class="text-center text-muted py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h4>No Pending Warranty Claims</h4>
            <p>All warranty claims have been processed.</p>
        </div>
    </div>
</div>
@else
<!-- Pending Claims Table -->
<div class="card">
    <div class="card-header bg-warning text-white">
        <h5 class="mb-0">
            <i class="fas fa-clock"></i> Pending Manual Approval
            <span class="badge bg-danger">{{ $pendingClaims->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Original License</th>
                        <th>Warranty Valid Until</th>
                        <th>Activation Attempts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingClaims as $claim)
                    <tr>
                        <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <strong>{{ $claim->userLicense->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $claim->userLicense->user->username }}</small>
                        </td>
                        <td>{{ $claim->userLicense->order->product->name }}</td>
                        <td>
                            <code class="small">{{ $claim->userLicense->license_key_formatted }}</code>
                            <br>
                            <small class="text-muted">Order: {{ $claim->userLicense->order->order_number }}</small>
                        </td>
                        <td>
                            @if($claim->userLicense->is_warranty_valid)
                                <span class="badge bg-success">
                                    {{ $claim->userLicense->warranty_until->format('d/m/Y') }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ $claim->userLicense->warranty_until->diffForHumans() }}
                                </small>
                            @else
                                <span class="badge bg-danger">Expired</span>
                            @endif
                        </td>
                        <td>{{ $claim->userLicense->activation_attempts }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.warranty.show', $claim) }}" 
                                   class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <form method="POST" action="{{ route('admin.warranty.approve', $claim) }}" 
                                      class="d-inline" onsubmit="return confirm('Approve this warranty claim?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="showRejectForm({{ $claim->id }})" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $pendingClaims->links() }}
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
@endif
@endsection

@section('scripts')
<script>
function showRejectForm(claimId) {
    // Set form action
    $('#rejectForm').attr('action', '{{ route("admin.warranty.reject", ":id") }}'.replace(':id', claimId));
    
    // Clear previous form data
    $('#rejectForm textarea').val('');
    
    // Show modal
    $('#rejectModal').modal('show');
}

$(document).ready(function() {
    // Auto-refresh page every 30 seconds for pending claims
    setTimeout(function() {
        location.reload();
    }, 30000);
});
</script>
@endsection