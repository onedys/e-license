// Dashboard JavaScript

$(document).ready(function() {
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeDashboardCharts();
    }
    
    // Real-time updates for dashboard
    if ($('.real-time-update').length) {
        initializeRealTimeUpdates();
    }
    
    // License filter
    $('#licenseFilter').change(function() {
        const filter = $(this).val();
        const rows = $('.license-row');
        
        if (filter === 'all') {
            rows.show();
        } else {
            rows.hide();
            rows.filter('.status-' + filter).show();
        }
    });
    
    // Order filter
    $('#orderFilter').change(function() {
        const filter = $(this).val();
        const rows = $('.order-row');
        
        if (filter === 'all') {
            rows.show();
        } else {
            rows.hide();
            rows.filter('.status-' + filter).show();
        }
    });
    
    // Quick actions
    $('.quick-action-btn').click(function() {
        const action = $(this).data('action');
        
        switch(action) {
            case 'activate':
                window.location.href = $('#activationUrl').val();
                break;
            case 'claim':
                window.location.href = $('#warrantyUrl').val();
                break;
            case 'buy':
                window.location.href = $('#productsUrl').val();
                break;
            case 'support':
                $('#supportModal').modal('show');
                break;
        }
    });
    
    // Mark notification as read
    $('.notification-item').click(function() {
        const notificationId = $(this).data('id');
        const item = $(this);
        
        if (!item.hasClass('read')) {
            $.ajax({
                url: $('#markNotificationReadUrl').val(),
                type: 'POST',
                data: {
                    notification_id: notificationId
                },
                success: function() {
                    item.addClass('read');
                    item.find('.badge').remove();
                    updateUnreadCount();
                }
            });
        }
    });
    
    // Mark all notifications as read
    $('#markAllReadBtn').click(function() {
        $.ajax({
            url: $('#markAllNotificationsReadUrl').val(),
            type: 'POST',
            success: function() {
                $('.notification-item').addClass('read');
                $('.notification-item .badge').remove();
                updateUnreadCount();
                toastr.success('Semua notifikasi ditandai sudah dibaca');
            }
        });
    });
    
    // Update unread notification count
    function updateUnreadCount() {
        const unreadCount = $('.notification-item:not(.read)').length;
        $('#unreadNotificationCount').text(unreadCount);
        
        if (unreadCount === 0) {
            $('#unreadNotificationCount').hide();
        } else {
            $('#unreadNotificationCount').show();
        }
    }
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Copy license key on click
    $('.copy-license').click(function() {
        const licenseKey = $(this).data('license');
        eLicense.copyToClipboard(licenseKey, 'Lisensi berhasil disalin!');
    });
    
    // Copy confirmation ID on click
    $('.copy-cid').click(function() {
        const cid = $(this).data('cid');
        eLicense.copyToClipboard(cid, 'Confirmation ID berhasil disalin!');
    });
});

// Initialize dashboard charts
function initializeDashboardCharts() {
    // Sales chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: window.salesChartData || {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
    
    // License status chart
    const licenseCtx = document.getElementById('licenseStatusChart');
    if (licenseCtx) {
        const licenseChart = new Chart(licenseCtx, {
            type: 'doughnut',
            data: window.licenseChartData || {
                labels: ['Active', 'Pending', 'Blocked', 'Replaced'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        '#198754',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }
}

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Update stats every 30 seconds
    setInterval(function() {
        $.ajax({
            url: $('#statsUpdateUrl').val(),
            type: 'GET',
            success: function(data) {
                updateStats(data);
            }
        });
    }, 30000);
    
    // Update notifications every 60 seconds
    setInterval(function() {
        $.ajax({
            url: $('#notificationsUpdateUrl').val(),
            type: 'GET',
            success: function(data) {
                updateNotifications(data);
            }
        });
    }, 60000);
}

// Update dashboard stats
function updateStats(data) {
    if (data.total_orders) {
        $('#totalOrders').text(data.total_orders);
    }
    if (data.total_licenses) {
        $('#totalLicenses').text(data.total_licenses);
    }
    if (data.active_licenses) {
        $('#activeLicenses').text(data.active_licenses);
    }
    if (data.pending_licenses) {
        $('#pendingLicenses').text(data.pending_licenses);
    }
}

// Update notifications
function updateNotifications(data) {
    if (data.notifications && data.notifications.length > 0) {
        const container = $('#notificationsContainer');
        let html = '';
        
        data.notifications.forEach(function(notification) {
            html += `
                <div class="list-group-item notification-item ${notification.read_at ? 'read' : ''}" 
                      data-id="${notification.id}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${notification.title}</h6>
                        <small>${notification.time_ago}</small>
                    </div>
                    <p class="mb-1">${notification.message}</p>
                    ${notification.read_at ? '' : '<span class="badge bg-primary">New</span>'}
                </div>
            `;
        });
        
        container.html(html);
        updateUnreadCount();
    }
}

// Function to refresh license list
function refreshLicenseList() {
    $.ajax({
        url: $('#licensesRefreshUrl').val(),
        type: 'GET',
        success: function(data) {
            $('#licensesContainer').html(data);
        }
    });
}

// Function to refresh order list
function refreshOrderList() {
    $.ajax({
        url: $('#ordersRefreshUrl').val(),
        type: 'GET',
        success: function(data) {
            $('#ordersContainer').html(data);
        }
    });
}