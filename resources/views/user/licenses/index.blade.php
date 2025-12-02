@extends('layouts.app')

@section('title', 'My Licenses - e-License')

@section('content')
<div class="row">
    <div class="col">
        <h1><i class="fas fa-key"></i> My Licenses</h1>
        <p class="lead">All licenses assigned to your account</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select name="product" class="form-select" onchange="this.form.submit()">
                    <option value="">All Products</option>
                    @foreach($licenses->pluck('order.product')->unique() as $product)
                        @if($product)
                            <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="License key..." 
                       value="{{ request('search') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('user.licenses.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Licenses</h6>
                        <h3 class="mb-0">{{ $licenses->total() }}</h3>
                    </div>
                    <i class="fas fa-key fa-2x opacity-50"></i>
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
                        <h3 class="mb-0">{{ $licenses->where('status', 'active')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
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
                        <h3 class="mb-0">{{ $licenses->where('status', 'pending')->count() }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
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
                        <h3 class="mb-0">{{ $licenses->where('status', 'blocked')->count() }}</h3>
                    </div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Licenses Table -->
<div class="card">
    <div class="card-body">
        @if($licenses->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-key fa-3x mb-3"></i>
                <h5>No licenses found</h5>
                <p>You don't have any licenses yet. <a href="{{ route('products.index') }}">Buy your first license</a></p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>License Key</th>
                            <th>Product</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Garansi</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($licenses as $license)
                        <tr>
                            <td>
                                <code class="small">{{ $license->license_key_formatted }}</code>
                                <br>
                                @if($license->confirmation_id_formatted)
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> CID: {{ Str::limit($license->confirmation_id_formatted, 15) }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $license->order->product->name }}</td>
                            <td>{{ $license->order->order_number }}</td>
                            <td>
                                @if($license->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($license->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($license->status === 'blocked')
                                    <span class="badge bg-danger">Blocked</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($license->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($license->is_warranty_valid)
                                    <small class="text-success">
                                        <i class="fas fa-shield-alt"></i> {{ $license->warranty_until->diffForHumans() }}
                                    </small>
                                @else
                                    <small class="text-muted">Expired</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('user.licenses.show', $license->id) }}" 
                                       class="btn btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($license->status === 'pending')
                                        <a href="{{ route('user.activation.form') }}" 
                                           class="btn btn-outline-success" title="Activate">
                                            <i class="fas fa-play-circle"></i>
                                        </a>
                                    @endif
                                    @if($license->can_claim_warranty)
                                        <a href="{{ route('user.warranty.claim.form') }}" 
                                           class="btn btn-outline-warning" title="Claim Warranty">
                                            <i class="fas fa-shield-alt"></i>
                                        </a>
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
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-3">
    <a href="{{ route('user.activation.form') }}" class="btn btn-primary">
        <i class="fas fa-play-circle"></i> Activate License
    </a>
    <a href="{{ route('user.warranty.claim.form') }}" class="btn btn-warning">
        <i class="fas fa-shield-alt"></i> Claim Warranty
    </a>
    <a href="{{ route('products.index') }}" class="btn btn-success">
        <i class="fas fa-store"></i> Buy More
    </a>
</div>
@endsection