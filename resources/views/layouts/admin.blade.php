<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - e-License')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            border-left: 4px solid transparent;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
            border-left-color: #0d6efd;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
            border-left-color: #0d6efd;
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-key"></i> e-License <span class="badge bg-warning">Admin</span>
            </a>
            
            <div class="d-flex align-items-center">
                <!-- Notification Bell -->
                <div class="dropdown me-3">
                    <a href="#" class="text-light position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        @php
                            $unreadCount = auth()->user()->notifications()->unread()->count();
                        @endphp
                        @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount }}
                        </span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px;">
                        <div class="dropdown-header">
                            <strong>Notifications</strong>
                            @if($unreadCount > 0)
                            <a href="#" class="float-end text-decoration-none" onclick="markAllAsRead()">
                                <small>Mark all read</small>
                            </a>
                            @endif
                        </div>
                        <div class="dropdown-list" style="max-height: 300px; overflow-y: auto;">
                            @foreach(auth()->user()->notifications()->latest()->limit(10)->get() as $notification)
                            <a href="#" class="dropdown-item {{ $notification->read_at ? '' : 'bg-light' }}" 
                               onclick="markAsRead({{ $notification->id }})">
                                <div class="dropdown-item-content">
                                    <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                    <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                    <div class="text-truncate">{{ $notification->data['message'] ?? '' }}</div>
                                </div>
                            </a>
                            @endforeach
                            
                            @if(auth()->user()->notifications()->count() === 0)
                            <div class="dropdown-item text-center text-muted py-3">
                                No notifications
                            </div>
                            @endif
                        </div>
                        <div class="dropdown-footer">
                            <a href="#" class="text-decoration-none" onclick="viewAllNotifications()">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>
                
                <span class="text-light me-3">
                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.system.info') }}">
                                <i class="fas fa-info-circle"></i> System Info
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('user.dashboard') }}">
                                <i class="fas fa-user"></i> User Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('/') }}">
                                <i class="fas fa-store"></i> Public Site
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <nav class="nav flex-column pt-3">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                           href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        
                        <div class="px-3 mt-3 mb-2 text-uppercase small text-white-50">
                            <i class="fas fa-database"></i> Data Management
                        </div>
                        
                        <a class="nav-link {{ request()->routeIs('admin.license-pool.*') ? 'active' : '' }}" 
                           href="{{ route('admin.license-pool.index') }}">
                            <i class="fas fa-key"></i> License Pool
                            @php
                                $lowStock = \App\Models\LicensePool::where('status', 'active')->count() < 10;
                            @endphp
                            @if($lowStock)
                            <span class="badge bg-danger float-end">!</span>
                            @endif
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                           href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users"></i> Users
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" 
                           href="{{ route('admin.orders.index') }}">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                           href="{{ route('admin.products.index') }}">
                            <i class="fas fa-box"></i> Products
                        </a>
                        
                        <div class="px-3 mt-3 mb-2 text-uppercase small text-white-50">
                            <i class="fas fa-shield-alt"></i> Warranty
                        </div>
                        
                        <a class="nav-link {{ request()->routeIs('admin.warranty.*') ? 'active' : '' }}" 
                           href="{{ route('admin.warranty.index') }}">
                            <i class="fas fa-exchange-alt"></i> Warranty Claims
                            @php
                                $pendingCount = \App\Models\WarrantyExchange::where('auto_approved', false)
                                    ->whereNull('approved_at')
                                    ->count();
                            @endphp
                            @if($pendingCount > 0)
                            <span class="badge bg-warning float-end">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        
                        <div class="px-3 mt-3 mb-2 text-uppercase small text-white-50">
                            <i class="fas fa-chart-bar"></i> Reports
                        </div>
                        
                        <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" 
                           href="{{ route('admin.reports.sales') }}">
                            <i class="fas fa-chart-line"></i> Sales Report
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" 
                           href="{{ route('admin.reports.activations') }}">
                            <i class="fas fa-chart-pie"></i> Activation Report
                        </a>
                        
                        <div class="px-3 mt-3 mb-2 text-uppercase small text-white-50">
                            <i class="fas fa-tools"></i> Tools
                        </div>
                        
                        <a class="nav-link" href="{{ route('admin.system.info') }}">
                            <i class="fas fa-info-circle"></i> System Info
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">
                <!-- Notifications -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">@yield('page-title')</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                @yield('breadcrumbs')
                            </ol>
                        </nav>
                    </div>
                    <div>
                        @yield('page-actions')
                    </div>
                </div>
                
                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-key"></i> e-License Admin Panel v1.0
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small>
                        &copy; {{ date('Y') }} e-License. Server time: {{ now()->format('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('scripts')
    
    <script>
    // Auto-hide alerts after 5 seconds
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Initialize DataTables on tables with class 'datatable'
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 25,
            responsive: true
        });
    });
    
    // Confirm before delete
    function confirmDelete(message) {
        return confirm(message || 'Apakah Anda yakin ingin menghapus?');
    }
    
    // Confirm before action
    function confirmAction(message) {
        return confirm(message || 'Apakah Anda yakin?');
    }
    
    // Notification functions
    function markAsRead(notificationId) {
        fetch('/user/notifications/' + notificationId + '/mark-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function markAllAsRead() {
        fetch('/user/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function viewAllNotifications() {
        window.location.href = '/user/notifications';
    }
    </script>
</body>
</html>