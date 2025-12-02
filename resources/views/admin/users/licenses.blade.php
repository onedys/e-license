@extends('layouts.admin')

@section('title', 'User Licenses - e-License')
@section('page-title', 'User Licenses')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.users.index') }}">Users</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
</li>
<li class="breadcrumb-item active">Licenses</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to User
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-key"></i> Licenses for {{ $user->name }}
            <span class="badge bg-primary">{{ $licenses->total() }} licenses</span>
        </h5>
    </div>
    <div class="card-body">
        @if($licenses->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-key fa-3x mb-3"></i>
                <h5>No licenses found</h5>
                <p>This user hasn't purchased any licenses yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>License Key</th>
                            <th>Product</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Installation ID</th>
                            <th>Confirmation ID</th>
                            <th>Activated</th>
                            <th>Garansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($licenses as $license)
                        <tr>
                            <td>
                                <code class="small">{{ $license->license_key_formatted }}</code>
                            </td>
                            <td>{{ $license->order->product->name }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $license->order) }}" 
                                   class="text-decoration-none">
                                    {{ $license->order->order_number }}
                                </a>
                            </td>
                            <td>
                                @if($license->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($license->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($license->status === 'blocked')
                                    <span class="badge bg-danger">Blocked</span>
                                @elseif($license->status === 'replaced')
                                    <span class="badge bg-secondary">Replaced</span>
                                @endif
                            </td>
                            <td>
                                @if($license->installation_id)
                                    <small class="text-muted">
                                        {{ Str::limit($license->getPlainAttribute('installation_id'), 15) }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($license->confirmation_id)
                                    <code class="small">{{ Str::limit($license->confirmation_id_formatted, 20) }}</code>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($license->activated_at)
                                    {{ $license->activated_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">{{ $license->activated_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Not yet</span>
                                @endif
                            </td>
                            <td>
                                @if($license->warranty_until)
                                    @if($license->warranty_until->isFuture())
                                        <span class="badge bg-success">
                                            {{ $license->warranty_until->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection