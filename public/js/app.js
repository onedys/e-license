// e-License Main JavaScript

// CSRF Token Setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Global Functions
window.eLicense = {
    // Show loading spinner
    showLoading: function(button) {
        if (button) {
            const originalText = button.html();
            button.data('original-text', originalText);
            button.prop('disabled', true);
            button.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        }
    },
    
    // Hide loading spinner
    hideLoading: function(button) {
        if (button && button.data('original-text')) {
            button.prop('disabled', false);
            button.html(button.data('original-text'));
        }
    },
    
    // Copy text to clipboard
    copyToClipboard: function(text, successMessage = 'Copied!') {
        navigator.clipboard.writeText(text).then(function() {
            toastr.success(successMessage);
        }, function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                toastr.success(successMessage);
            } catch (err) {
                toastr.error('Failed to copy');
            }
            document.body.removeChild(textArea);
        });
    },
    
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    },
    
    // Format date
    formatDate: function(dateString, format = 'DD/MM/YYYY HH:mm') {
        return moment(dateString).format(format);
    },
    
    // Show confirmation dialog
    confirmAction: function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    }
};

// Auto-hide alerts
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Form validation
    $('.needs-validation').on('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Cart count update
    function updateCartCount() {
        $.ajax({
            url: '/cart/count',
            type: 'GET',
            success: function(count) {
                $('#cart-count').text(count);
            }
        });
    }
    
    // Initialize if cart count exists
    if ($('#cart-count').length) {
        updateCartCount();
    }
    
    // Auto-refresh dashboard data every 30 seconds
    if ($('.auto-refresh').length) {
        setInterval(function() {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                data: { 'ajax': true },
                success: function(data) {
                    $('.auto-refresh').html($(data).find('.auto-refresh').html());
                }
            });
        }, 30000);
    }
});

// Toastr notifications configuration
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};