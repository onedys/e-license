@extends('layouts.admin')

@section('title', 'License Usage Report - e-License')
@section('page-title', 'License Usage Report')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active">License Usage Report</li>
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
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date', now()->subYear()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date', now()->format('Y-m-d')) }}">
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
                        <label class="form-label">License Status</label>
                        <select name="license_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('license_status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="blocked" {{ request('license_status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            <option value="invalid" {{ request('license_status') == 'invalid' ? 'selected' : '' }}>Invalid</option>
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
                        <h6 class="mb-0">Total Keys</h6>
                        <h3 class="mb-0" id="totalKeys">0</h3>
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
                        <h6 class="mb-0">Active Keys</h6>
                        <h3 class="mb-0" id="activeKeys">0</h3>
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
                        <h6 class="mb-0">Available Keys</h6>
                        <h3 class="mb-0" id="availableKeys">0</h3>
                    </div>
                    <i class="fas fa-database fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Usage Rate</h6>
                        <h3 class="mb-0" id="usageRate">0%</h3>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Assignment Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="assignmentChart" height="200"></canvas>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Product-wise License Usage</h5>
            </div>
            <div class="card-body">
                <canvas id="productUsageChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Most Used Licenses</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Total Assignments</th>
                                <th>Active Assignments</th>
                                <th>First Used</th>
                                <th>Last Used</th>
                            </tr>
                        </thead>
                        <tbody id="licenseData">
                            <!-- Data akan diisi via AJAX -->
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
let assignmentChart, statusChart, productUsageChart;

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
        url: '{{ route("admin.reports.license-usage.data") }}?' + formData,
        type: 'GET',
        success: function(response) {
            updateStats(response.stats);
            updateCharts(response.charts);
            updateTable(response.data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading license usage data:', error);
            alert('Error loading license usage data');
        }
    });
}

function updateStats(stats) {
    $('#totalKeys').text(formatNumber(stats.total_keys));
    $('#activeKeys').text(formatNumber(stats.active_keys));
    $('#availableKeys').text(formatNumber(stats.available_keys));
    $('#usageRate').text(stats.usage_rate + '%');
}

function updateCharts(charts) {
    // Destroy existing charts
    if (assignmentChart) assignmentChart.destroy();
    if (statusChart) statusChart.destroy();
    if (productUsageChart) productUsageChart.destroy();
    
    // Assignment Trend Chart
    const assignmentCtx = document.getElementById('assignmentChart').getContext('2d');
    assignmentChart = new Chart(assignmentCtx, {
        type: 'line',
        data: {
            labels: charts.assignment_trend.labels,
            datasets: [{
                label: 'License Assignments',
                data: charts.assignment_trend.data,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily License Assignments'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
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
                    'rgb(75, 192, 192)',  // Active
                    'rgb(255, 99, 132)',   // Blocked
                    'rgb(255, 205, 86)'    // Invalid
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'License Status Distribution'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Product Usage Chart
    const productCtx = document.getElementById('productUsageChart').getContext('2d');
    productUsageChart = new Chart(productCtx, {
        type: 'bar',
        data: {
            labels: charts.product_usage.labels,
            datasets: [
                {
                    label: 'Total Keys',
                    data: charts.product_usage.total_keys,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                },
                {
                    label: 'Assigned Keys',
                    data: charts.product_usage.assigned_keys,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'License Usage by Product'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
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
            <td>${license.product}</td>
            <td><span class="badge bg-${license.status == 'active' ? 'success' : (license.status == 'blocked' ? 'danger' : 'warning')}">${license.status}</span></td>
            <td>${license.total_assignments}</td>
            <td>${license.active_assignments}</td>
            <td>${license.first_used}</td>
            <td>${license.last_used}</td>
        </tr>
        `;
    });
    
    $('#licenseData').html(html);
    
    // Reinitialize DataTables
    $('.datatable').DataTable({
        destroy: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        pageLength: 25,
        order: [[3, 'desc']] // Sort by total assignments descending
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
    window.open('{{ route("admin.reports.export") }}?type=license-usage&' + formData, '_blank');
}
</script>
@endsection
