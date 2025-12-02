@extends('layouts.admin')

@section('title', 'User Management - e-License')
@section('page-title', 'User Management')

@section('breadcrumbs')
<li class="breadcrumb-item active">Users</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-filter"></i> Filter
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="{{ route('admin.users.index') }}?is_admin=yes">
                <i class="fas fa-user-shield"></i> Admins Only
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.users.index') }}?is_admin=no">
                <i class="fas fa-users"></i> Users Only
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                <i class="fas fa-list"></i> All Users
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
                        <h6 class="mb-0">Total Users</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Admins</h6>
                        <h3 class="mb-0">{{ $stats['admins'] }}</h3>
                    </div>
                    <i class="fas fa-user-shield fa-2x opacity-50"></i>
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
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Active Today</h6>
                        <h3 class="mb-0">{{ $stats['active_today'] }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
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
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Username, name, or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">User Type</label>
                <select name="is_admin" class="form-select">
                    <option value="">All Users</option>
                    <option value="yes" {{ request('is_admin') == 'yes' ? 'selected' : '' }}>Admins Only</option>
                    <option value="no" {{ request('is_admin') == 'no' ? 'selected' : '' }}>Regular Users</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Licenses</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $user->username }}</small>
                        </td>
                        <td>
                            @if($user->email)
                                <small><i class="fas fa-envelope"></i> {{ $user->email }}</small><br>
                            @endif
                            @if($user->phone)
                                <small><i class="fas fa-phone"></i> {{ $user->phone }}</small>
                            @endif
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.orders', $user) }}" class="text-decoration-none">
                                {{ $user->orders()->count() }} orders
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.licenses', $user) }}" class="text-decoration-none">
                                {{ $user->licenses()->count() }} licenses
                            </a>
                        </td>
                        <td>
                            {{ $user->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#resetPasswordModal{{ $user->id }}">
                                                <i class="fas fa-key text-warning"></i> Reset Password
                                            </button>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="dropdown-item">
                                                    @if($user->is_admin)
                                                        <i class="fas fa-user text-secondary"></i> Make Regular User
                                                    @else
                                                        <i class="fas fa-user-shield text-danger"></i> Make Admin
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Reset Password Modal -->
                            <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reset Password for {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i>
                                                    User will need to use this new password to login.
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">New Password</label>
                                                    <input type="password" name="new_password" class="form-control" 
                                                           required minlength="8">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Confirm Password</label>
                                                    <input type="password" name="new_password_confirmation" 
                                                           class="form-control" required minlength="8">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-key"></i> Reset Password
                                                </button>
                                            </div>
                                        </form>
                                    </div>
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
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
