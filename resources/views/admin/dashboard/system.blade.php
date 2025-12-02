@extends('layouts.admin')

@section('title', 'System Information - e-License')
@section('page-title', 'System Information')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active">System Info</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-server"></i> System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><strong>Laravel Version</strong></td>
                        <td>{{ $info['laravel_version'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td>{{ $info['php_version'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Server Software</strong></td>
                        <td>{{ $info['server_software'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Database Driver</strong></td>
                        <td>{{ $info['database_driver'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Timezone</strong></td>
                        <td>{{ $info['timezone'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Environment</strong></td>
                        <td>
                            <span class="badge bg-{{ $info['environment'] == 'production' ? 'success' : 'warning' }}">
                                {{ $info['environment'] }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Debug Mode</strong></td>
                        <td>
                            <span class="badge bg-{{ $info['debug_mode'] == 'Enabled' ? 'danger' : 'success' }}">
                                {{ $info['debug_mode'] }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Maintenance Mode</strong></td>
                        <td>
                            <span class="badge bg-{{ $info['maintenance_mode'] == 'Enabled' ? 'warning' : 'success' }}">
                                {{ $info['maintenance_mode'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-database"></i> Database Information</h5>
            </div>
            <div class="card-body">
                @php
                    $tables = [
                        'users' => 'Total Users',
                        'products' => 'Products',
                        'orders' => 'Orders',
                        'license_pools' => 'License Pool',
                        'user_licenses' => 'User Licenses',
                        'warranty_exchanges' => 'Warranty Claims',
                    ];
                @endphp
                
                <table class="table table-sm">
                    @foreach($tables as $table => $label)
                    @php
                        $count = DB::table($table)->count();
                    @endphp
                    <tr>
                        <td width="60%"><strong>{{ $label }}</strong></td>
                        <td class="text-end">{{ number_format($count) }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Application Stats</h5>
            </div>
            <div class="card-body">
                @php
                    $today = \Carbon\Carbon::today();
                    $stats = [
                        'Today Orders' => \App\Models\Order::whereDate('created_at', $today)->count(),
                        'Today Sales' => \App\Models\Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
                        'Today Activations' => \App\Models\UserLicense::whereDate('activated_at', $today)->count(),
                        'Pending Orders' => \App\Models\Order::where('payment_status', 'pending')->count(),
                        'Active Licenses' => \App\Models\UserLicense::active()->count(),
                        'Blocked Licenses' => \App\Models\UserLicense::blocked()->count(),
                    ];
                @endphp
                
                <table class="table table-sm">
                    @foreach($stats as $label => $value)
                    <tr>
                        <td width="60%"><strong>{{ $label }}</strong></td>
                        <td class="text-end">
                            @if(str_contains($label, 'Sales'))
                                Rp {{ number_format($value, 0, ',', '.') }}
                            @else
                                {{ number_format($value) }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-tools"></i> System Tools</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-outline-primary w-100" onclick="clearCache()">
                            <i class="fas fa-broom"></i> Clear Cache
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-outline-info w-100" onclick="optimizeApp()">
                            <i class="fas fa-cogs"></i> Optimize
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('admin.license-pool.bulk-revalidate') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-sync-alt"></i> Revalidate All
                        </a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-outline-danger w-100" onclick="showMaintenanceModal()">
                            <i class="fas fa-wrench"></i> Maintenance
                        </button>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Quick Links:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.license-pool.export') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download"></i> Export Licenses
                        </a>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-chart-bar"></i> Sales Report
                        </a>
                        <a href="/telescope" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-search"></i> Telescope
                        </a>
                        <a href="/horizon" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-tachometer-alt"></i> Horizon
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Warnings & Errors</h5>
            </div>
            <div class="card-body">
                @php
                    $warnings = [];
                    
                    // Check low stock
                    $lowStock = \App\Models\LicensePool::where('status', 'active')->count() < 10;
                    if ($lowStock) {
                        $warnings[] = 'License pool stock is low (< 10)';
                    }
                    
                    // Check pending warranty
                    $pendingWarranty = \App\Models\WarrantyExchange::where('auto_approved', false)
                        ->whereNull('approved_at')
                        ->count();
                    if ($pendingWarranty > 0) {
                        $warnings[] = "$pendingWarranty warranty claims pending";
                    }
                    
                    // Check failed payments
                    $failedPayments = \App\Models\Order::where('payment_status', 'failed')->count();
                    if ($failedPayments > 0) {
                        $warnings[] = "$failedPayments failed payments";
                    }
                    
                    // Check if app is in production but debug is on
                    if ($info['environment'] == 'production' && $info['debug_mode'] == 'Enabled') {
                        $warnings[] = 'Debug mode is enabled in production!';
                    }
                @endphp
                
                @if(empty($warnings))
                    <div class="text-center text-success py-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p>No warnings or errors detected</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($warnings as $warning)
                        <div class="list-group-item text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ $warning }}
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-wrench"></i> Maintenance Mode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Maintenance mode will display a custom message to all visitors.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" id="maintenanceMessage" rows="3" placeholder="Site is under maintenance..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Retry After (seconds)</label>
                    <input type="number" class="form-control" id="retryAfter" value="3600">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="enableMaintenance()">
                    <i class="fas fa-power-off"></i> Enable Maintenance
                </button>
                @if($info['maintenance_mode'] == 'Enabled')
                <button type="button" class="btn btn-success" onclick="disableMaintenance()">
                    <i class="fas fa-play"></i> Disable Maintenance
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function clearCache() {
    if (!confirm('Clear all cache?')) return;
    
    $.ajax({
        url: '{{ route("admin.system.clear-cache") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function() {
            alert('Cache cleared successfully!');
            location.reload();
        },
        error: function() {
            alert('Error clearing cache');
        }
    });
}

function optimizeApp() {
    if (!confirm('Optimize application?')) return;
    
    $.ajax({
        url: '{{ route("admin.system.optimize") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function() {
            alert('Application optimized!');
        },
        error: function() {
            alert('Error optimizing');
        }
    });
}

function showMaintenanceModal() {
    $('#maintenanceModal').modal('show');
}

function enableMaintenance() {
    const message = $('#maintenanceMessage').val() || 'Site is under maintenance. Please check back soon.';
    const retryAfter = $('#retryAfter').val() || 3600;
    
    if (!confirm('Enable maintenance mode?')) return;
    
    $.ajax({
        url: '{{ route("admin.system.maintenance.enable") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            message: message,
            retry: retryAfter
        },
        success: function() {
            alert('Maintenance mode enabled!');
            $('#maintenanceModal').modal('hide');
            location.reload();
        },
        error: function() {
            alert('Error enabling maintenance mode');
        }
    });
}

function disableMaintenance() {
    if (!confirm('Disable maintenance mode?')) return;
    
    $.ajax({
        url: '{{ route("admin.system.maintenance.disable") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function() {
            alert('Maintenance mode disabled!');
            $('#maintenanceModal').modal('hide');
            location.reload();
        },
        error: function() {
            alert('Error disabling maintenance mode');
        }
    });
}
</script>
@endsection