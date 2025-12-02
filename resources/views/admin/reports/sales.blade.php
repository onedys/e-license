@extends('layouts.admin')

@section('title', 'Sales Report - e-License')
@section('page-title', 'Sales Report')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
</li>
<li class="breadcrumb-item active">Sales Report</li>
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
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
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
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
                        <h6 class="mb-0">Total Sales</h6>
                        <h3 class="mb-0" id="totalSales">Rp 0</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Orders</h6>
                        <h3 class="mb-0" id="totalOrders">0</h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Avg. Order Value</h6>
                        <h3 class="mb-0" id="avgOrder">Rp 0</h3>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Success Rate</h6>
                        <h3 class="mb-0" id="successRate">0%</h3>
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
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Sales Chart</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Sales by Product</h5>
            </div>
            <div class="card-body">
                <canvas id="productChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Detailed Sales Data</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>License Sent</th>
                            </tr>
                        </thead>
                        <tbody id="salesData">
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
let salesChart, productChart;

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
        url: '{{ route("admin.reports.sales.data") }}?' + formData,
        type: 'GET',
        success: function(response) {
            updateStats(response.stats);
            updateCharts(response.charts);
            updateTable(response.data);
        },
        error: function() {
            alert('Error loading report data');
        }
    });
}

function updateStats(stats) {
    $('#totalSales').text('Rp ' + formatNumber(stats.total_sales));
    $('#totalOrders').text(formatNumber(stats.total_orders));
    $('#avgOrder').text('Rp ' + formatNumber(stats.avg_order));
    $('#successRate').text(stats.success_rate + '%');
}

function updateCharts(charts) {
    // Destroy existing charts
    if (salesChart) salesChart.destroy();
    if (productChart) productChart.destroy();
    
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: charts.sales.labels,
            datasets: [{
                label: 'Sales (Rp)',
                data: charts.sales.data,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Daily Sales'
                }
            }
        }
    });
    
    // Product Chart
    const productCtx = document.getElementById('productChart').getContext('2d');
    productChart = new Chart(productCtx, {
        type: 'doughnut',
        data: {
            labels: charts.products.labels,
            datasets: [{
                data: charts.products.data,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales by Product'
                }
            }
        }
    });
}

function updateTable(data) {
    let html = '';
    
    data.forEach(function(order) {
        html += `
        <tr>
            <td>${order.order_number}</td>
            <td>${order.date}</td>
            <td>${order.customer}</td>
            <td>${order.product}</td>
            <td>${order.quantity}</td>
            <td>Rp ${formatNumber(order.amount)}</td>
            <td><span class="badge bg-${order.status == 'paid' ? 'success' : (order.status == 'pending' ? 'warning' : 'danger')}">${order.status}</span></td>
            <td><span class="badge bg-${order.license_sent ? 'success' : 'secondary'}">${order.license_sent ? 'Yes' : 'No'}</span></td>
        </tr>
        `;
    });
    
    $('#salesData').html(html);
    
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
    window.open('{{ route("admin.reports.export") }}?' + formData, '_blank');
}
</script>
@endsection