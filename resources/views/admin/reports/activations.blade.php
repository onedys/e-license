@extends('layouts.admin')

@section('title', 'Activation Report - e-License')
@section('page-title', 'Activation Report')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active">Activation Report</li>
@endsection

@section('page-actions')
<button type="button" class="btn btn-primary" onclick="exportReport()">
    <i class="fas fa-download"></i> Export Report
</button>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3" id="reportFilter">
    @csrf
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from', now()->subDays(30)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            @foreach(\App\Models\Product::all() as $product)
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
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Activations</h6>
                        <h3 class="mb-0" id="totalActivations">0</h3>
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
                        <h6 class="mb-0">Active Licenses</h6>
                        <h3 class="mb-0" id="activeLicenses">0</h3>
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
                        <h6 class="mb-0">Pending Licenses</h6>
                        <h3 class="mb-0" id="pendingLicenses">0</h3>
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
                        <h6 class="mb-0">Blocked Licenses</h6>
                        <h3 class="mb-0" id="blockedLicenses">0</h3>
                    </div>
                    <i class="fas fa-ban fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Activation Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="activationChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Activation Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>Activated Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Activation Attempts</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody id="activationData">
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activationChart, statusChart;

$(document).ready(function() {
    loadReportData();
    
    // Reload data when filter changes
    $('#reportFilter').change(function() {
        loadReportData();
    });
});

function loadReportData() {
    const formData = $('#reportFilter').serialize();
    
    $.ajax({
        url: '{{ route("admin.reports.activations.data") }}?' + formData,
        type: 'GET',
        success: function(response) {
            updateStats(response.stats);
            updateCharts(response.charts);
            updateTable(response.data);
        },
        error: function() {
            alert('Error loading activation data');
        }
    });
}

function updateStats(stats) {
    $('#totalActivations').text(formatNumber(stats.total_licenses));
    $('#activeLicenses').text(formatNumber(stats.active_licenses));
    $('#pendingLicenses').text(formatNumber(stats.pending_licenses));
    $('#blockedLicenses').text(formatNumber(stats.blocked_licenses));
}

function updateCharts(charts) {
    // Destroy existing charts
    if (activationChart) activationChart.destroy();
    if (statusChart) statusChart.destroy();
    
    // Activation Trend Chart
    const activationCtx = document.getElementById('activationChart').getContext('2d');
    activationChart = new Chart(activationCtx, {
        type: 'line',
        data: {
            labels: charts.activation_trend.labels,
            datasets: [
                {
                    label: 'Total Activations',
                    data: charts.activation_trend.total,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3
                },
                {
                    label: 'Active Licenses',
                    data: charts.activation_trend.active,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily Activation Trends'
                }
            }
        }
    });
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: charts.status_distribution.labels,
            datasets: [{
                data: charts.status_distribution.data,
                backgroundColor: [
                    'rgb(75, 192, 192)',  // Active - green
                    'rgb(255, 205, 86)',  // Pending - yellow
                    'rgb(255, 99, 132)'   // Blocked - red
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'License Status Distribution'
                }
            }
        }
    });
}

function updateTable(data) {
    let html = '';
    
    data.forEach(function(license) {
        html += `
        <tr>
            <td><code>${license.license_key}</code></td>
            <td>${license.activated_date}</td>
            <td>${license.customer}</td>
            <td>${license.product}</td>
            <td><span class="badge bg-${license.status == 'active' ? 'success' : (license.status == 'pending' ? 'warning' : 'danger')}">${license.status}</span></td>
            <td>${license.activation_attempts}</td>
            <td>${license.last_activity}</td>
        </tr>
        `;
    });
    
    $('#activationData').html(html);
    
    // Reinitialize DataTables
    $('.datatable').DataTable({
        destroy: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        pageLength: 25
    });
}

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function resetFilter() {
    $('#reportFilter')[0].reset();
    loadReportData();
}

function exportReport() {
    const formData = $('#reportFilter').serialize();
    window.open('{{ route("admin.reports.export") }}?type=activations&' + formData, '_blank');
}
</script>
@endsection
