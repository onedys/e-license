@extends('layouts.admin')

@section('title', 'Warranty Claim Details - e-License')
@section('page-title', 'Warranty Claim Details')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.warranty.index') }}">Warranty Claims</a>
</li>
<li class="breadcrumb-item active">Details</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.warranty.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <!-- Claim Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Claim Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Claim Date</strong></td>
                        <td>{{ $warrantyExchange->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Claim Type</strong></td>
                        <td>
                            @if($warrantyExchange->auto_approved)
                                <span class="badge bg-success">Auto Approved</span>
                            @else
                                <span class="badge bg-info">Manual</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            @if($warrantyExchange->approved_at)
                                <span class="badge bg-success">Approved</span>
                                on {{ $warrantyExchange->approved_at->format('d/m/Y H:i') }}
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Reason</strong></td>
                        <td>{{ $warrantyExchange->reason }}</td>
                    </tr>
                    @if($warrantyExchange->admin)
                    <tr>
                        <td><strong>Processed By</strong></td>
                        <td>{{ $warrantyExchange->admin->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Customer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Name</strong></td>
                        <td>{{ $warrantyExchange->userLicense->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Username</strong></td>
                        <td>{{ $warrantyExchange->userLicense->user->username }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $warrantyExchange->userLicense->user->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone</strong></td>
                        <td>{{ $warrantyExchange->userLicense->user->phone ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Original License -->
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-key"></i> Original License (Blocked)</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>License Key</strong></td>
                        <td>
                            <code>{{ $warrantyExchange->userLicense->license_key_formatted }}</code>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Product</strong></td>
                        <td>{{ $warrantyExchange->userLicense->order->product->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Order Number</strong></td>
                        <td>{{ $warrantyExchange->userLicense->order->order_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Purchase Date</strong></td>
                        <td>{{ $warrantyExchange->userLicense->order->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Warranty Until</strong></td>
                        <td>
                            @if($warrantyExchange->userLicense->is_warranty_valid)
                                <span class="badge bg-success">
                                    {{ $warrantyExchange->userLicense->warranty_until->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="badge bg-danger">Expired</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Activation Attempts</strong></td>
                        <td>{{ $warrantyExchange->userLicense->activation_attempts }}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Activation</strong></td>
                        <td>
                            @if($warrantyExchange->userLicense->activated_at)
                                {{ $warrantyExchange->userLicense->activated_at->format('d/m/Y H:i') }}
                            @else
                                Never
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Replacement License -->
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-key"></i> Replacement License</h5>
            </div>
            <div class="card-body">
                @if($warrantyExchange->replacementLicense)
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>License Key</strong></td>
                        <td>
                            <code>{{ $warrantyExchange->replacementLicense->license_key_formatted }}</code>
                            <button class="btn btn-sm btn-outline-success ms-2" 
                                    onclick="copyToClipboard('{{ $warrantyExchange->replacementLicense->license_key_formatted }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <span class="badge bg-{{ $warrantyExchange->replacementLicense->status == 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($warrantyExchange->replacementLicense->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Assigned Date</strong></td>
                        <td>{{ $warrantyExchange->replacementLicense->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Warranty Period</strong></td>
                        <td>
                            Follows original warranty: 
                            {{ $warrantyExchange->replacementLicense->warranty_until->format('d/m/Y') }}
                        </td>
                    </tr>
                </table>
                @elseif($warrantyExchange->newLicensePool)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>License from Pool:</strong><br>
                    <code>{{ $warrantyExchange->newLicensePool->getPlainAttribute('license_key') }}</code>
                    <br>
                    <small>Status: 
                        <span class="badge bg-{{ $warrantyExchange->newLicensePool->status == 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($warrantyExchange->newLicensePool->status) }}
                        </span>
                    </small>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No replacement license assigned yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
@if(!$warrantyExchange->approved_at && !$warrantyExchange->auto_approved)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-2">
                <form method="POST" action="{{ route('admin.warranty.approve', $warrantyExchange) }}" 
                      onsubmit="return confirm('Approve this warranty claim?')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-check"></i> Approve Claim
                    </button>
                </form>
            </div>
            <div class="col-md-4 mb-2">
                <button type="button" class="btn btn-danger w-100" 
                        onclick="showRejectForm()">
                    <i class="fas fa-times"></i> Reject Claim
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Reject Modal -->
@if(!$warrantyExchange->approved_at && !$warrantyExchange->auto_approved)
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.warranty.reject', $warrantyExchange) }}">
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
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    }, function() {
        alert('Failed to copy. Please copy manually.');
    });
}

function showRejectForm() {
    $('#rejectModal').modal('show');
}
</script>
@endsection